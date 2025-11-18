<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/mesa_debug.log');

header('Content-Type: application/json');

try {
    // 📌 Bootstrap general + roles + auditLog()
    require_once __DIR__ . '/../../../../middleware/controller_bootstrap.php';

    // 📌 Constructor + Modelo Mesa
    require_once __DIR__ . '/MesaConstructor.php';

} catch (Throwable $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al cargar dependencias',
        'debug'   => $e->getMessage(),
        'trace'   => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}

/* =====================================================
 *  📌 Inicializar modelo
 * ===================================================== */
try {
    $mesaConstructor = new MesaConstructor();
    $mesaModel = $mesaConstructor->getModel();
} catch (Throwable $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al inicializar MesaModel',
        'debug'   => $e->getMessage(),
        'trace'   => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}

$isAjax = isAjaxRequest();

/* =====================================================
 *  📌 Obtener propietario real
 * ===================================================== */
[$id_propietario, $error] = getPropietarioID($mesaModel->getDB());
if ($error) {
    returnJson($isAjax, 'error', $error);
}

/* =====================================================
 *  📌 Acción principal
 * ===================================================== */
$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {

        /* =====================================================
         * 🟢 CREAR MESA
         * ===================================================== */
        case 'crear':
            verifyRoleAccess('mesas', 'crear');

            $id_area = $_POST['id_area'] ?? null;
            $nombre  = trim($_POST['nombre_mesa'] ?? '');

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

            $id_mesa = $nuevaMesa['id_mesa'];

            // 📌 AUDITORÍA
            auditLog('mesas', 'crear', [
                'target_table' => 'mesas',
                'target_id'    => $id_mesa,
                'new' => [
                    'nombre'  => $nombre,
                    'id_area' => $id_area
                ]
            ]);

            returnJson($isAjax, 'success', 'Mesa creada correctamente.', [
                'id_mesa' => $id_mesa,
                'id_area' => $id_area,
                'nombre'  => $nombre
            ]);
            break;



        /* =====================================================
         * 🟠 EDITAR MESA
         * ===================================================== */
        case 'editar':
            verifyRoleAccess('mesas', 'editar');

            $id_mesa = $_POST['id_mesa'] ?? null;
            $id_area = $_POST['id_area'] ?? null;
            $nombre  = trim($_POST['nombre_mesa'] ?? '');

            if (!$id_mesa || empty($nombre) || empty($id_area)) {
                returnJson($isAjax, 'error', 'Datos incompletos.');
            }

            if ($mesaModel->mesaExiste($nombre, $id_area, $id_mesa)) {
                returnJson($isAjax, 'warning', 'Ya existe una mesa con ese nombre.');
            }

            // 📌 Obtener OLD antes de actualizar
            $oldData = $mesaModel->getMesaById($id_mesa);

            $mesaModel->editarMesa($id_mesa, $id_area, $nombre);

            // 📌 AUDITORÍA
            auditLog('mesas', 'editar', [
                'target_table' => 'mesas',
                'target_id'    => $id_mesa,
                'old' => $oldData,
                'new' => [
                    'nombre'  => $nombre,
                    'id_area' => $id_area
                ]
            ]);

            returnJson($isAjax, 'success', 'Mesa actualizada correctamente.', [
                'id_mesa' => $id_mesa,
                'id_area' => $id_area,
                'nombre'  => $nombre
            ]);
            break;



        /* =====================================================
         * 🔴 ELIMINAR MESA
         * ===================================================== */
        case 'eliminar':
            verifyRoleAccess('mesas', 'eliminar');

            $id_mesa = $_POST['id_mesa'] ?? null;

            if (!$id_mesa) {
                returnJson($isAjax, 'error', 'ID de mesa no proporcionado.');
            }

            // 📌 Obtener OLD antes de eliminar
            $oldData = $mesaModel->getMesaById($id_mesa);

            $mesaModel->eliminarMesa($id_mesa);

            // 📌 AUDITORÍA
            auditLog('mesas', 'eliminar', [
                'target_table' => 'mesas',
                'target_id'    => $id_mesa,
                'old'          => $oldData
            ]);

            returnJson($isAjax, 'success', 'Mesa eliminada correctamente.', [
                'id_mesa' => $id_mesa
            ]);
            break;



        /* =====================================================
         * 🚫 ACCIÓN NO RECONOCIDA
         * ===================================================== */
        default:
            returnJson($isAjax, 'error', 'Acción no válida o no reconocida.');
    }

} catch (Throwable $e) {

    error_log("⚠️ Error en MesaController: " . $e->getMessage() . " en " . $e->getFile() . ':' . $e->getLine());

    echo json_encode([
        'status'  => 'error',
        'message' => 'Error interno en controlador',
        'debug'   => $e->getMessage(),
        'trace'   => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}
