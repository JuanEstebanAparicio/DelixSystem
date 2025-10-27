<?php
require_once __DIR__ . '/../../../config/supabase.php';
header('Content-Type: application/json');

try {
    $query = $conexion->query("SELECT * FROM storage ORDER BY category, name ASC");
    $insumos = $query->fetchAll(PDO::FETCH_ASSOC);

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
?>
