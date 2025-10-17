<?php
$baseDir = dirname(dirname(__DIR__));
require_once($baseDir . '/Model/Entity/products.php');
require_once($baseDir . '/Model/Crud/storage_crud.php');
require_once($baseDir . '/Model/Entity/connection.php');

$pdo = Connection::getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $photoName = null;
    if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == 0) {
        $photoName = uniqid() . "_" . $_FILES["foto"]["name"];
        $targetPath = $baseDir . '/Media/' . $photoName;

        if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $targetPath)) {
            die("Error al subir la imagen.");
        }
    }

    // Crear objeto Product
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
        $crud->createProduct($product);
        header("Location: http://localhost/Proyecto_de_aula/View/ingredient_manager.php?success=1");
        exit();
    } catch (Exception $e) {
        die("Error al registrar: " . $e->getMessage());
    }
} else {
    http_response_code(403);
    echo "Este recurso solo acepta solicitudes POST.";
}
?>
