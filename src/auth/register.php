<?php
require_once dirname(__DIR__, 2) . '/core/supabaseClient.php';
require_once dirname(__DIR__, 1) . '/helpers/alerts.php';


// Validar campos
$fields = ['first_name', 'last_name', 'email', 'restaurant_name', 'password', 'confirm_password'];
foreach ($fields as $f) {
    if (empty($_POST[$f])) showAlert('Atención', 'Faltan campos obligatorios', 'warning', '../../public/index.php');
}

if ($_POST['password'] !== $_POST['confirm_password']) {
    showAlert('Atención', 'Las contraseñas no coinciden', 'error', '../../public/index.php');
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// Crear usuario en Supabase Auth
$response = supabaseRequest('/auth/v1/signup', [
    'email' => $email,
    'password' => $password,
    'data' => [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'restaurant_name' => $_POST['restaurant_name']
    ]
]);

if ($response['status'] >= 200 && $response['status'] < 300) {
    showAlert('Éxito', 'Usuario registrado correctamente. Revisa tu correo para verificar la cuenta.', 'success', '../../public/index.php');
} else {
    $error = $response['data']['msg'] ?? $response['error'] ?? 'Error desconocido';
    showAlert('Error', "Error al registrar el usuario: $error", 'error', '../../public/index.php');
}
