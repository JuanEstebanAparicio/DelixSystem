<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/config/constants.php';

// Obtener datos del formulario
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$restaurant_name = $_POST['restaurant_name'] ?? '';

// 1️⃣ Insertar usuario directamente en la tabla 'usuarios'
$insert = supabase('usuarios', 'POST', [
    'first_name' => $first_name,
    'last_name' => $last_name,
    'email' => $email,
    'restaurant_name' => $restaurant_name,
    'password' => password_hash($password, PASSWORD_DEFAULT)
]);

if ($insert && isset($insert['status']) && $insert['status'] >= 200 && $insert['status'] < 300) {
    echo json_encode([
        'status' => 'success',
        'message' => '✅ Registro exitoso'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => '⚠️ Error al guardar en la tabla usuarios',
        'debug' => $insert // útil para depurar
    ]);
}
