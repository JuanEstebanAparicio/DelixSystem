<?php
$baseDir = dirname(__DIR__, 3);
require_once($baseDir . '/config/supabase.php');
require_once(__DIR__ . '/products.php');
require_once(__DIR__ . '/storage_crud.php');

$pdo = $conexion ?? null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $crud = new storage_crud($pdo);
        $product = $crud->getProductById($id);

        // Si tiene imagen, eliminar del disco
        if (!empty($product['photo'])) {
            $photoPath = $baseDir . '/pages/inventory/' . $product['photo'];
            if (file_exists($photoPath)) unlink($photoPath);
        }

        // Eliminar registro
        $crud->deleteProduct($id);

        header("Location: ../view/ingredient_manager.php?success=3");
        exit();
    } catch (Exception $e) {
        die(" Error al eliminar: " . $e->getMessage());
    }
} else {
    http_response_code(400);
    echo "⚠️ ID no especificado.";
}
?>
