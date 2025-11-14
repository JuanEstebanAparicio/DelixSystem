<?php

// 🔧 Forzar a que siempre se trate como AJAX (evita redirecciones HTML)
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

// 🔧 Iniciar sesión si no existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔧 Limpiar headers previos del bootstrap
header_remove("Content-Type");
header("Content-Type: application/json; charset=utf-8");

// 🔧 ACTIVAR BUFFER PARA ELIMINAR CUALQUIER OUTPUT ACCIDENTAL
ob_start();

// 🟢 Bootstrap de permisos
require_once __DIR__ . '/../../../../middleware/controller_bootstrap.php';

require_once(__DIR__ . '/products_constructor.php');
require_once(__DIR__ . '/storage_crud.php');
require_once(__DIR__ . '/../../../../config/supabase_img.php');
require_once(__DIR__ . '/../../../../config/supabase.php');

try {

    // ======================================================
    // 🟢 Obtener propietario real (propietario o empleado)
    // ======================================================
    $isAjax = true; // ya NO confiamos en PHP, forzamos AJAX siempre

    [$id_user, $error] = getPropietarioID($conexion);
    if ($error) {
        ob_clean();
        returnJson(true, 'error', $error);
    }

    $crud = new storage_crud();

    // ======================================================
    // 🧩 MAPEO DE ACCIONES → PERMISOS
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
        ob_clean();
        returnJson(true, 'error', 'Acción no válida.');
    }

    // ======================================================
    // 🛡️ Verificar permisos (solo empleados)
    // ======================================================
    if (!isset($_SESSION['usuario'])) { 
        verifyRoleAccess('inventario', $accion);
    }

    // ======================================================
    // 🔧 Helper para fechas
    // ======================================================
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
        if (!$name) {
            ob_clean();
            returnJson(true, 'error', 'El nombre del ingrediente es obligatorio.');
        }

        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category ?: 'sin_categoria');
            $safeProduct = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
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

        ob_clean();
        returnJson(true, 'success', 'Ingrediente agregado correctamente.');
    }

    // ======================================================
    // 🟨 EDITAR PRODUCTO
    // ======================================================
    if ($accion === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            ob_clean();
            returnJson(true, 'error', 'ID inválido.');
        }

        $current = $crud->getProductById($id, $id_user);
        if (!$current) {
            ob_clean();
            returnJson(true, 'error', 'Ingrediente no encontrado.');
        }

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

        ob_clean();
        returnJson(true, 'success', 'Ingrediente actualizado correctamente.');
    }

    // ======================================================
    // 🟥 ELIMINAR PRODUCTO
    // ======================================================
    if ($accion === 'eliminar') {

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            ob_clean();
            returnJson(true, 'error', 'ID inválido.');
        }

        $producto = $crud->getProductById($id, $id_user);
        if (!$producto) {
            ob_clean();
            returnJson(true, 'error', 'Ingrediente no encontrado.');
        }

        if (!empty($producto['photo'])) {
            $relative = str_replace(rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/storage_img/", '', $producto['photo']);
            supabaseDeleteImage('storage_img', $relative);
        }

        $crud->deleteProduct($id, $id_user);

        ob_clean();
        returnJson(true, 'success', 'Ingrediente eliminado correctamente.');
    }

} catch (Exception $e) {
    ob_clean();
    returnJson(true, 'error', $e->getMessage());
}

?>
