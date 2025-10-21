<?php
$baseDir = dirname(__DIR__, 2);
require_once($baseDir . '/config/supabase.php');
require_once(__DIR__ . '/products.php');
require_once(__DIR__ . '/storage_crud.php');

$pdo = $conexion;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {

    $id = $_POST['id'];

    $photoName = $_POST['foto_actual'] ?? null;
    if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == 0) {
        $photoName = uniqid() . "_" . $_FILES["foto"]["name"];
        $targetPath = $baseDir . '/Media/' . $photoName;

        if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $targetPath)) {
            die("Error al subir la imagen.");
        }
    }

    $product = new Product(
        $_POST['nombre'],
        $_POST['cantidad'],
        $_POST['cantidad_minima'],
        $_POST['unidad'],
        $_POST['costo_unitario'],
        $_POST['categoria'],
        $_POST['fecha_ingreso'],
        $_POST['fecha_vencimiento'],
        $_POST['lote'],
        $_POST['descripcion'],
        $_POST['ubicacion'],
        $_POST['estado'],
        $_POST['proveedor'],
        $photoName
    );

    try {
        $crud = new StorageCRUD($pdo);
        $crud->updateProduct($product, $id);
        header("Location: ../view/ingredient_manager.php?success=2");
        exit();
    } catch (Exception $e) {
        die("Error al actualizar: " . $e->getMessage());
    }
} else {
    http_response_code(403);
    echo "Este recurso solo acepta solicitudes POST.";
}
?>
