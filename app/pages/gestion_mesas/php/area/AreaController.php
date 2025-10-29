<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../../../../config/supabase.php';
require_once __DIR__ . '/../../../../middleware/role_guard.php';

include_once __DIR__ . '/AreaModel.php';
session_start(); // ✅ asegúrate de tener la sesión iniciada

$areaModel = new AreaModel($conexion);
$accion = $_REQUEST['accion'] ?? '';
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$id_usuario = $_SESSION['usuario']['id'] ?? null; // ✅ ID del usuario logueado

if (!$id_usuario) {
    returnJson($isAjax, 'error', 'No hay sesión activa.');
}

switch ($accion) {

    // ✅ Crear área
    case 'crear':
        require_once __DIR__ . '/../../../../middleware/role_guard.php';
        canEmployeePerform(['GESTOR_MESAS', 'SUPERVISOR']); // 💡 Solo empleados con estos roles pueden crear

        $nombre = trim($_POST['nombre_area'] ?? '');

        if (empty($nombre)) {
            returnJson($isAjax, 'error', 'El nombre del área es obligatorio.');
        }

        if ($areaModel->areaExiste($nombre, $id_usuario)) {
            returnJson($isAjax, 'error', 'Ya existe un área con ese nombre en tu cuenta.');
        }

        $areaModel->crearArea($nombre, $id_usuario);
        $id_area = $conexion->lastInsertId();

        returnJson($isAjax, 'success', 'Área creada correctamente.', [
            'id_area' => $id_area,
            'nombre' => $nombre
        ]);
        break;

    // ✅ Editar área
    case 'editar':
        require_once __DIR__ . '/../../../../middleware/role_guard.php';
        canEmployeePerform(['GESTOR_MESAS', 'SUPERVISOR']); // 💡 Roles que pueden editar

        $id_area = $_POST['id_area'] ?? null;
        $nombre = trim($_POST['nombre_area'] ?? '');

        if (!$id_area || empty($nombre)) {
            returnJson($isAjax, 'error', 'Datos incompletos.');
        }

        if ($areaModel->areaExiste($nombre, $id_usuario, $id_area)) {
            returnJson($isAjax, 'error', 'Ya existe un área con ese nombre en tu cuenta.');
        }

        $areaModel->editarArea($id_area, $nombre, $id_usuario);

        returnJson($isAjax, 'success', 'Área actualizada correctamente.', [
            'id_area' => $id_area,
            'nombre' => $nombre
        ]);
        break;

    // ✅ Eliminar área
    case 'eliminar':
        require_once __DIR__ . '/../../../../middleware/role_guard.php';
        canEmployeePerform(['GESTOR_MESAS']); // 💡 Solo este rol puede eliminar

        $id_area = $_POST['id_area'] ?? $_GET['id_area'] ?? null;

        if (!$id_area) {
            returnJson($isAjax, 'error', 'ID de área no válido.');
        }

        $areaModel->eliminarArea($id_area, $id_usuario);
        returnJson($isAjax, 'success', 'Área eliminada correctamente.', ['id_area' => $id_area]);
        break;

    // ✅ Ordenar áreas (Drag & Drop)
    case 'ordenar':
        require_once __DIR__ . '/../../../../middleware/role_guard.php';
        canEmployeePerform(['GESTOR_MESAS', 'SUPERVISOR']); // 💡 Permitir a ambos roles reordenar

        if (!isset($_POST['orden']) || !is_array($_POST['orden'])) {
            echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
            exit;
        }

        if ($areaModel->actualizarOrden($_POST['orden'])) {
            echo json_encode(['status' => 'success', 'message' => 'Orden actualizado']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar el orden']);
        }
        exit;

    // 🚫 Acción no válida
    default:
        returnJson($isAjax, 'error', 'Acción no válida.');
        break;
}


// --- Función auxiliar para respuestas JSON ---
function returnJson($ajax, $status, $message, $data = [])
{
    if ($ajax) {
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit;
    }
    header("Location: ../../view/gestion_mesas.php?status={$status}&msg=" . urlencode($message));
    exit;
}
