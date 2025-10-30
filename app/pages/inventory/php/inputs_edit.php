<?php
ob_start();
session_start();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
        throw new Exception("⚠️ Solicitud inválida o ID faltante.");
    }

    if (!isset($_SESSION['usuario']['id'])) {
        throw new Exception("No hay usuario autenticado.");
    }

    $id_user = intval($_SESSION['usuario']['id']);
    $id = intval($_POST['id']);

    $baseDir = dirname(__DIR__, 3);
    require_once($baseDir . '/pages/inventory/php/products.php');
    require_once($baseDir . '/pages/inventory/php/storage_crud.php');

    $crud = new storage_crud();
    $current = $crud->getProductById($id, $id_user);
    if (!$current) {
        throw new Exception("❌ Producto no encontrado o no pertenece al usuario.");
    }

    $category = trim($_POST['category'] ?? $current['category']);
    $newCategory = trim($_POST['new_category'] ?? '');
    if ($category === '__new__' || $newCategory !== '') $category = $newCategory;

    $productName = trim($_POST['name'] ?? $current['name']);

    $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category ?: 'sin_categoria');
    $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $productName ?: 'producto');

    $mediaRoot = $baseDir . "/pages/inventory/media";
    $categoryDir = "$mediaRoot/$safeCategory";
    $productDir = "$categoryDir/$safeProduct";

    if (!is_dir($categoryDir)) mkdir($categoryDir, 0777, true);
    if (!is_dir($productDir)) mkdir($productDir, 0777, true);

    $photoPath = $current['photo'] ?? null;

    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoName = uniqid('photo_') . "_" . basename($_FILES['photo']['name']);
        $targetPath = "$productDir/$photoName";
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            throw new Exception("Error al guardar la nueva imagen.");
        }
        if (!empty($current['photo'])) {
            $oldPhotoAbs = $baseDir . '/pages/inventory/' . ltrim($current['photo'], '/');
            if (file_exists($oldPhotoAbs)) @unlink($oldPhotoAbs);
        }
        $photoPath = "media/$safeCategory/$safeProduct/$photoName";
    }

    $product = new Product(
        $id_user,
        $productName,
        $_POST['amount'] ?? $current['amount'],
        $_POST['minimum_quantity'] ?? $current['minimum_quantity'],
        $_POST['unit'] ?? $current['unit'],
        $_POST['unit_cost'] ?? $current['unit_cost'],
        $category,
        $_POST['fecha_ingreso'] ?? $current['entrance_date'],
        $_POST['fecha_vencimiento'] ?? $current['expiration_date'],
        $_POST['batch'] ?? $current['batch'],
        $_POST['description'] ?? $current['description'],
        $_POST['location'] ?? $current['location'],
        $_POST['state'] ?? $current['state'],
        $_POST['supplier'] ?? $current['supplier'],
        $photoPath
    );

    $ok = $crud->updateProduct($product, $id);
    if (!$ok) throw new Exception("Error al actualizar el ingrediente en la base de datos.");

    ob_clean();
    echo json_encode(["success" => true, "message" => "✅ Ingrediente actualizado correctamente"]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
