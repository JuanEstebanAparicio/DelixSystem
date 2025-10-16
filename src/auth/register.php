<?php
// PROJECTDELIX/src/auth/register.php
require_once __DIR__ . '/../../app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// --- Sanitizar datos ---
$nombre       = trim($_POST['first_name'] ?? '');
$apellido     = trim($_POST['last_name'] ?? '');
$correo       = trim($_POST['email'] ?? '');
$restaurante  = trim($_POST['restaurant_name'] ?? '');
$contrasena   = $_POST['password'] ?? '';
$confirmacion = $_POST['confirm_password'] ?? '';

// --- Validaciones básicas ---
if ($contrasena !== $confirmacion) {
    echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden.']);
    exit;
}

if (!$nombre || !$apellido || !$correo || !$restaurante || !$contrasena) {
    echo json_encode(['status' => 'error', 'message' => 'Por favor completa todos los campos.']);
    exit;
}

// --- Verificar si el correo ya existe ---
$check = supabase('admins?correo=eq.' . urlencode($correo), 'GET');

if (!empty($check['data'])) {
    echo json_encode(['status' => 'error', 'message' => 'El correo ya está registrado.']);
    exit;
}

// --- Insertar nuevo admin ---
$hash = password_hash($contrasena, PASSWORD_BCRYPT);

$data = [
    'nombre'      => $nombre,
    'apellido'    => $apellido,
    'correo'      => $correo,
    'restaurante' => $restaurante,
    'contrasena'  => $hash,
    'verificado'  => false
];

$response = supabase('admins', 'POST', [$data]);

if ($response['status'] >= 200 && $response['status'] < 300) {
    echo json_encode(['status' => 'success', 'message' => 'Registro exitoso']);
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al registrar: ' . ($response['error'] ?: json_encode($response['data']))
    ]);
}
