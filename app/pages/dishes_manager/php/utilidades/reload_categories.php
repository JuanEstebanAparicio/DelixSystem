<?php
require_once __DIR__ . '/../../../../config/supabase.php';

try {
    $stmt = $conexion->query("SELECT DISTINCT category FROM dish WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$categorias) $categorias = [];

    header('Content-Type: application/json');
    echo json_encode($categorias);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
