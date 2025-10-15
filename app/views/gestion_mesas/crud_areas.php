<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../../config/supabase.php';

// Acción por método GET (eliminar)
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
    $nombre = trim($_POST['nombre_area'] ?? '');
    $id_area = $_POST['id_area'] ?? null;

    if (!empty($nombre)) {
        // 🔍 Validar duplicados
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM areas WHERE LOWER(nombre) = LOWER(?)" . ($accion === 'editar' ? " AND id_area != ?" : ""));
        $params = ($accion === 'editar') ? [$nombre, $id_area] : [$nombre];
        $stmt->execute($params);
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
            // ⚠️ Ya existe un área con ese nombre
            header("Location: gestion_mesas.php?error=area_existente");
            exit;
        }

        // ✅ Crear o editar según acción
        if ($accion === 'crear') {
            $stmt = $conexion->prepare("INSERT INTO areas (nombre) VALUES (?)");
            $stmt->execute([$nombre]);
        } elseif ($accion === 'editar' && $id_area) {
            $stmt = $conexion->prepare("UPDATE areas SET nombre = ? WHERE id_area = ?");
            $stmt->execute([$nombre, $id_area]);
        }
    }

    header("Location: gestion_mesas.php");
    exit;
}

// Si no hay acción, redirige
header("Location: gestion_mesas.php");
exit;
?>
