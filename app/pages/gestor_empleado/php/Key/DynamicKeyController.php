<?php
require_once __DIR__ . '/DynamicKeyModel.php';
header("Content-Type: application/json; charset=utf-8");

try {
    $action = $_POST['action'] ?? null;
    $userId = $_POST['user_id'] ?? null;

    if (!$action || !$userId) {
        throw new Exception("Missing parameters");
    }

    $model = new DynamicKeyModel();

    switch ($action) {
        case 'get':
            $key = $model->getActiveKey($userId);
            if ($key) {
                echo json_encode([
                    "status" => "ok",
                    "code" => $key['code'],
                    "expires_at" => $key['expires_at']
                ]);
            } else {
                echo json_encode(["status" => "no_active"]);
            }
            break;

        case 'generate':
            $model->expireOldKeys($userId);

            // Generate secure random code (example: A7F2-B9K1-Z5L8)
            $bytes = strtoupper(bin2hex(random_bytes(6)));
            $formatted = implode('-', str_split(substr($bytes, 0, 12), 4));

            $expiresAt = date("Y-m-d H:i:sP", strtotime("+1 minute"));
            $model->createNewKey($userId, $formatted, $expiresAt);

            echo json_encode([
                "status" => "ok",
                "code" => $formatted,
                "expires_at" => $expiresAt
            ]);
            break;

        default:
            throw new Exception("Unknown action");
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
