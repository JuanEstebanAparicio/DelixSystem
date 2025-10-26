<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// ✅ Filtro correcto por correo
$user = supabase('usuarios', 'GET', null, '?email=eq.' . urlencode($email));

if (!$user || empty($user['data'])) {
  echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
  exit;
}

$usuario = $user['data'][0];

// ✅ Validar contraseña
if (!password_verify($password, $usuario['password'])) {
  echo json_encode(['status' => 'error', 'message' => 'Contraseña incorrecta']);
  exit;
}

// ✅ Validar rol
if (!isset($usuario['rol']) || strtolower($usuario['rol']) !== 'propietario') {
  echo json_encode([
    'status' => 'error',
    'message' => '🚫 No tienes permisos para acceder a esta sección'
  ]);
  exit;
}

// ✅ Login exitoso
session_start();
$_SESSION['usuario'] = [
  'id' => $usuario['id'],
  'first_name' => $usuario['first_name'],
  'last_name' => $usuario['last_name'],
  'email' => $usuario['email'],
  'restaurant_name' => $usuario['restaurant_name'],
  'rol' => $usuario['rol']
];

echo json_encode([
  'status' => 'success',
  'message' => 'Inicio de sesión exitoso',
  'redirect' => '/DelixSystem/app/pages/dashboard_propietario/view/index.php'
]);
