<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/mesa_debug.log');

header('Content-Type: application/json');

try {
    include __DIR__ . '/../../../../config/supabase.php';
    include __DIR__ . '/../../../../middleware/role_guard.php';
    include __DIR__ . '/MesaConstructor.php';
} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al cargar dependencias',
        'debug' => $e->getMessage(),
        'trace' => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}

// 🔹 Inicializar modelo
try {
    $mesaConstructor = new MesaConstructor();
    $mesaModel = $mesaConstructor->getModel();
} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al inicializar MesaModel',
        'debug' => $e->getMessage(),
        'trace' => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// 🔹 Identificar tipo de usuario
$id_usuario = $_SESSION['usuario']['id'] ?? null;
$id_empleado = $_SESSION['empleado_auth']['id'] ?? null;

// 🔹 Obtener propietario si es empleado
$id_propietario = $id_usuario;
if ($id_empleado && !$id_usuario) {
    try {
        $stmt = $mesaModel->getDB()->prepare("SELECT user_id FROM employees WHERE id = ?");
        $stmt->execute([$id_empleado]);
        $id_propietario = $stmt->fetchColumn();

        if (!$id_propietario) {
            returnJson($isAjax, 'error', 'Empleado sin propietario asignado.');
        }
    } catch (Throwable $e) {
        returnJson($isAjax, 'error', 'Error al obtener propietario.', [
            'debug' => $e->getMessage(),
            'trace' => $e->getFile() . ':' . $e->getLine()
        ]);
    }
}

try {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        // ✅ Eliminar mesa
        case 'eliminar':
            canEmployeePerform(['GESTOR_MESAS']);
            $id_mesa = $_POST['id_mesa'] ?? null;

            if (!$id_mesa) {
                returnJson($isAjax, 'error', 'ID de mesa no proporcionado.');
            }

            $mesaModel->eliminarMesa($id_mesa);
            returnJson($isAjax, 'success', 'Mesa eliminada correctamente.');
            break;

        // ✅ Crear mesa
        case 'crear':
            canEmployeePerform(['GESTOR_MESAS', 'SUPERVISOR']);

            $id_area = $_POST['id_area'] ?? null;
            $nombre = trim($_POST['nombre_mesa'] ?? '');

            if (empty($nombre) || empty($id_area)) {
                returnJson($isAjax, 'error', 'Datos incompletos.');
            }

            if ($mesaModel->mesaExiste($nombre, $id_area)) {
                returnJson($isAjax, 'warning', 'Ya existe una mesa con ese nombre en esta área.');
            }

            $nuevaMesa = $mesaModel->crearMesa($id_area, $nombre);
            if (!$nuevaMesa || !isset($nuevaMesa['id_mesa'])) {
                returnJson($isAjax, 'error', 'No se pudo crear la mesa.');
            }

            returnJson($isAjax, 'success', 'Mesa creada correctamente.', [
                'id_mesa' => $nuevaMesa['id_mesa'],
                'id_area' => $id_area,
                'nombre' => $nombre
            ]);
            break;

        // ✅ Editar mesa
        case 'editar':
            canEmployeePerform(['GESTOR_MESAS', 'SUPERVISOR']);

            $id_mesa = $_POST['id_mesa'] ?? null;
            $id_area = $_POST['id_area'] ?? null;
            $nombre = trim($_POST['nombre_mesa'] ?? '');

            if (!$id_mesa || empty($nombre) || empty($id_area)) {
                returnJson($isAjax, 'error', 'Datos incompletos.');
            }

            if ($mesaModel->mesaExiste($nombre, $id_area, $id_mesa)) {
                returnJson($isAjax, 'warning', 'Ya existe una mesa con ese nombre.');
            }

            $mesaModel->editarMesa($id_mesa, $id_area, $nombre);
            returnJson($isAjax, 'success', 'Mesa actualizada correctamente.', [
                'id_mesa' => $id_mesa,
                'id_area' => $id_area,
                'nombre' => $nombre
            ]);
            break;

        default:
            returnJson($isAjax, 'error', 'Acción no válida o no reconocida.');
    }
} catch (Throwable $e) {
    error_log("⚠️ Error en MesaController: " . $e->getMessage() . " en " . $e->getFile() . ':' . $e->getLine());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno en controlador',
        'debug' => $e->getMessage(),
        'trace' => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}

// 🔹 Función auxiliar para devolver respuesta JSON o redirección
function returnJson($ajax, $status, $message, $data = [])
{
    if ($ajax) {
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit;
    }
    header("Location: ../../view/gestion_mesas.php?status={$status}&msg=" . urlencode($message));
    exit;
}
