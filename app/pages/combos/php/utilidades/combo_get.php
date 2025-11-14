<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../config/supabase.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["success" => false, "error" => "No autorizado"]);
    exit;
}

$id_user = $_SESSION['usuario']['id'];

try {
    $stmt = $conexion->prepare("
        SELECT c.id, c.id_dish, c.state, c.description, c.created_at, c.photo,
               d.name_dish, d.price, d.category
        FROM combo_day c
        INNER JOIN dish d ON c.id_dish = d.id
        WHERE c.id_user = :id_user
        ORDER BY c.created_at DESC
    ");
    $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->execute();
    $combos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "combos" => $combos
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error al obtener los combos: " . $e->getMessage()
    ]);
}
?>
