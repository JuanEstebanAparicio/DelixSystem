<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once(__DIR__ . '/products_constructor.php');
require_once(__DIR__ . '/storage_crud.php');
require_once(__DIR__ . '/../../../../config/supabase_img.php'); // helper de Supabase

try {
    if (empty($_SESSION['usuario']['id'])) {
        throw new Exception("⚠️ No hay usuario autenticado.");
    }

    $id_user = intval($_SESSION['usuario']['id']);
    $crud = new storage_crud();

    $roundAction = $_POST['action'] ?? '';
    $actionMap = [
        'create' => 'add',
        'add' => 'add',
        'update' => 'edit',
        'edit' => 'edit',
        'delete' => 'delete'
    ];
    $method = $actionMap[$roundAction] ?? null;
    if (!$method) throw new Exception("Acción no válida.");

    $toDate = fn($d) => empty($d) ? null : (new DateTime($d))->format("Y-m-d H:i:s");

    // ======================================================
    // 🟩 CREAR PRODUCTO
    // ======================================================
    if ($method === 'add') {
        $category = trim($_POST['category'] ?? '');
        $newCategory = trim($_POST['new_category'] ?? '');
        if ($category === '__new__' && $newCategory !== '') {
            $category = $newCategory;
        }

        $name = trim($_POST['name'] ?? '');
        if (!$name) throw new Exception("El nombre del ingrediente es obligatorio.");

        // Subir imagen a Supabase si existe
        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category ?: 'sin_categoria');
            $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
            $path = "inventario/$id_user/$safeCategory/$safeProduct"; // ruta dentro del bucket
            $photo = supabaseUploadImage($_FILES['photo'], 'storage_img', $path);
        }

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

    // ======================================================
    // 🟨 EDITAR PRODUCTO
    // ======================================================
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

        // Si sube nueva imagen, reemplazarla en Supabase
        if (!empty($_FILES['photo']['name'])) {
            $path = "inventario/$id_user/$safeCategory/$safeProduct";

            // Eliminar imagen anterior si existía
            if (!empty($photo)) {
                supabaseDeleteImage('storage_img', str_replace(rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/storage_img/", '', $photo));
            }

            $photo = supabaseUploadImage($_FILES['photo'], 'storage_img', $path);
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

    // ======================================================
    // 🟥 ELIMINAR PRODUCTO
    // ======================================================
    if ($method === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception("ID inválido.");

        $producto = $crud->getProductById($id, $id_user);
        if (!$producto) throw new Exception("Ingrediente no encontrado.");

        // Eliminar imagen del bucket si existía
        if (!empty($producto['photo'])) {
            supabaseDeleteImage('storage_img', str_replace(rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/storage_img/", '', $producto['photo']));
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
