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
echo json_encode([
  'status' => 'success',
  'message' => 'Inicio de sesión exitoso',
  'redirect' => '../../public/dashboard.php'
]);
