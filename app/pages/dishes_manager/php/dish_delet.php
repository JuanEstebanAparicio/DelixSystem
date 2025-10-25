<?php
require_once(__DIR__ . '/dishes_crud.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $crud = new dishes_crud();

    try {
        $crud->deleteDish($id);

        header('Location: ../view/dishes_manager.php?success=3');
        exit;
    } catch (Exception $e) {
        error_log('Error al eliminar plato: ' . $e->getMessage());
        header('Location: ../view/dishes_manager.php?success=0');
        exit;
    }
} else {
    header('Location: ../view/dishes_manager.php?success=0');
    exit;
}
?>
