<?php
$baseDir = dirname(__DIR__, 3);
require_once($baseDir . '/config/supabase.php');
require_once(__DIR__ . '/products.php');
require_once(__DIR__ . '/storage_crud.php');

header('Content-Type: application/json');
$pdo = $conexion ?? null;

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id'])) {
        throw new Exception("🚫 Solicitud no válida o ID faltante.");
    }

    $id = $_POST['id'];
    $photoPath = $_POST['photo_actual'] ?? null;
    $category = trim($_POST['category'] ?? '');
    $productName = trim($_POST['name'] ?? '');
    $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category);
    $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $productName);
    $categoryDir = $baseDir . "/pages/inventory/media/$safeCategory";
    $productDir = "$categoryDir/$safeProduct";

    if (!is_dir($categoryDir)) mkdir($categoryDir, 0777, true);
    if (!is_dir($productDir)) mkdir($productDir, 0777, true);

    // Subir nueva foto (si hay)
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === 0) {
        $photoName = uniqid() . "_" . basename($_FILES["photo"]["name"]);
        $targetPath = "$productDir/$photoName";

        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $targetPath)) {
            throw new Exception("❌ Error al mover la nueva imagen.");
        }

        if (!empty($_POST['photo_actual'])) {
            $oldPhotoAbs = $baseDir . '/pages/inventory/' . ltrim($_POST['photo_actual'], '/');
            if (file_exists($oldPhotoAbs)) @unlink($oldPhotoAbs);
        }

        $photoPath = "media/$safeCategory/$safeProduct/$photoName";
    }

    $product = new Product(
        $productName,
        $_POST['amount'] ?? 0,
        $_POST['minimum_quantity'] ?? 0,
        $_POST['unit'] ?? '',
        $_POST['unit_cost'] ?? 0,
        $category,
        $_POST['entrance_date'] ?? null,
        $_POST['expiration_date'] ?? null,
        $_POST['batch'] ?? null,
        $_POST['description'] ?? null,
        $_POST['location'] ?? null,
        $_POST['state'] ?? 'Activo',
        $_POST['supplier'] ?? null,
        $photoPath
    );

    $crud = new storage_crud($pdo);
    $crud->updateProduct($product, $id);

    echo json_encode(["success" => true, "message" => "✏️ Ingrediente actualizado correctamente"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
