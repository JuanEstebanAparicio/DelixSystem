<?php
$baseDir = dirname(__DIR__, 3);

require_once($baseDir . '/config/supabase.php');
require_once(__DIR__ . '/products.php');
require_once(__DIR__ . '/storage_crud.php');

$pdo = $conexion ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {

    $id = $_POST['id'];
    $photoPath = $_POST['foto_actual'] ?? null;

    // Sanitizar datos
    $category = trim($_POST['categoria']);
    $productName = trim($_POST['nombre']);

    // Asegurar nombres válidos de carpetas
    $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category);
    $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $productName);

    // Crear estructura de carpetas
    $categoryDir = $baseDir . "/pages/inventory/media/$safeCategory";
    $productDir = "$categoryDir/$safeProduct";

    if (!is_dir($categoryDir)) mkdir($categoryDir, 0777, true);
    if (!is_dir($productDir)) mkdir($productDir, 0777, true);

    // Procesar imagen (si se sube una nueva)
    if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === 0) {
        $photoName = uniqid() . "_" . basename($_FILES["foto"]["name"]);
        $targetPath = "$productDir/$photoName";

        // Subir nueva imagen
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetPath)) {
            // Eliminar imagen anterior (si existe)
            if (!empty($_POST['foto_actual']) && file_exists($baseDir . '/pages/inventory/' . $_POST['foto_actual'])) {
                unlink($baseDir . '/pages/inventory/' . $_POST['foto_actual']);
            }

            // Nueva ruta relativa
            $photoPath = "media/$safeCategory/$safeProduct/$photoName";
        } else {
            die("❌ Error al mover la nueva imagen al destino.");
        }
    }

    // Crear objeto producto actualizado
    $product = new Product(
        $productName,
        $_POST['cantidad'],
        $_POST['cantidad_minima'],
        $_POST['unidad'],
        $_POST['costo_unitario'],
        $category,
        $_POST['fecha_ingreso'],
        $_POST['fecha_vencimiento'],
        $_POST['lote'],
        $_POST['descripcion'],
        $_POST['ubicacion'],
        $_POST['estado'],
        $_POST['proveedor'],
        $photoPath
    );

    try {
        $crud = new storage_crud($pdo);
        $crud->updateProduct($product, $id);
        header("Location: ../view/ingredient_manager.php?success=2");
        exit();
    } catch (Exception $e) {
        die("❌ Error al actualizar: " . $e->getMessage());
    }

} else {
    http_response_code(403);
    echo "🚫 Este recurso solo acepta solicitudes POST.";
}
?>
