<?php
require_once __DIR__ . '/DynamicKeyModel.php';
header("Content-Type: application/json; charset=utf-8");

try {
    $accion = $_POST['accion'] ?? null;
    $adminId = $_POST['admin_id'] ?? null;

    if (!$accion || !$adminId) {
        echo json_encode(["status" => "error", "message" => "Faltan parámetros"]);
        exit;
    }

    $model = new DynamicKeyModel();

    switch ($accion) {
        // 🔹 Consultar si existe un código activo
        case 'consultar':
            $key = $model->getActiveKey($adminId);
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

        // 🔹 Generar un nuevo código
        case 'generar':
            // Expirar los anteriores primero ✅
            $model->expireOldKeys($adminId);

            // Generar nuevo código
            $bytes = random_bytes(8);
            $code = strtoupper(implode('-', str_split(bin2hex($bytes), 4)));

            // Definir expiración 1 minuto desde ahora
            $expiresAt = date("Y-m-d H:i:sP", strtotime("+1 minute"));

            $model->createNewKey($adminId, $code, $expiresAt);

            echo json_encode([
                "status" => "ok",
                "code" => $code,
                "expires_at" => $expiresAt
            ]);
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Acción no reconocida"]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
