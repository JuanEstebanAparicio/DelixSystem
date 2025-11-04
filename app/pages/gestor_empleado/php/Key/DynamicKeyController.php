<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

// ✅ Include DB connection

require_once __DIR__ . '/../../../../middleware/controller_bootstrap.php';
// ✅ Include the model
require_once __DIR__ . '/DynamicKeyModel.php';

// ✅ Start session (por si el frontend no envía user_id)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $action = $_POST['action'] ?? null;

    // ✅ Prioriza el user_id del POST, pero usa el de sesión si existe
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : (
        $_SESSION['usuario']['id'] ?? null
    );

    if (!$action || !$userId) {
        throw new Exception("Missing parameters: action or user_id");
    }

    // ✅ Crear modelo
    $model = new DynamicKeyModel($conexion);

    switch ($action) {
        case 'get':
            verifyRoleAccess('empleados', 'ver'); // Solo admin o supervisor pueden ver

            // 1️⃣ Limpia claves expiradas
            $model->expireKey($userId);

            // 2️⃣ Busca si hay clave activa en memoria (mejora de velocidad)
            if (!isset($_SESSION['active_key'][$userId])) {
                $key = $model->getActiveKey($userId);
                if ($key) {
                    $_SESSION['active_key'][$userId] = $key; // Cache temporal
                }
            } else {
                $key = $_SESSION['active_key'][$userId];
            }

            if (empty($key)) {
                echo json_encode(["status" => "no_active"]);
                exit;
            }

            echo json_encode([
                "status" => "success",
                "code" => $key['code'],
                "expires_at" => $key['expires_at']
            ]);
            break;

        case 'generate':

             verifyRoleAccess('empleados', 'generar_codigo'); // 🔐 Protección fuerte

            // 1️⃣ Desactiva claves previas activas
            $model->deactivateOldKeys($userId);

            // 2️⃣ Limpia claves inactivas antiguas
            $model->cleanOldKeys($userId);

            // 3️⃣ Genera nueva clave
            $code = strtoupper(implode('-', str_split(bin2hex(random_bytes(4)), 4)));
            $expiresAt = gmdate("Y-m-d H:i:s", strtotime("+1 minute"));

            // 4️⃣ Guarda en base de datos
            $model->createKey($userId, $code, $expiresAt);

            // 5️⃣ Cachea en sesión (mejora de rendimiento)
            $_SESSION['active_key'][$userId] = [
                'code' => $code,
                'expires_at' => $expiresAt
            ];

            echo json_encode([
                "status" => "success",
                "code" => $code,
                "expires_at" => $expiresAt
            ]);
            break;

        default:
            throw new Exception("Invalid action");
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
