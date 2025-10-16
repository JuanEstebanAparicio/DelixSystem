<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../../../../config/supabase.php';
include_once __DIR__ . '/AreaModel.php';

$areaModel = new AreaModel($conexion);
$accion = $_REQUEST['accion'] ?? '';

switch ($accion) {
    // ✅ Crear
    case 'crear':
        $nombre = trim($_POST['nombre_area'] ?? '');
        if (!empty($nombre)) {
            if ($areaModel->areaExiste($nombre)) {
                header("Location: ../../view/gestion_mesas.php?error=area_existente");
                exit;
            }
            $areaModel->crearArea($nombre);
        }
        header("Location: ../../view/gestion_mesas.php");
        exit;

    // ✅ Editar
    case 'editar':
        $id_area = $_POST['id_area'] ?? null;
        $nombre = trim($_POST['nombre_area'] ?? '');
        if ($id_area && !empty($nombre)) {
            if ($areaModel->areaExiste($nombre, $id_area)) {
                header("Location: ../../view/gestion_mesas.php?error=area_existente");
                exit;
            }
            $areaModel->editarArea($id_area, $nombre);
        }
        header("Location: ../../view/gestion_mesas.php");
        exit;

    // ✅ Eliminar
    case 'eliminar':
        $id_area = $_GET['id_area'] ?? null;
        if ($id_area) {
            $areaModel->eliminarArea($id_area);
        }
        header("Location: ../../view/gestion_mesas.php");
        exit;

    // 🚫 Si no hay acción válida
    default:
        header("Location: ../../view/gestion_mesas.php");
        exit;
}
