<?php
session_start();
header('Content-Type: application/json');

$baseDir = dirname(__DIR__, 4);
require_once($baseDir . '/config/supabase.php');

try {
    if (!isset($_SESSION['usuario'])) {
        throw new Exception("Usuario no autenticado.");
    }

    $id_user = $_SESSION['usuario']['id'];

    // 🔹 OBTENER CATEGORÍAS
    $sqlCategorias = "
        SELECT DISTINCT category 
        FROM storage 
        WHERE id_user = :id_user 
          AND category IS NOT NULL 
          AND category != '' 
        ORDER BY category ASC
    ";
    $stmtCat = $conexion->prepare($sqlCategorias);
    $stmtCat->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmtCat->execute();
    $categorias = $stmtCat->fetchAll(PDO::FETCH_COLUMN) ?: [];

    // 🔹 OBTENER INVENTARIO
    $sqlInsumos = "
        SELECT * 
        FROM storage 
        WHERE id_user = :id_user 
        ORDER BY category, name ASC
    ";
    $stmtInv = $conexion->prepare($sqlInsumos);
    $stmtInv->execute([':id_user' => $id_user]);
    $insumos = $stmtInv->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "categorias" => $categorias,
        "insumos" => $insumos
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>