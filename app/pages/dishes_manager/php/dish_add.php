<?php
require_once(__DIR__ . '/dishes.php');
require_once(__DIR__ . '/dishes_crud.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name_dish'] ?? '');
    $price       = $_POST['price'] ?? '';
    $category    = trim($_POST['category'] ?? '');
    $newCategory = trim($_POST['new_category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $state       = $_POST['state'] ?? 'Activo';
    $created_at  = date('Y-m-d H:i:s');
    $photo       = '';

    // Si se agrega una nueva categoría, reemplaza la seleccionada
    if (!empty($newCategory)) {
        $category = $newCategory;
    }

    // 📸 Manejo de imagen
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['photo']['name']);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $categoryDir = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($category));
        $dishDir = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($name));
        $baseDir = __DIR__ . '/../media/' . $categoryDir . '/' . $dishDir . '/';
        if (!is_dir($baseDir)) mkdir($baseDir, 0777, true);
        $uniqueName = uniqid('dish_') . '.' . $ext;
        $targetFile = $baseDir . $uniqueName;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $photo = 'media/' . $categoryDir . '/' . $dishDir . '/' . $uniqueName;
        }
    }

    // 🧄 Ingredientes con cantidades y unidades
    $ingredients = [];
    if (!empty($_POST['ingredients'])) {
        foreach ($_POST['ingredients'] as $ingId) {
            $ingredients[] = [
                'id' => $ingId,
                'quantity' => $_POST['quantity_' . $ingId] ?? 1,
                'unit' => $_POST['unit_' . $ingId] ?? 'unidad'
            ];
        }
    }

    // 🥗 Crear instancia del plato
    $dish = new dishes(null, $name, $price, $category, $description, $state, $created_at, $photo);
    $crud = new dishes_crud();

    try {
        $crud->createDish($dish, $ingredients);
        echo json_encode(["success" => true, "message" => "Plato creado exitosamente"]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}
?>
