<?php
session_start();
require_once(__DIR__ . '/dishes.php');
require_once(__DIR__ . '/dishes_crud.php');

header('Content-Type: application/json');

if (!isset($_SESSION['usuario']['id'])) {
    echo json_encode(["success" => false, "error" => "No autorizado"]);
    exit;
}

$id_user = $_SESSION['usuario']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id          = $_POST['id'] ?? null;
    $name        = trim($_POST['name_dish'] ?? '');
    $price       = $_POST['price'] ?? '';
    $category    = trim($_POST['category'] ?? '');
    $newCategory = trim($_POST['new_category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $state       = $_POST['state'] ?? 'Activo';
    $photo       = $_POST['current_photo'] ?? '';

    if (!empty($newCategory)) $category = $newCategory;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['photo']['name']);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        $safeCategory = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($category));
        $safeDish = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($name));
        $baseDir = __DIR__ . '/../media/' . $safeCategory . '/' . $safeDish . '/';

        if (!is_dir($baseDir)) mkdir($baseDir, 0777, true);

        $uniqueName = uniqid('dish_') . '.' . $ext;
        $targetFile = $baseDir . $uniqueName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $photo = 'media/' . $safeCategory . '/' . $safeDish . '/' . $uniqueName;
        }
    }

    $ingredients = [];
    if (!empty($_POST['ingredients'])) {
        foreach ($_POST['ingredients'] as $idIng) {
            $ingredients[] = [
                'id'       => $idIng,
                'quantity' => $_POST['quantity_' . $idIng] ?? 1,
                'unit'     => $_POST['unit_' . $idIng] ?? 'unidad'
            ];
        }
    }

    $dish = new dishes($id, $id_user, $name, $price, $category, $description, $state, null, $photo);
    $crud = new dishes_crud();

    try {
        $crud->updateDish($dish, $id, $ingredients);
        echo json_encode(["success" => true, "message" => "Plato actualizado correctamente"]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}

?>