<?php
$baseDir = dirname(__DIR__, 3);

require_once($baseDir . '/config/supabase.php');
require_once(__DIR__ . '/products.php');
require_once(__DIR__ . '/storage_crud.php');

$pdo = $conexion ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $photoPath = null;

    // Sanitizar datos básicos
    $category = trim($_POST['category']);
    $productName = trim($_POST['name']);

    // Reemplazar caracteres inválidos para nombres de carpetas
    $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category);
    $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $productName);

    // Crear estructura de carpetas: media/<categoria>/<producto>
    $categoryDir = $baseDir . "/pages/inventory/media/$safeCategory";
    $productDir = "$categoryDir/$safeProduct";

    if (!is_dir($categoryDir)) mkdir($categoryDir, 0777, true);
    if (!is_dir($productDir)) mkdir($productDir, 0777, true);

    // Procesar imagen
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === 0) {
        $photoName = uniqid() . "_" . basename($_FILES["photo"]["name"]);
        $targetPath = "$productDir/$photoName";

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetPath)) {
            // Ruta relativa para guardar en la base de datos
            $photoPath = "media/$safeCategory/$safeProduct/$photoName";
        } else {
            die("❌ Error al mover la imagen al destino.");
        }
    }

    // Crear instancia del producto
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

    try {
        $crud = new storage_crud($pdo);
        $crud->createProduct($product);

        header("Location: ../view/ingredient_manager.php?success=1");
        exit();
    } catch (Exception $e) {
        die("❌ Error al registrar producto: " . $e->getMessage());
    }
} else {
    http_response_code(403);
    echo "🚫 Este recurso solo acepta solicitudes POST.";
}
?>
