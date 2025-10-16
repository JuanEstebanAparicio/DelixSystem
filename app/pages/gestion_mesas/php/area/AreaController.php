<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../../../../config/supabase.php';
include __DIR__ . '/AreaModel.php';

$areaModel = new AreaModel($conexion);

// Acción por GET → Eliminar
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    $id_area = $_GET['id_area'] ?? null;

    if ($id_area) {
        $areaModel->eliminarArea($id_area);
    }

    header("Location: ../../view/gestion_mesas.php");
    exit;
}

// Acción por POST → Crear o Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $nombre = trim($_POST['nombre_area'] ?? '');
    $id_area = $_POST['id_area'] ?? null;

    if (!empty($nombre)) {
        // Validar duplicados
        if ($areaModel->areaExiste($nombre, $accion === 'editar' ? $id_area : null)) {
            header("Location: ../../view/gestion_mesas.php?error=area_existente");
            exit;
        }

        // Ejecutar acción
        if ($accion === 'crear') {
            $areaModel->crearArea($nombre);
        } elseif ($accion === 'editar' && $id_area) {
            $areaModel->editarArea($id_area, $nombre);
        }
    }

    header("Location: ../../view/gestion_mesas.php");
    exit;
}

// Si no hay acción válida, redirigir
header("Location: ../../view/gestion_mesas.php");
exit;

