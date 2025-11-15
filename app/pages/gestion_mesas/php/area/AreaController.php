<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/area_debug.log');

header('Content-Type: application/json');

try {
    // Bootstrap general + Auditoría + Roles
    require_once __DIR__ . '/../../../../middleware/controller_bootstrap.php';

    // Modelo
    require_once __DIR__ . '/AreaModel.php';

} catch (Throwable $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al cargar dependencias',
        'debug'   => $e->getMessage(),
        'trace'   => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}

// Validar conexión
if (!isset($conexion) || !$conexion instanceof PDO) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Conexión a base de datos no inicializada'
    ]);
    exit;
}

$areaModel = new AreaModel($conexion);
$isAjax = isAjaxRequest();
$accion = $_REQUEST['accion'] ?? '';

// Obtener el propietario real
[$id_propietario, $error] = getPropietarioID($conexion);
if ($error) {
    returnJson($isAjax, 'error', $error);
}


try {

    switch ($accion) {

        /* =====================================================
         * 🟢 CREAR ÁREA
         * ===================================================== */
        case 'crear':
            verifyRoleAccess('areas', 'crear');

            $nombre = trim($_POST['nombre_area'] ?? '');

            if (empty($nombre)) {
                returnJson($isAjax, 'error', 'El nombre del área es obligatorio.');
            }

            if ($areaModel->areaExiste($nombre, $id_propietario)) {
                returnJson($isAjax, 'error', 'Ya existe un área con ese nombre.');
            }

            $ok = $areaModel->crearArea($nombre, $id_propietario);
            $id_area = $conexion->lastInsertId();

            if (!$ok) {
                returnJson($isAjax, 'error', 'Error al crear el área.');
            }

            // Auditoría
            auditLog('areas', 'crear', [
                'target_table' => 'areas',
                'target_id'    => $id_area,
                'new'          => ['nombre' => $nombre]
            ]);

            returnJson($isAjax, 'success', 'Área creada correctamente.', [
                'id_area' => $id_area,
                'nombre'  => $nombre
            ]);
            break;


        /* =====================================================
         * 🟠 EDITAR ÁREA
         * ===================================================== */
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

            // Obtener OLD antes del cambio
            $oldData = $areaModel->getAreaById($id_area, $id_propietario);

            $areaModel->editarArea($id_area, $nombre, $id_propietario);

            // Auditoría
            auditLog('areas', 'editar', [
                'target_table' => 'areas',
                'target_id'    => $id_area,
                'old'          => $oldData,
                'new'          => ['nombre' => $nombre]
            ]);

            returnJson($isAjax, 'success', 'Área actualizada correctamente.', [
                'id_area' => $id_area,
                'nombre'  => $nombre
            ]);
            break;


        /* =====================================================
         * 🔴 ELIMINAR ÁREA
         * ===================================================== */
        case 'eliminar':
            verifyRoleAccess('areas', 'eliminar');

            $id_area = $_POST['id_area'] ?? $_GET['id_area'] ?? null;

            if (!$id_area) {
                returnJson($isAjax, 'error', 'ID de área no válido.');
            }

            // Obtener OLD antes de eliminar
            $oldData = $areaModel->getAreaById($id_area, $id_propietario);

            $areaModel->eliminarArea($id_area, $id_propietario);

            // Auditoría
            auditLog('areas', 'eliminar', [
                'target_table' => 'areas',
                'target_id'    => $id_area,
                'old'          => $oldData
            ]);

            returnJson($isAjax, 'success', 'Área eliminada correctamente.', [
                'id_area' => $id_area
            ]);
            break;


        /* =====================================================
         * 🔵 ORDENAR ÁREAS
         * ===================================================== */
        case 'ordenar':
            verifyRoleAccess('areas', 'ordenar');

            if (!isset($_POST['orden']) || !is_array($_POST['orden'])) {
                returnJson($isAjax, 'error', 'Datos de orden inválidos.');
            }

            $areaModel->actualizarOrden($_POST['orden']);

            // Auditoría
            auditLog('areas', 'ordenar', [
                'meta' => ['orden' => $_POST['orden']]
            ]);

            returnJson($isAjax, 'success', 'Orden actualizado correctamente.');
            break;


        /* =====================================================
         * 🚫 ACCIÓN NO VÁLIDA
         * ===================================================== */
        default:
            returnJson($isAjax, 'error', 'Acción no válida.');
    }

} catch (Throwable $e) {

    // Registrar error en log del sistema
    error_log("⚠️ Error en AreaController: " . $e->getMessage());

    echo json_encode([
        'status'  => 'error',
        'message' => 'Error interno en controlador',
        'debug'   => $e->getMessage(),
        'trace'   => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}
