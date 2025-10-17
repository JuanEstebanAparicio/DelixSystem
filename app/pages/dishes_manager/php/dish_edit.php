<?php
require_once('../../Model/Entity/dishes.php');
require_once('../../Model/Crud/dishes_crud.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['nombre'] ?? '';
    $price = $_POST['precio'] ?? '';
    $category = $_POST['categoria'] ?? '';
    $description = $_POST['descripcion'] ?? '';
    $state = $_POST['estado'] ?? '';
    $created_at = date('Y-m-d H:i:s');
    $photo = '';

    // Manejo de foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $targetDir = '../../Media/platos/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['foto']['name']);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
            $photo = $targetFile;
        }
    } else {
        $photo = $_POST['foto_actual'] ?? '';
    }

    $dish = new dishes($id, $name, $price, $category, $description, $state, $created_at, $photo);
    $crud = new dishes_crud();
    try {
        $crud->updateDish($dish, $id);
        header('Location: /Proyecto_de_aula/View/dishes_manager.php?success=2');
        exit;
    } catch (Exception $e) {
        header('Location: /Proyecto_de_aula/View/dishes_manager.php?success=0');
        exit;
    }
}
?>
