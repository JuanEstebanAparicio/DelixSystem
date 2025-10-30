<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Registrar errores en archivo local (por si el navegador no muestra)
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/area_debug.log');

header('Content-Type: application/json'); // siempre devolver JSON al frontend

try {
    include __DIR__ . '/../../../../config/supabase.php';
    require_once __DIR__ . '/../../../../middleware/role_guard.php';
    include_once __DIR__ . '/AreaModel.php';
} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al cargar dependencias',
        'debug' => $e->getMessage(),
        'trace' => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificamos conexión
if (!isset($conexion) || !$conexion instanceof PDO) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Conexión a base de datos no inicializada'
    ]);
    exit;
}

$areaModel = new AreaModel($conexion);
$accion = $_REQUEST['accion'] ?? '';
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// 🔹 Determinar tipo de usuario
$id_usuario = $_SESSION['usuario']['id'] ?? null;
$id_empleado = $_SESSION['empleado_auth']['id'] ?? null;

if (!$id_usuario && !$id_empleado) {
    returnJson($isAjax, 'error', 'No hay sesión activa o no válida.');
}

// 🔹 Si es empleado, obtener el propietario real (para las áreas)
$id_propietario = $id_usuario;
if ($id_empleado && !$id_usuario) {
    try {
        $stmt = $conexion->prepare("SELECT user_id FROM employees WHERE id = ?");
        $stmt->execute([$id_empleado]);
        $id_propietario = $stmt->fetchColumn();

        if (!$id_propietario) {
            returnJson($isAjax, 'error', 'Empleado sin propietario asignado.');
        }
    } catch (Throwable $e) {
        returnJson($isAjax, 'error', 'Error al obtener propietario', [
            'debug' => $e->getMessage(),
            'trace' => $e->getFile() . ':' . $e->getLine()
        ]);
    }
}

try {
    switch ($accion) {
        // ✅ Crear área
        case 'crear':
            canEmployeePerform(['GESTOR_MESAS', 'SUPERVISOR']);

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

            returnJson($isAjax, 'success', 'Área creada correctamente.', [
                'id_area' => $id_area,
                'nombre' => $nombre
            ]);
            break;

        // ✅ Editar área
        case 'editar':
            canEmployeePerform(['GESTOR_MESAS', 'SUPERVISOR']);

            $id_area = $_POST['id_area'] ?? null;
            $nombre = trim($_POST['nombre_area'] ?? '');

            if (!$id_area || empty($nombre)) {
                returnJson($isAjax, 'error', 'Datos incompletos para editar.');
            }

            if ($areaModel->areaExiste($nombre, $id_propietario, $id_area)) {
                returnJson($isAjax, 'error', 'Ya existe un área con ese nombre.');
            }

            $areaModel->editarArea($id_area, $nombre, $id_propietario);
            returnJson($isAjax, 'success', 'Área actualizada correctamente.', [
                'id_area' => $id_area,
                'nombre' => $nombre
            ]);
            break;

        // ✅ Eliminar área
        case 'eliminar':
            canEmployeePerform(['GESTOR_MESAS']);

            $id_area = $_POST['id_area'] ?? $_GET['id_area'] ?? null;
            if (!$id_area) {
                returnJson($isAjax, 'error', 'ID de área no válido.');
            }

            $areaModel->eliminarArea($id_area, $id_propietario);
            returnJson($isAjax, 'success', 'Área eliminada correctamente.', ['id_area' => $id_area]);
            break;

        // ✅ Reordenar áreas
        case 'ordenar':
            canEmployeePerform(['GESTOR_MESAS', 'SUPERVISOR']);

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
    // Captura cualquier error del bloque principal
    error_log("⚠️ Error en AreaController: " . $e->getMessage() . " en " . $e->getFile() . ':' . $e->getLine());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno en controlador',
        'debug' => $e->getMessage(),
        'trace' => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}

/** Función auxiliar para retornar JSON */
function returnJson($ajax, $status, $message, $data = [])
{
    if ($ajax) {
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit;
    }
    header("Location: ../../view/gestion_mesas.php?status={$status}&msg=" . urlencode($message));
    exit;
}
