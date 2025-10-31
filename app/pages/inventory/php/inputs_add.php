<?php
ob_start();
session_start();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Solo se permiten solicitudes POST.");
    }

    if (!isset($_SESSION['usuario']['id'])) {
        throw new Exception("No hay usuario autenticado.");
    }

    $id_user = intval($_SESSION['usuario']['id']);

    $baseDir = dirname(__DIR__, 3);
    require_once($baseDir . '/pages/inventory/php/products.php');
    require_once($baseDir . '/pages/inventory/php/storage_crud.php');

    $category = trim($_POST['category'] ?? '');
    $newCategory = trim($_POST['new_category'] ?? '');
    if ($category === '__new__' || $newCategory !== '') $category = $newCategory;

    $productName = trim($_POST['name'] ?? '');

    $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category ?: 'sin_categoria');
    $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $productName ?: 'producto');

    $mediaRoot = $baseDir . "/pages/inventory/media";
    $categoryDir = "$mediaRoot/$safeCategory";
    $productDir = "$categoryDir/$safeProduct";

    if (!is_dir($categoryDir)) mkdir($categoryDir, 0777, true);
    if (!is_dir($productDir)) mkdir($productDir, 0777, true);

    $photoPath = null;
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoName = uniqid('photo_') . "_" . basename($_FILES['photo']['name']);
        $targetPath = "$productDir/$photoName";
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            throw new Exception("Error al guardar la imagen.");
        }
        $photoPath = "media/$safeCategory/$safeProduct/$photoName";
    }

    $product = new Product(
        $id_user,
        $productName,
        $_POST['amount'] ?? 0,
        $_POST['minimum_quantity'] ?? 0,
        $_POST['unit'] ?? '',
        $_POST['unit_cost'] ?? 0,
        $category,
        $_POST['fecha_ingreso'] ?? null,
        $_POST['fecha_vencimiento'] ?? null,
        $_POST['batch'] ?? null,
        $_POST['description'] ?? null,
        $_POST['location'] ?? null,
        $_POST['state'] ?? 'Activo',
        $_POST['supplier'] ?? null,
        $photoPath
    );

    $crud = new storage_crud();
    $ok = $crud->insertProduct($product);
    if (!$ok) throw new Exception("Error al insertar el ingrediente en la base de datos.");

    ob_clean();
    echo json_encode(["success" => true, "message" => "✅ Ingrediente agregado correctamente"]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>