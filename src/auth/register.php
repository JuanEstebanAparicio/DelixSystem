<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/config/constants.php';

$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$password = $_POST['password'];
$restaurant_name = $_POST['restaurant_name'];

$auth_url = SUPABASE_URL . '/auth/v1/signup';

$auth_payload = [
  'email' => $email,
  'password' => $password,
  'data' => [
    'first_name' => $first_name,
    'last_name' => $last_name,
    'restaurant_name' => $restaurant_name
  ]
];

file_put_contents(__DIR__ . '/debug_supabase.txt', json_encode($auth_payload, JSON_PRETTY_PRINT));
$ch = curl_init($auth_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'apikey: ' . SUPABASE_SERVICE_KEY,
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY
    ],
    CURLOPT_POSTFIELDS => json_encode($auth_payload)
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

$auth_data = json_decode($response, true);

if ($http_code >= 200 && $http_code < 300) {

    $insert = supabase('usuarios', 'POST', [
        'nombre' => $first_name,
        'apellido' => $last_name,
        'email' => $email,
        'restaurante' => $restaurant_name,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ]);

    if ($insert['status'] >= 200 && $insert['status'] < 300) {
        echo json_encode([
            'status' => 'success',
            'message' => '✅ Registro exitoso'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => '⚠️ Error al guardar en la tabla usuarios'
        ]);
    }

} else {
    echo json_encode([
        'status' => 'error',
        'message' => '❌ Error al registrar en Supabase Auth: ' . json_encode($auth_data)
    ]);
}
