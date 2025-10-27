<?php
$baseDir = dirname(__DIR__, 3);
require_once($baseDir . '/pages/inventory/php/products.php');
require_once($baseDir . '/pages/inventory/php/storage_crud.php');

header('Content-Type: application/json');

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_POST['id'])) {
        throw new Exception("⚠️ Solicitud inválida o ID faltante.");
    }

    $id = intval($_POST['id']);
    $crud = new storage_crud();

    $current = $crud->getProductById($id);
    if (!$current) {
        throw new Exception("❌ Producto no encontrado en la base de datos.");
    }
    $category = trim($_POST['category'] ?? $current['category']);
    $productName = trim($_POST['name'] ?? $current['name']);
    $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category);
    $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $productName);

    $categoryDir = $baseDir . "/pages/inventory/media/$safeCategory";
    $productDir = "$categoryDir/$safeProduct";

    if (!is_dir($categoryDir)) mkdir($categoryDir, 0777, true);
    if (!is_dir($productDir)) mkdir($productDir, 0777, true);

    $photoPath = $current['photo'];

    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {
        $photoName = uniqid('photo_') . "_" . basename($_FILES["photo"]["name"]);
        $targetPath = "$productDir/$photoName";

        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $targetPath)) {
            throw new Exception("❌ Error al guardar la nueva imagen.");
        }
        if (!empty($current['photo'])) {
            $oldPhotoAbs = $baseDir . '/pages/inventory/' . ltrim($current['photo'], '/');
            if (file_exists($oldPhotoAbs)) @unlink($oldPhotoAbs);
        }

        $photoPath = "media/$safeCategory/$safeProduct/$photoName";
    }

    $product = new Product(
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

    $crud->updateProduct($product, $id);

    echo json_encode(["success" => true, "message" => "✏️ Ingrediente actualizado correctamente"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
