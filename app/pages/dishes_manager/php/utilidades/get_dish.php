<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["success" => false, "error" => "No autorizado"]);
    exit;
}

$id_user = $_SESSION['usuario']['id'];

require_once __DIR__ . '/../../../../config/supabase.php';

try {
    $stmt = $conexion->prepare("
        SELECT * 
        FROM dish
        WHERE id_user = :id_user
        ORDER BY category, name_dish ASC
    ");
    $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->execute();
    $platos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtIng = $conexion->prepare("
        SELECT id, name 
        FROM storage 
        WHERE id_user = :id_user
        ORDER BY name ASC
    ");
    $stmtIng->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmtIng->execute();
    $ingredientes = $stmtIng->fetchAll(PDO::FETCH_ASSOC);

    foreach ($platos as $i => $dish) {
        $stmt2 = $conexion->prepare("
            SELECT ingredient_id 
            FROM dish_ingredient
            WHERE dish_id = ?
        ");
        $stmt2->execute([$dish['id']]);
        $platos[$i]['ingredients'] = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    }

    echo json_encode([
        "success" => true,
        "platos" => $platos,
        "ingredientes" => $ingredientes
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>