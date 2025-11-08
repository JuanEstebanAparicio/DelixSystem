<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

$id_usuario = $_SESSION['usuario']['id'];

require_once __DIR__ . '/../../../../config/supabase.php';

try {
    $stmt = $conexion->prepare("SELECT * FROM dish WHERE id_user = :id_user ORDER BY category, name_dish ASC");
    $stmt->bindParam(':id_user', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $platos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($platos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
