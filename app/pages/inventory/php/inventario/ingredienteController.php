<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once(__DIR__ . '/products.php');
require_once(__DIR__ . '/storage_crud.php');

try {
    if (empty($_SESSION['usuario']['id'])) {
        throw new Exception("⚠️ No hay usuario autenticado.");
    }

    $id_user = intval($_SESSION['usuario']['id']);
    $crud = new storage_crud();

    $baseDir = dirname(__DIR__, 2);
    $mediaRoot = $baseDir . '/media';

    $roundAction = $_POST['action'] ?? '';
    $actionMap = [
        'create' => 'add',
        'add' => 'add',
        'update' => 'edit',
        'edit' => 'edit',
        'delete' => 'delete'
    ];
    $method = $actionMap[$roundAction] ?? null;

    if (!$method) {
        throw new Exception("Acción no válida.");
    }

    $toDate = function ($d) {
        if (empty($d)) return null;
        return (new DateTime($d))->format("Y-m-d H:i:s");
    };

    $clearDirectory = function ($dir) {
        if (!is_dir($dir)) return;
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->clearDirectory($path);
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
    };

    $uploadPhoto = function ($file, $safeCategory, $safeProduct, $mediaRoot, $clearDirectory) {
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return null;

        $categoryDir = "$mediaRoot/$safeCategory";
        $productDir = "$categoryDir/$safeProduct";

        if (!is_dir($productDir) && !mkdir($productDir, 0777, true)) {
            throw new Exception("No se pudo crear el directorio del producto.");
        }
        $clearDirectory($productDir);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid("photo_") . "." . strtolower($ext);
        $target = "$productDir/$name";

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new Exception("Error al guardar la imagen.");
        }

        return "media/$safeCategory/$safeProduct/$name";
    };

    if ($method === 'add') {
        $category = trim($_POST['category'] ?? '');
        $newCategory = trim($_POST['new_category'] ?? '');
        if ($category === '__new__' && $newCategory !== '') {
            $category = $newCategory;
        }

        $name = trim($_POST['name'] ?? '');
        if (!$name) {
            throw new Exception("El nombre del ingrediente es obligatorio.");
        }

        $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category ?: 'sin_categoria');
        $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

        $photo = $uploadPhoto($_FILES['photo'] ?? [], $safeCategory, $safeProduct, $mediaRoot, $clearDirectory);

        $producto = new Product(
            $id_user,
            $name,
            floatval($_POST['amount'] ?? 0),
            floatval($_POST['minimum_quantity'] ?? 0),
            $_POST['unit'] ?? '',
            floatval($_POST['unit_cost'] ?? 0),
            $category,
            $toDate($_POST['fecha_ingreso'] ?? null),
            $toDate($_POST['fecha_vencimiento'] ?? null),
            $_POST['batch'] ?? '',
            $_POST['description'] ?? '',
            $_POST['location'] ?? '',
            $_POST['state'] ?? 'Activo',
            $_POST['supplier'] ?? '',
            $photo
        );

        $crud->insertProduct($producto);
        echo json_encode(["success" => true, "message" => "✅ Ingrediente agregado correctamente."]);
        exit;
    }

    if ($method === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception("ID inválido.");

        $current = $crud->getProductById($id, $id_user);
        if (!$current) throw new Exception("Ingrediente no encontrado.");

        $category = trim($_POST['category'] ?? $current['category']);
        $newCategory = trim($_POST['new_category'] ?? '');
        if ($category === '__new__' && $newCategory !== '') {
            $category = $newCategory;
        }

        $name = trim($_POST['name'] ?? $current['name']);
        $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category ?: 'sin_categoria');
        $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

        $photo = $current['photo'];
        if (!empty($_FILES['photo']['name'])) {
            $newPhoto = $uploadPhoto($_FILES['photo'], $safeCategory, $safeProduct, $mediaRoot, $clearDirectory);
            if ($newPhoto) {
                $photo = $newPhoto;
            }
        }

        $producto = new Product(
            $id_user,
            $name,
            floatval($_POST['amount'] ?? $current['amount']),
            floatval($_POST['minimum_quantity'] ?? $current['minimum_quantity']),
            $_POST['unit'] ?? $current['unit'],
            floatval($_POST['unit_cost'] ?? $current['unit_cost']),
            $category,
            $toDate($_POST['fecha_ingreso'] ?? $current['entrance_date']),
            $toDate($_POST['fecha_vencimiento'] ?? $current['expiration_date']),
            $_POST['batch'] ?? $current['batch'],
            $_POST['description'] ?? $current['description'],
            $_POST['location'] ?? $current['location'],
            $_POST['state'] ?? $current['state'],
            $_POST['supplier'] ?? $current['supplier'],
            $photo
        );

        $crud->updateProduct($producto, $id);
        echo json_encode(["success" => true, "message" => "✅ Ingrediente actualizado correctamente."]);
        exit;
    }

    if ($method === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception("ID inválido.");

        $producto = $crud->getProductById($id, $id_user);
        if (!$producto) throw new Exception("Ingrediente no encontrado.");

        if (!empty($producto['photo'])) {
            $file = $baseDir . '/' . $producto['photo'];
            if (file_exists($file)) @unlink($file);
        }

        $crud->deleteProduct($id, $id_user);
        echo json_encode(["success" => true, "message" => "🗑️ Ingrediente eliminado correctamente."]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
