<?php
$baseDir = dirname(__DIR__, 3);
require_once($baseDir . '/pages/inventory/php/storage_crud.php');

header('Content-Type: application/json');

try {
    if ($_SERVER["REQUEST_METHOD"] !== "GET" || empty($_GET['id'])) {
        throw new Exception("⚠️ ID no especificado o método inválido.");
    }

    $id = $_GET['id'];
    $crud = new storage_crud();
    $product = $crud->getProductById($id);

    if (!$product) {
        throw new Exception("Ingrediente no encontrado.");
    }
    
    if (!empty($product['photo'])) {
        $photoPath = $baseDir . '/pages/inventory/' . $product['photo'];
        if (file_exists($photoPath)) @unlink($photoPath);
    }

    $crud->deleteProduct($id);
    echo json_encode(["success" => true, "message" => "🗑️ Ingrediente eliminado correctamente"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
