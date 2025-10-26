<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$user = supabase('usuarios', 'GET', ['email' => $email]);

if (!$user || empty($user['data'])) {
  echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
  exit;
}

$usuario = $user['data'][0];
if (!password_verify($password, $usuario['password'])) {
  echo json_encode(['status' => 'error', 'message' => 'Contraseña incorrecta']);
  exit;
}

// ✅ Login exitoso
session_start();
$_SESSION['usuario'] = [
  'id' => $usuario['id'], // asegúrate que tu tabla tenga 'id' o cambia por 'id_usuario'
  'first_name' => $usuario['first_name'],
  'last_name' => $usuario['last_name'],
  'email' => $usuario['email'],
  'restaurant_name' => $usuario['restaurant_name']
];

echo json_encode([
  'status' => 'success',
  'message' => 'Inicio de sesión exitoso',
  'redirect' => '/DelixSystem/app/pages/dashboard_propietario/view/index.php'
]);