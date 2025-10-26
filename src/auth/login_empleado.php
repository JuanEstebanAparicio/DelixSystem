<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json; charset=UTF-8");

// Dependencias
require_once __DIR__ . '/../../config/supabase.php';
require_once __DIR__ . '/../../app/pages/gestor_empleado/php/employee/EmpleadoModel.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Método no permitido"]);
        exit;
    }

    // Capturar campos
    $codigo = trim($_POST['codigo_dinamico'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');

    if (!$codigo || !$correo || !$documento) {
        throw new Exception("Por favor, completa todos los campos.");
    }

    // Configurar conexión PDO
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Inicializar modelo
    $model = new EmpleadoModel($conexion);

    // ✅ Verificar código dinámico
    $codeCheck = $model->verifyCode($codigo);
    if (!$codeCheck || !$codeCheck['valid']) {
        throw new Exception($codeCheck['msg'] ?? "Código inválido o expirado.");
    }

    $userId = $codeCheck['user_id'];

    // ✅ Verificar que el empleado exista en ese restaurante
    $stmt = $conexion->prepare("
        SELECT * FROM employees 
        WHERE email = :email 
        AND document = :document 
        AND user_id = :user_id
        LIMIT 1
    ");
    $stmt->execute([
        'email' => $correo,
        'document' => $documento,
        'user_id' => $userId
    ]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) {
        throw new Exception("No se encontró ningún empleado asociado a este código.");
    }

    // ✅ Desactivar el código una vez usado
    $model->deactivateCode($codigo);

    // ✅ Crear sesión del empleado
    session_name("empleado_session");
    session_start();

    $_SESSION['empleado'] = [
        'id' => $empleado['id'],
        'full_name' => $empleado['full_name'],
        'email' => $empleado['email'],
        'document' => $empleado['document'],
        'user_id' => $empleado['user_id']
    ];

    // ✅ Responder con redirección
    echo json_encode([
        "status" => "success",
        "redirect" => "/DelixSystem/app/pages/dashboard_empleado/view/index.php",
        "message" => "Bienvenido de nuevo, {$empleado['full_name']} 👋"
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "⚠️ " . $e->getMessage()
    ]);
}
?>
