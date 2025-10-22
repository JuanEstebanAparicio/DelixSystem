<?php
$baseDir = dirname(__DIR__, 3);

require_once($baseDir . '/config/supabase.php');
require_once(__DIR__ . '/products.php');
require_once(__DIR__ . '/storage_crud.php');

$pdo = $conexion ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {

    $id = $_POST['id'];
    $photoPath = $_POST['photo_actual'] ?? null; // nombre del campo para la foto actual (ajusta si usas otro)

    // Sanitizar entradas (usar los mismos nombres que en el form)
    $category = trim($_POST['category'] ?? '');
    $productName = trim($_POST['name'] ?? '');

    // Rutas seguras para carpetas
    $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category);
    $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $productName);

    // Estructura de carpetas
    $categoryDir = $baseDir . "/pages/inventory/media/$safeCategory";
    $productDir = "$categoryDir/$safeProduct";

    if (!is_dir($categoryDir)) mkdir($categoryDir, 0777, true);
    if (!is_dir($productDir)) mkdir($productDir, 0777, true);

    // --- PROCESAR FOTO NUEVA ---
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === 0) {
        $photoName = uniqid() . "_" . basename($_FILES["photo"]["name"]);
        $targetPath = "$productDir/$photoName";

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetPath)) {
            // eliminar foto vieja si existe (ruta absoluta)
            if (!empty($_POST['photo_actual'])) {
                $oldPhotoAbs = $baseDir . '/pages/inventory/' . ltrim($_POST['photo_actual'], '/');
                if (file_exists($oldPhotoAbs)) {
                    @unlink($oldPhotoAbs); // @ para evitar warning si falla
                }
            }
            // nueva ruta relativa para BD (igual que en create)
            $photoPath = "media/$safeCategory/$safeProduct/$photoName";
        } else {
            die("❌ Error al mover la nueva imagen al destino.");
        }
    }

    // --- INSTANCIAR OBJETO CON LOS MISMOS CAMPOS QUE CREATE.PHP ---
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

    // --- GUARDAR CAMBIOS ---
    try {
        $crud = new storage_crud($pdo);
        $crud->updateProduct($product, $id);
        header("Location: ../view/ingredient_manager.php?success=2");
        exit();
    } catch (Exception $e) {
        die("❌ Error al actualizar producto: " . $e->getMessage());
    }

} else {
    http_response_code(403);
    echo "🚫 Este recurso solo acepta solicitudes POST.";
}
?>
