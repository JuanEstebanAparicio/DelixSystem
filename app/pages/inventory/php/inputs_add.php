<?php
$baseDir = dirname(__DIR__, 3);

require_once($baseDir . '/config/supabase.php');
require_once(__DIR__ . '/products.php');
require_once(__DIR__ . '/storage_crud.php');

header('Content-Type: application/json');
$pdo = $conexion ?? null;

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("🚫 Solo se permiten solicitudes POST.");
    }

    $photoPath = null;
    $category = trim($_POST['category']);
    $productName = trim($_POST['name']);

    $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category);
    $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $productName);

    $categoryDir = $baseDir . "/pages/inventory/media/$safeCategory";
    $productDir = "$categoryDir/$safeProduct";

    if (!is_dir($categoryDir)) mkdir($categoryDir, 0777, true);
    if (!is_dir($productDir)) mkdir($productDir, 0777, true);

    // Subida de imagen
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === 0) {
        $photoName = uniqid() . "_" . basename($_FILES["photo"]["name"]);
        $targetPath = "$productDir/$photoName";

        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $targetPath)) {
            throw new Exception("❌ Error al mover la imagen al destino.");
        }

        $photoPath = "media/$safeCategory/$safeProduct/$photoName";
    }

    $product = new Product(
        $productName,
        $_POST['amount'],
        $_POST['minimum_quantity'],
        $_POST['unit'],
        $_POST['unit_cost'],
        $category,
        $_POST['entrance_date'] ?? null,
        $_POST['expiration_date'] ?? null,
        $_POST['batch'] ?? null,
        $_POST['description'] ?? null,
        $_POST['location'] ?? null,
        $_POST['state'] ?? 'Activo',
        $_POST['supplier'],
        $photoPath
    );

    $crud = new storage_crud($pdo);
    $crud->createProduct($product);

    echo json_encode(["success" => true, "message" => "✅ Ingrediente agregado correctamente"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
