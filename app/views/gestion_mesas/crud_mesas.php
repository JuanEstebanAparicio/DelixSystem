<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../../config/supabase.php';

// Acción por método GET (eliminar)
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    $id_mesa = $_GET['id_mesa'] ?? null;

    if ($id_mesa) {
        $stmt = $conexion->prepare("DELETE FROM mesas WHERE id_mesa = ?");
        $stmt->execute([$id_mesa]);
    }
    header("Location: gestion_mesas.php");
    exit;
}

// Acción por método POST (crear o editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id_area = $_POST['id_area'] ?? null;
    $nombre = trim($_POST['nombre_mesa'] ?? '');
    $id_mesa = $_POST['id_mesa'] ?? null;

    if (!empty($nombre) && !empty($id_area)) {
        // 🔍 Validar duplicado dentro del mismo área
        $stmt = $conexion->prepare("
            SELECT COUNT(*) 
            FROM mesas 
            WHERE LOWER(nombre) = LOWER(?) 
            AND id_area = ? 
            " . ($accion === 'editar' ? "AND id_mesa != ?" : "")
        );
        $params = ($accion === 'editar') ? [$nombre, $id_area, $id_mesa] : [$nombre, $id_area];
        $stmt->execute($params);
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
            // ⚠️ Ya existe una mesa con ese nombre en el área
            header("Location: gestion_mesas.php?error=mesa_existente");
            exit;
        }

        // ✅ Crear o editar según acción
        if ($accion === 'crear') {
            $stmt = $conexion->prepare("INSERT INTO mesas (id_area, nombre) VALUES (?, ?)");
            $stmt->execute([$id_area, $nombre]);
        } elseif ($accion === 'editar' && $id_mesa) {
            $stmt = $conexion->prepare("UPDATE mesas SET id_area = ?, nombre = ? WHERE id_mesa = ?");
            $stmt->execute([$id_area, $nombre, $id_mesa]);
        }
    }

    header("Location: gestion_mesas.php");
    exit;
}

// Si no hay acción, redirige
header("Location: gestion_mesas.php");
exit;
?>
