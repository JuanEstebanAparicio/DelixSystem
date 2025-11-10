<?php
// ✅ DelixSystem/src/auth/login_empleado.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// 🔹 Capturar campos
$restaurant_name = trim($_POST['restaurant_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$document = trim($_POST['document'] ?? '');

if (empty($restaurant_name) || empty($email) || empty($document)) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Por favor, completa todos los campos.'
  ]);
  exit;
}

try {
  // 🔸 Buscar restaurante (propietario)
  $rest = supabaseRest('usuarios', 'GET', null, '?restaurant_name=eq.' . urlencode($restaurant_name));

  if (!$rest || empty($rest['data'])) {
    echo json_encode([
      'status' => 'error',
      'message' => 'No se encontró ningún restaurante con ese nombre.'
    ]);
    exit;
  }

  $user_id = $rest['data'][0]['id'];

  // 🔸 Buscar empleado en esa empresa
  $emp = supabaseRest(
    'employees',
    'GET',
    null,
    '?email=eq.' . urlencode($email) . '&document=eq.' . urlencode($document) . '&user_id=eq.' . $user_id
  );

  if (!$emp || empty($emp['data'])) {
    echo json_encode([
      'status' => 'error',
      'message' => 'Credenciales incorrectas o no perteneces a este restaurante.'
    ]);
    exit;
  }

  // 🔸 Crear sesión
  $empleado = $emp['data'][0];

  $_SESSION['empleado_auth'] = [
    'id' => $empleado['id'],
    'full_name' => $empleado['full_name'],
    'email' => $empleado['email'],
    'document' => $empleado['document'],
    'restaurant_name' => $restaurant_name,
    'auth_type' => 'empleado'
  ];

  // 🟢 Marcar como conectado
  supabaseRest('employees', 'PATCH', ['is_online' => true], '?id=eq.' . $empleado['id']);

  echo json_encode([
    'status' => 'success',
    'message' => 'Inicio de sesión exitoso.',
    'redirect' => '/DelixSystem/app/pages/dashboard_empleado/view/index.php'
  ]);
  exit;

} catch (Exception $e) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Error interno: ' . $e->getMessage()
  ]);
  exit;
}
