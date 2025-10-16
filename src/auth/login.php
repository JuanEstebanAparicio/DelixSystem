<?php
// PROJECTDELIX/src/auth/login.php

session_start();
require_once __DIR__ . '/../../app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Método no permitido');
}

$correo     = trim($_POST['email'] ?? '');
$contrasena = $_POST['password'] ?? '';

if (!$correo || !$contrasena) {
    exit('Por favor completa todos los campos.');
}

// Buscar usuario por correo
$query = '?correo=eq.' . urlencode($correo);
$response = supabase('admins', 'GET', null, $query);

if ($response['status'] !== 200 || empty($response['data'])) {
    echo '<script>alert("Correo no registrado o inválido."); window.history.back();</script>';
    exit;
}

$admin = $response['data'][0];
$hash  = $admin['contrasena'] ?? '';

if (!password_verify($contrasena, $hash)) {
    echo '<script>alert("Contraseña incorrecta."); window.history.back();</script>';
    exit;
}

// Crear sesión
$_SESSION['admin'] = [
    'id'          => $admin['id'],
    'nombre'      => $admin['nombre'],
    'apellido'    => $admin['apellido'],
    'correo'      => $admin['correo'],
    'restaurante' => $admin['restaurante']
];

// Redirigir al dashboard
echo '<script>alert("Bienvenido, ' . htmlspecialchars($admin['nombre']) . '!"); window.location.href = "../../app/views/dashboard.php";</script>';
