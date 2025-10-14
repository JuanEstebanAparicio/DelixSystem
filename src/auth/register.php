<?php
// PROJECTDELIX/src/auth/register.php

require_once __DIR__ . '/../../app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Método no permitido');
}

$nombre       = trim($_POST['first_name'] ?? '');
$apellido     = trim($_POST['last_name'] ?? '');
$correo       = trim($_POST['email'] ?? '');
$restaurante  = trim($_POST['restaurant_name'] ?? '');
$contrasena   = $_POST['password'] ?? '';
$confirmacion = $_POST['confirm_password'] ?? '';

if ($contrasena !== $confirmacion) {
    exit('Las contraseñas no coinciden.');
}

if (!$nombre || !$apellido || !$correo || !$restaurante || !$contrasena) {
    exit('Por favor completa todos los campos.');
}

$hash = password_hash($contrasena, PASSWORD_BCRYPT);

$data = [
    'nombre'      => $nombre,
    'apellido'    => $apellido,
    'correo'      => $correo,
    'restaurante' => $restaurante,
    'contrasena'  => $hash,
    'verificado'  => false
];

// Insertar en tabla admins
$response = supabase('admins', 'POST', [$data]);

if ($response['status'] >= 200 && $response['status'] < 300) {
    echo '<script>alert("Registro exitoso ✅"); window.location.href = "../../public/index.php";</script>';
} else {
    echo '<script>alert("Error al registrar: ' . htmlspecialchars($response['error'] ?? 'Error desconocido') . '"); window.history.back();</script>';
}
