<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

require_once __DIR__ . '/../../../../config/supabase.php';
require_once __DIR__ . '/EmpleadoModel.php';

$conexion = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $codigo = $_POST['codigo_dinamico'] ?? null;
    $nombre = $_POST['nombre_completo'] ?? null;
    $correo = $_POST['correo'] ?? null;
    $documento = $_POST['documento'] ?? null;

    if (!$codigo || !$nombre || !$correo || !$documento) {
        throw new Exception("All fields are required");
    }

    $model = new EmpleadoModel($conexion);
    $codeCheck = $model->verifyCode($codigo);

    if (!$codeCheck['valid']) {
        throw new Exception($codeCheck['msg']);
    }

    $userId = $codeCheck['user_id'];
    $model->registerEmployee($userId, $nombre, $correo, $documento);
    $model->deactivateCode($codigo);

    echo json_encode(["status" => "success", "message" => "Employee registered successfully"]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
