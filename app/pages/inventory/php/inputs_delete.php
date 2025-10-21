<?php
$baseDir = dirname(__DIR__, 2);
require_once($baseDir . '/config/supabase.php');
require_once(__DIR__ . '/products.php');
require_once(__DIR__ . '/storage_crud.php');

$pdo = $conexion;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $crud = new StorageCRUD($pdo);
        $crud->deleteProduct($id);

        // Redirección correcta
        header("Location: ../view/ingredient_manager.php?success=3");
        exit();
    } catch (Exception $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    http_response_code(400);
    echo "ID no especificado.";
}
?>
