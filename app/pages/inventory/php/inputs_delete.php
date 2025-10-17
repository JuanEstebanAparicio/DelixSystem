<?php
$baseDir = dirname(dirname(__DIR__));
require_once($baseDir . '/Model/Crud/storage_crud.php');
require_once($baseDir . '/Model/Entity/connection.php');

$pdo = Connection::getConnection();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $crud = new StorageCRUD($pdo);
        $crud->deleteProduct($id);
        header("Location: http://localhost/Proyecto_de_aula/View/ingredient_manager.php?success=3");
        exit();
    } catch (Exception $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    http_response_code(400);
    echo "ID no especificado.";
}
?>
