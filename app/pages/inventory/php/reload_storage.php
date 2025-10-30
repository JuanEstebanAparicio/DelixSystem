<?php
session_start();
$baseDir = dirname(__DIR__, 3);
require_once($baseDir . '/config/supabase.php');

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['usuario'])) {
        throw new Exception("Usuario no autenticado.");
    }

    $id_user = $_SESSION['usuario']['id'];

    $sql = "SELECT DISTINCT category 
            FROM storage 
            WHERE id_user = :id_user AND category IS NOT NULL AND category != '' 
            ORDER BY category ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->execute();

    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!$categorias) $categorias = [];

    // ✅ IMPORTANTE: devolver solo el array plano, no un objeto
    echo json_encode($categorias);

} catch (Exception $e) {
    echo json_encode([]);
}
