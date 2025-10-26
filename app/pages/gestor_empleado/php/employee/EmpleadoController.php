<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../../../config/supabase.php';
require_once __DIR__ . '/EmpleadoModel.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Método no permitido"]);
        exit;
    }

    $action = $_POST['action'] ?? 'register';

    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

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
        exit;
    }

    // ----------------------------------------------------
    // 🔹 REGISTRO DE EMPLEADO
    // ----------------------------------------------------
    $codigo = trim($_POST['codigo_dinamico'] ?? '');
    $nombre = trim($_POST['nombre_completo'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');

    if (!$codigo || !$nombre || !$correo || !$documento) {
        throw new Exception("Por favor, completa todos los campos.");
    }

    // ✅ Verificar código dinámico
    $codeCheck = $model->verifyCode($codigo);
    if (!$codeCheck || !$codeCheck['valid']) {
        throw new Exception($codeCheck['msg'] ?? "Código inválido o expirado.");
    }

    $userId = $codeCheck['user_id'];

    // ✅ Registrar empleado
    $registroExitoso = $model->registerEmployee($userId, $nombre, $correo, $documento);
    if (!$registroExitoso) {
        throw new Exception("No se pudo registrar el empleado. Inténtalo de nuevo.");
    }

    // ✅ Desactivar código usado
    $model->deactivateCode($codigo);

    // ✅ Buscar el empleado recién creado
    $stmt = $conexion->prepare("SELECT * FROM employees WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $correo]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) {
        throw new Exception("No se pudo recuperar la información del empleado.");
    }

    // ✅ Crear sesión específica del empleado
    if (session_status() === PHP_SESSION_NONE) session_start();

    $_SESSION['empleado_auth'] = [
        'auth_type' => 'empleado',
        'logged_in' => true,
        'id' => $empleado['id'],
        'full_name' => $empleado['full_name'],
        'email' => $empleado['email'],
        'document' => $empleado['document'],
        'user_id' => $empleado['user_id'],
        'login_time' => date('Y-m-d H:i:s'),
    ];

    // ✅ Respuesta con redirección directa
    echo json_encode([
        "status" => "success",
        "redirect" => "/DelixSystem/app/pages/dashboard_empleado/view/index.php",
        "message" => "Bienvenido al sistema, {$empleado['full_name']} 👋"
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "⚠️ " . $e->getMessage()
    ]);
    exit;
}
?>
