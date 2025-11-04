<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../config/supabase.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["success" => false, "error" => "Sesión no iniciada"]);
    exit;
}

$id_user = $_SESSION['usuario']['id'];

try {
    $stmt = $conexion->prepare("SELECT * FROM storage WHERE id_user = :id_user ORDER BY category, name ASC");
    $stmt->execute([':id_user' => $id_user]);
    $insumos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "insumos" => $insumos
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
