<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json; charset=UTF-8");

// ✅ Dependencias
require_once __DIR__ . '/../../../../config/supabase.php';
require_once __DIR__ . '/EmpleadoModel.php';

try {
    // ✅ Validar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Método no permitido"]);
        exit;
    }

    // ✅ Validar entrada
    $codigo = trim($_POST['codigo_dinamico'] ?? '');
    $nombre = trim($_POST['nombre_completo'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');

    if (!$codigo || !$nombre || !$correo || !$documento) {
        throw new Exception("Por favor, completa todos los campos.");
    }

    // ✅ Conexión a Supabase (ya configurada en supabase.php)
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // ✅ Inicializar modelo
    $model = new EmpleadoModel($conexion);

    // 🔍 Verificar código dinámico
    $codeCheck = $model->verifyCode($codigo);
    if (!$codeCheck['valid']) {
        throw new Exception($codeCheck['msg']);
    }

    $userId = $codeCheck['user_id'];

    // 👤 Registrar empleado
    $registroExitoso = $model->registerEmployee($userId, $nombre, $correo, $documento);
    if (!$registroExitoso) {
        throw new Exception("No se pudo registrar el empleado. Inténtalo de nuevo.");
    }

    // 📴 Desactivar el código tras su uso
    $model->deactivateCode($codigo);

    // 🎉 Respuesta de éxito
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
