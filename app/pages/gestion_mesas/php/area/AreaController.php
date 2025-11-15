<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/area_debug.log');

header('Content-Type: application/json');

try {
    // Carga única centralizada
    require_once __DIR__ . '/../../../../middleware/controller_bootstrap.php';
    require_once __DIR__ . '/AreaModel.php';
} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al cargar dependencias',
        'debug'   => $e->getMessage(),
        'trace'   => $e->getFile() . ':' . $e->getLine(),
    ]);
    exit;
}

// ✅ Verificar conexión PDO
if (!isset($conexion) || !$conexion instanceof PDO) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Conexión a base de datos no inicializada',
    ]);
    exit;
}

// Inicialización básica
$areaModel = new AreaModel($conexion);
$isAjax = isAjaxRequest();
$accion = $_REQUEST['accion'] ?? '';

// ✅ Obtener propietario (centralizado)
[$id_propietario, $error] = getPropietarioID($conexion);
if ($error) {
    returnJson($isAjax, 'error', $error);
}

try {
    switch ($accion) {
        // 🟢 Crear área
       case 'crear':
    verifyRoleAccess('areas', 'crear');

    $nombre = trim($_POST['nombre_area'] ?? '');
    if (empty($nombre)) {
        returnJson($isAjax, 'error', 'El nombre del área es obligatorio.');
    }

    if ($areaModel->areaExiste($nombre, $id_propietario)) {
        returnJson($isAjax, 'error', 'Ya existe un área con ese nombre en tu cuenta.');
    }

    $ok = $areaModel->crearArea($nombre, $id_propietario);
    $id_area = $conexion->lastInsertId();

    if (!$ok) {
        returnJson($isAjax, 'error', 'Error al crear el área.');
    }

    // ◀️ aquí consultamos el nombre del restaurante
    $stmtR = $conexion->prepare("SELECT restaurant_name FROM areas WHERE id_area = ?");
    $stmtR->execute([$id_area]);
    $restaurant_name = $stmtR->fetchColumn();

    returnJson($isAjax, 'success', 'Área creada correctamente.', [
        'id_area' => $id_area,
        'nombre'  => $nombre,
        'restaurant_name' => $restaurant_name
    ]);

            break;

        // 🟠 Editar área
        case 'editar':
            verifyRoleAccess('areas', 'editar');

            $id_area = $_POST['id_area'] ?? null;
            $nombre  = trim($_POST['nombre_area'] ?? '');

            if (!$id_area || empty($nombre)) {
                returnJson($isAjax, 'error', 'Datos incompletos para editar.');
            }

            if ($areaModel->areaExiste($nombre, $id_propietario, $id_area)) {
                returnJson($isAjax, 'error', 'Ya existe un área con ese nombre.');
            }

            $areaModel->editarArea($id_area, $nombre, $id_propietario);
            returnJson($isAjax, 'success', 'Área actualizada correctamente.', [
                'id_area' => $id_area,
                'nombre'  => $nombre
            ]);
            break;

        // 🔴 Eliminar área
        case 'eliminar':
            verifyRoleAccess('areas', 'eliminar');

            $id_area = $_POST['id_area'] ?? $_GET['id_area'] ?? null;
            if (!$id_area) {
                returnJson($isAjax, 'error', 'ID de área no válido.');
            }

            $areaModel->eliminarArea($id_area, $id_propietario);
            returnJson($isAjax, 'success', 'Área eliminada correctamente.', [
                'id_area' => $id_area
            ]);
            break;

        // 🔵 Reordenar áreas
        case 'ordenar':
            verifyRoleAccess('areas', 'ordenar');

            if (!isset($_POST['orden']) || !is_array($_POST['orden'])) {
                returnJson($isAjax, 'error', 'Datos de orden inválidos.');
            }

            if ($areaModel->actualizarOrden($_POST['orden'])) {
                returnJson($isAjax, 'success', 'Orden actualizado correctamente.');
            } else {
                returnJson($isAjax, 'error', 'Error al guardar el orden.');
            }
            break;

        default:
            returnJson($isAjax, 'error', 'Acción no válida.');
    }
} catch (Throwable $e) {
    error_log("⚠️ Error en AreaController: " . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error interno en controlador',
        'debug'   => $e->getMessage(),
        'trace'   => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}