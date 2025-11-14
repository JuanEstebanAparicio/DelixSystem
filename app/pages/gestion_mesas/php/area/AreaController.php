<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../../../middleware/controller_bootstrap.php';  // 🟢 Permisos + funciones globales
require_once __DIR__ . '/products_constructor.php';
require_once __DIR__ . '/storage_crud.php';
require_once __DIR__ . '/../../../../config/supabase_img.php';
require_once __DIR__ . '/../../../../config/supabase.php';

try {

    // ======================================================
    // 🟢 USO ESTÁNDAR DEL BOOTSTRAP
    // ======================================================
    $isAjax = isAjaxRequest();

    // 🔹 Obtener propietario real (propietario o empleado del propietario)
    [$id_user, $error] = getPropietarioID($conexion);
    if ($error) {
        returnJson($isAjax, 'error', $error);
    }

    // ======================================================
    // 🧩 MAPEO DE ACCIONES / PERMISOS
    // ======================================================
    $roundAction = $_POST['action'] ?? '';
    $actionMap = [
        'create' => 'crear',
        'add'    => 'crear',
        'update' => 'editar',
        'edit'   => 'editar',
        'delete' => 'eliminar'
    ];
    $accion = $actionMap[$roundAction] ?? null;

    if (!$accion) {
        returnJson($isAjax, 'error', 'Acción no válida.');
    }

    // ======================================================
    // 🛡️ VERIFICAR PERMISOS SOLO PARA EMPLEADOS
    // ======================================================
    if (!isset($_SESSION['usuario'])) { // si NO es propietario
        verifyRoleAccess('inventario', $accion);
    }

    $crud = new storage_crud();

    $toDate = fn($d) => empty($d) ? null : (new DateTime($d))->format("Y-m-d H:i:s");


    // ======================================================
    // 🟩 CREAR PRODUCTO
    // ======================================================
    if ($accion === 'crear') {

        $category = trim($_POST['category'] ?? '');
        $newCategory = trim($_POST['new_category'] ?? '');

        if ($category === '__new__' && $newCategory !== '') {
            $category = $newCategory;
        }

        $name = trim($_POST['name'] ?? '');
        if (!$name) returnJson($isAjax, 'error', 'El nombre del ingrediente es obligatorio.');

        $photo = null;
        if (!empty($_FILES['photo']['name'])) {

            $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category ?: 'sin_categoria');
            $safeProduct  = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

            $path = "inventario/$id_user/$safeCategory/$safeProduct";

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

        returnJson($isAjax, 'success', 'Ingrediente agregado correctamente.');
    }


    // ======================================================
    // 🟨 EDITAR PRODUCTO
    // ======================================================
    if ($accion === 'editar') {

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) returnJson($isAjax, 'error', 'ID inválido.');

        $current = $crud->getProductById($id, $id_user);
        if (!$current) returnJson($isAjax, 'error', 'Ingrediente no encontrado.');

        $category = trim($_POST['category'] ?? $current['category']);
        $newCategory = trim($_POST['new_category'] ?? '');

        if ($category === '__new__' && $newCategory !== '') {
            $category = $newCategory;
        }

        $name = trim($_POST['name'] ?? $current['name']);

        $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category ?: 'sin_categoria');
        $safeProduct  = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

        $photo = $current['photo'];

        if (!empty($_FILES['photo']['name'])) {

            $path = "inventario/$id_user/$safeCategory/$safeProduct";

            if (!empty($photo)) {
                $relative = str_replace(rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/storage_img/", '', $photo);
                supabaseDeleteImage('storage_img', $relative);
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

        returnJson($isAjax, 'success', 'Ingrediente actualizado correctamente.');
    }


    // ======================================================
    // 🟥 ELIMINAR PRODUCTO
    // ======================================================
    if ($accion === 'eliminar') {

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) returnJson($isAjax, 'error', 'ID inválido.');

        $producto = $crud->getProductById($id, $id_user);
        if (!$producto) returnJson($isAjax, 'error', 'Ingrediente no encontrado.');

        if (!empty($producto['photo'])) {
            $relative = str_replace(rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/storage_img/", '', $producto['photo']);
            supabaseDeleteImage('storage_img', $relative);
        }

        $crud->deleteProduct($id, $id_user);

        returnJson($isAjax, 'success', 'Ingrediente eliminado correctamente.');
    }


} catch (Exception $e) {
    ob_clean();
    returnJson($isAjax, 'error', $e->getMessage());
}
?>
