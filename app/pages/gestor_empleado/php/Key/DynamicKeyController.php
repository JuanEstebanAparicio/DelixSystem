<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

// ✅ Include DB connection
require_once __DIR__ . '/../../../../config/supabase.php';
// ✅ Include the model
require_once __DIR__ . '/DynamicKeyModel.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $action = $_POST['action'] ?? null;
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;

    if (!$action || !$userId) {
        throw new Exception("Missing parameters");
    }

    // ✅ Create model (connection comes from supabase.php)
    $model = new DynamicKeyModel($conexion);

    switch ($action) {
        case 'get':
            $model->expireKey($userId);
            $key = $model->getActiveKey($userId);

            if (!$key) {
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
            $model->deactivateOldKeys($userId);

            $code = strtoupper(implode('-', str_split(bin2hex(random_bytes(4)), 4)));
            $expiresAt = date("Y-m-d H:i:s", strtotime("+1 minute"));
            $model->createKey($userId, $code, $expiresAt);

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
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
