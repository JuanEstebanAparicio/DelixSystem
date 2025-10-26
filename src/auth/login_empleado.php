<?php
// src/auth/login_empleado.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json; charset=UTF-8");

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Método no permitido"]);
        exit;
    }

    // Rutas relativas robustas
    $configPath = __DIR__ . '/../../config/supabase.php'; // desde src/auth -> ../../config
    $modelPath  = __DIR__ . '/../../app/pages/gestor_empleado/php/employee/EmpleadoModel.php'; // desde src/auth -> ../../app/...

    if (!file_exists($configPath)) throw new Exception("Config file not found: $configPath");
    if (!file_exists($modelPath))  throw new Exception("Model file not found: $modelPath");

    require_once $configPath;
    require_once $modelPath;

    // Aseguramos que las credenciales estén definidas en supabase.php
    if (!isset($host, $port, $dbname, $user, $password)) {
        throw new Exception("Credenciales de base de datos faltantes en supabase.php");
    }

    // Crear conexión PDO local (no depender de variable global)
    $conexion = new PDO(
        "pgsql:host={$host};port={$port};dbname={$dbname}",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Capturar campos
    $codigo   = trim($_POST['codigo_dinamico'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $documento= trim($_POST['documento'] ?? '');

    if (!$codigo || !$correo || !$documento) {
        throw new Exception("Por favor, completa todos los campos (código, correo, documento).");
    }

    // Inicializar modelo con la conexión recién creada
    $model = new EmpleadoModel($conexion);

    // Verificar código dinámico
    $codeCheck = $model->verifyCode($codigo);
    if (!$codeCheck || !$codeCheck['valid']) {
        throw new Exception($codeCheck['msg'] ?? "Código inválido o expirado.");
    }
    $userId = $codeCheck['user_id'];

    // Verificar que el empleado exista y pertenezca a ese owner (user_id)
    $stmt = $conexion->prepare("
        SELECT id, full_name, email, document, user_id
        FROM employees
        WHERE email = :email
          AND document = :document
          AND user_id = :user_id
        LIMIT 1
    ");
    $stmt->execute([
        'email'   => $correo,
        'document'=> $documento,
        'user_id' => $userId
    ]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) {
        throw new Exception("No se encontró ningún empleado asociado a este restaurante con esos datos.");
    }

    // Desactivar código (si esa es la política)
    $model->deactivateCode($codigo);

    // Crear sesión exclusiva para empleados (no interferir con sesión admin)
    session_name("empleado_session");
    if (session_status() === PHP_SESSION_NONE) session_start();

    $_SESSION['empleado'] = [
        'id'        => $empleado['id'],
        'full_name' => $empleado['full_name'],
        'email'     => $empleado['email'],
        'document'  => $empleado['document'],
        'user_id'   => $empleado['user_id'],
    ];

    echo json_encode([
        "status"   => "success",
        "redirect" => "/DelixSystem/app/pages/dashboard_empleado/view/index.php",
        "message"  => "Bienvenido, {$empleado['full_name']} 👋"
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    // Loguear para debug si lo necesitas (no exponer en producción)
    error_log("login_empleado error: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message"=> "⚠️ " . $e->getMessage()
    ]);
    exit;
}
