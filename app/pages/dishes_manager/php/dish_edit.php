<?php
require_once(__DIR__ . '/dishes.php');
require_once(__DIR__ . '/dishes_crud.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = $_POST['id'] ?? null;
    $name        = trim($_POST['name_dish'] ?? '');
    $price       = $_POST['price'] ?? '';
    $category    = trim($_POST['category'] ?? '');
    $newCategory = trim($_POST['new_category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $state       = $_POST['state'] ?? 'Activo';
    $created_at  = date('Y-m-d H:i:s');
    $photo       = $_POST['current_photo'] ?? '';

    if (!empty($newCategory)) {
        $category = $newCategory;
    }

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['photo']['name']);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        $categoryDir = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($category));
        $dishDir = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($name));

        $baseDir = __DIR__ . '/../media/' . $categoryDir . '/' . $dishDir . '/';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $uniqueName = uniqid('dish_') . '.' . $ext;
        $targetFile = $baseDir . $uniqueName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $photo = 'pages/dishes_manager/media/' . $categoryDir . '/' . $dishDir . '/' . $uniqueName;
        }
    }

    $dish = new dishes($id, $name, $price, $category, $description, $state, $created_at, $photo);
    $crud = new dishes_crud();

    try {
        $crud->updateDish($dish, $id);
        header('Location: ../view/dishes_manager.php?success=2');
        exit;
    } catch (Exception $e) {
        header('Location: ../view/dishes_manager.php?success=0');
        exit;
    }
}
?>
