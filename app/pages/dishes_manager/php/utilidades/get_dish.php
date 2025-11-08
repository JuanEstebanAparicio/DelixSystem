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
    // Obtener platos
    $stmt = $conexion->prepare("
        SELECT * 
        FROM dish
        WHERE id_user = :id_user
        ORDER BY category, name_dish ASC
    ");
    $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->execute();
    $platos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener ingredientes disponibles
    $stmtIng = $conexion->prepare("
        SELECT id, name 
        FROM storage 
        WHERE id_user = :id_user
        ORDER BY name ASC
    ");
    $stmtIng->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmtIng->execute();
    $ingredientes = $stmtIng->fetchAll(PDO::FETCH_ASSOC);

    // Agregar ingredientes por plato
    foreach ($platos as $i => $dish) {
        $stmt2 = $conexion->prepare("
            SELECT di.ingredient_id AS id, di.quantity_used, di.unit, s.name
            FROM dish_ingredient di
            INNER JOIN storage s ON di.ingredient_id = s.id
            WHERE di.dish_id = ?
        ");
        $stmt2->execute([$dish['id']]);
        $platos[$i]['ingredients'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        "success" => true,
        "platos" => $platos,
        "ingredientes" => $ingredientes
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error al obtener los datos: " . $e->getMessage()
    ]);
}
