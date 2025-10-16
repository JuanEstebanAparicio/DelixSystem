<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../../../../config/supabase.php';
include __DIR__ . '/MesaModel.php';

$mesaModel = new MesaModel($conexion);

// 🔹 Eliminar mesa
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    $id_mesa = $_GET['id_mesa'] ?? null;

    if ($id_mesa) {
        $mesaModel->eliminarMesa($id_mesa);
    }

    header("Location: ../../view/gestion_mesas.php");
    exit;
}

// 🔹 Crear o editar mesa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id_area = $_POST['id_area'] ?? null;
    $nombre = trim($_POST['nombre_mesa'] ?? '');
    $id_mesa = $_POST['id_mesa'] ?? null;

    if (!empty($nombre) && !empty($id_area)) {
        // Validar duplicado dentro del mismo área
        if ($mesaModel->mesaExiste($nombre, $id_area, $accion === 'editar' ? $id_mesa : null)) {
            header("Location: ../../view/gestion_mesas.php?error=mesa_existente");
            exit;
        }

        // Crear o editar según acción
        if ($accion === 'crear') {
            $mesaModel->crearMesa($id_area, $nombre);
        } elseif ($accion === 'editar' && $id_mesa) {
            $mesaModel->editarMesa($id_mesa, $id_area, $nombre);
        }
    }

    header("Location: ../../view/gestion_mesas.php");
    exit;
}

// 🔹 Acción no válida → Redirigir
header("Location: ../../view/gestion_mesas.php");
exit;
