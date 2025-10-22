<?php
require_once('../../Model/Crud/dishes_crud.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $crud = new dishes_crud();
    try {
        $crud->deleteDish($id);
        header('Location: /Proyecto_de_aula/View/dishes_manager.php?success=3');
        exit;
    } catch (Exception $e) {
        header('Location: /Proyecto_de_aula/View/dishes_manager.php?success=0');
        exit;
    }
} else {
    header('Location: /Proyecto_de_aula/View/dishes_manager.php?success=0');
    exit;
}
?>
