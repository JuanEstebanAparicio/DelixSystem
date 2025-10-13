<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/alerts.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(["success" => false, "message" => "Datos inválidos."]);
    exit;
}

$first_name = trim($input['first_name'] ?? '');
$last_name = trim($input['last_name'] ?? '');
$email = trim($input['email'] ?? '');
$restaurant_name = trim($input['restaurant_name'] ?? '');
$password = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

if ($password !== $confirm_password) {
    echo json_encode(["success" => false, "message" => "Las contraseñas no coinciden."]);
    exit;
}

// Registrar usuario en Supabase Auth
$response = supabaseRequest("/auth/v1/signup", "POST", [
    "email" => $email,
    "password" => $password,
    "data" => [
        "first_name" => $first_name,
        "last_name" => $last_name,
        "restaurant_name" => $restaurant_name
    ]
]);
if (isset($response['error_code']) && $response['error_code'] === 'unexpected_failure') {
    echo json_encode([
        "success" => false,
        "message" => "Hubo un error al enviar el correo de verificación. 
        Por favor, intenta de nuevo en unos minutos o usa otro correo."
    ]);
    exit;
}


// 🔍 Modo depuración
file_put_contents(__DIR__ . '/debug_supabase.txt', print_r($response, true));

if (isset($response['error'])) {
    echo json_encode(["success" => false, "message" => "Error Supabase: " . $response['error']['message']]);
    exit;
}

if (!isset($response['user']['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Error al registrar el usuario: respuesta inesperada de Supabase.",
        "debug" => $response
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Correo de verificación enviado."
]);

