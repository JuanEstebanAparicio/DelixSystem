<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json; charset=UTF-8");

// Dependencias
require_once __DIR__ . '/../../app/config/supabase.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Método no permitido"]);
        exit;
    }

    $correo = trim($_POST['correo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');

    if (!$correo || !$documento) {
        throw new Exception("Por favor, completa todos los campos.");
    }

    // Conexión
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Buscar empleado por correo y documento
    $stmt = $conexion->prepare("SELECT * FROM employees WHERE email = :correo AND document = :documento LIMIT 1");
    $stmt->execute([
        ':correo' => $correo,
        ':documento' => $documento
    ]);

    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) {
        throw new Exception("Empleado no encontrado o datos incorrectos.");
    }

    // Iniciar sesión separada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Usamos una sesión exclusiva para empleados
    $_SESSION['empleado'] = [
        'id' => $empleado['id'],
        'full_name' => $empleado['full_name'],
        'email' => $empleado['email'],
        'document' => $empleado['document'],
        'user_id' => $empleado['user_id'],
        'rol' => 'empleado'
    ];

    echo json_encode([
        "status" => "success",
        "message" => "Bienvenido de nuevo, {$empleado['full_name']} 👋",
        "redirect" => "/DelixSystem/app/pages/dashboard_empleado/view/index.php"
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "⚠️ " . $e->getMessage()
    ]);
}
