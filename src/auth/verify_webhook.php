<?php
// PROJECTDELIX/src/auth/verify_webhook.php
require_once __DIR__ . '/../../app/config/constants.php';
require_once __DIR__ . '/../../app/config/database.php';

// Leer payload crudo
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

// Validar payload básico
if (!$payload) {
    http_response_code(400);
    echo "Bad request";
    exit;
}

// Extraer record/user según webhook shape
$record = $payload['record'] ?? $payload['user'] ?? null;
if (!$record) {
    http_response_code(204);
    echo "No action";
    exit;
}

$emailConfirmed = $record['email_confirmed'] ?? false;
$email = $record['email'] ?? null;
$userId = $record['id'] ?? null;

if ($email && $emailConfirmed) {
    // Actualizar admins donde id = userId o email = email
    if ($userId) {
        $filter = "id=eq." . urlencode($userId);
    } else {
        $filter = "email=eq." . urlencode($email);
    }

    $updateResp = supabaseRequest('/rest/v1/admins?' . $filter, 'PATCH', ['verified' => true], true);

    // Log
    $log = [
        "user_id"    => $userId ?? null,
        "action"     => "email_verified",
        "module"     => "auth",
        "created_at" => date('c')
    ];
    supabaseRequest('/rest/v1/logs', 'POST', [$log], true);

    http_response_code(200);
    echo "OK";
    exit;
}

http_response_code(204);
echo "No action";
