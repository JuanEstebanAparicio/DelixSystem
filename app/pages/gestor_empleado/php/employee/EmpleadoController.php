<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json; charset=UTF-8");

// ✅ Dependencias
require_once __DIR__ . '/../../../../config/supabase.php';
require_once __DIR__ . '/EmpleadoModel.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Método no permitido"]);
        exit;
    }

    // ✅ Determinar acción
    $action = $_POST['action'] ?? 'register';

    // ✅ Configurar conexión
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // ✅ Inicializar modelo
    $model = new EmpleadoModel($conexion);

    // ----------------------------------------------------
    // 🔹 ELIMINAR EMPLEADO
    // ----------------------------------------------------
    if ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if (!$id) throw new Exception("ID de empleado no recibido.");

        $deleted = $model->deleteEmployee($id);
        if (!$deleted) throw new Exception("No se pudo eliminar el empleado.");

        echo json_encode([
            "status" => "success",
            "message" => "Empleado eliminado correctamente."
        ]);
        exit; // ✅ Detiene el script aquí
    }

    // ----------------------------------------------------
    // 🔹 REGISTRAR EMPLEADO (acción por defecto)
    // ----------------------------------------------------
    $codigo = trim($_POST['codigo_dinamico'] ?? '');
    $nombre = trim($_POST['nombre_completo'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');

    if (!$codigo || !$nombre || !$correo || !$documento) {
        throw new Exception("Por favor, completa todos los campos.");
    }

    $codeCheck = $model->verifyCode($codigo);
    if (!$codeCheck['valid']) {
        throw new Exception($codeCheck['msg']);
    }

    $userId = $codeCheck['user_id'];

    $registroExitoso = $model->registerEmployee($userId, $nombre, $correo, $documento);
    if (!$registroExitoso) {
        throw new Exception("No se pudo registrar el empleado. Inténtalo de nuevo.");
    }

    $model->deactivateCode($codigo);

    echo json_encode([
        "status" => "success",
        "message" => "Empleado vinculado correctamente al propietario."
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "⚠️ " . $e->getMessage()
    ]);
}
?>
