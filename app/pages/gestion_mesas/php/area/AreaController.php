<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../../../../config/supabase.php';
include_once __DIR__ . '/AreaModel.php';

$areaModel = new AreaModel($conexion);
$accion = $_REQUEST['accion'] ?? '';
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

switch ($accion) {
    // ✅ Crear
    case 'crear':
        $nombre = trim($_POST['nombre_area'] ?? '');

        if (empty($nombre)) {
            returnJson($isAjax, 'error', 'El nombre del área es obligatorio.');
        }

        if ($areaModel->areaExiste($nombre)) {
            returnJson($isAjax, 'error', 'Ya existe un área con ese nombre.');
        }

        $areaModel->crearArea($nombre);
        $id_area = $conexion->lastInsertId();

        returnJson($isAjax, 'success', 'Área creada correctamente.', [
            'id_area' => $id_area,
            'nombre' => $nombre
        ]);
        break;

    // ✅ Editar
    case 'editar':
        $id_area = $_POST['id_area'] ?? null;
        $nombre = trim($_POST['nombre_area'] ?? '');

        if (!$id_area || empty($nombre)) {
            returnJson($isAjax, 'error', 'Datos incompletos.');
        }

        if ($areaModel->areaExiste($nombre, $id_area)) {
            returnJson($isAjax, 'error', 'Ya existe un área con ese nombre.');
        }

        $areaModel->editarArea($id_area, $nombre);

        returnJson($isAjax, 'success', 'Área actualizada correctamente.', [
            'id_area' => $id_area,
            'nombre' => $nombre
        ]);
        break;

    // ✅ Eliminar
    case 'eliminar':
        $id_area = $_POST['id_area'] ?? null;

        if (!$id_area) {
            returnJson($isAjax, 'error', 'ID de área no válido.');
        }

        $areaModel->eliminarArea($id_area);
        returnJson($isAjax, 'success', 'Área eliminada correctamente.', ['id_area' => $id_area]);
        break;

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
