<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../../config/supabase.php';

// Acción por método GET (por ejemplo, eliminar)
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    $id_area = $_GET['id_area'] ?? null;

    if ($id_area) {
        $stmt = $conexion->prepare("DELETE FROM areas WHERE id_area = ?");
        $stmt->execute([$id_area]);
    }
    header("Location: gestion_mesas.php");
    exit;
}

// Acción por método POST (crear o editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $nombre = $_POST['nombre_area'] ?? '';
    $id_area = $_POST['id_area'] ?? null;

    if ($accion === 'crear' && !empty($nombre)) {
        $stmt = $conexion->prepare("INSERT INTO areas (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
    } elseif ($accion === 'editar' && $id_area && !empty($nombre)) {
        $stmt = $conexion->prepare("UPDATE areas SET nombre = ? WHERE id_area = ?");
        $stmt->execute([$nombre, $id_area]);
    }

    header("Location: gestion_mesas.php");
    exit;
}

// Si no hay acción, redirige
header("Location: gestion_mesas.php");
exit;
?>
