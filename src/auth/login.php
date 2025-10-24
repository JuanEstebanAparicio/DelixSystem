<?php
// DelixSystem/src/auth/login.php
require_once __DIR__ . '/../../app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $query = '?select=*' . '&email=eq.' . urlencode($email);
    $response = supabase('usuarios', 'GET', null, $query, false);

    header('Content-Type: application/json'); // 👈 muy importante

    if ($response['status'] === 200 && count($response['data']) > 0) {
        $user = $response['data'][0];

        if (password_verify($password, $user['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['usuario'] = [
                'id' => $user['id'],
                'first_name' => $user['first_name'] ?? $user['nombre'] ?? '',
                'last_name' => $user['last_name'] ?? '',
                'email' => $user['email'] ?? '',
                'restaurant_name' => $user['restaurant_name'] ?? $user['restaurante'] ?? ''
            ];

            echo json_encode([
                'status' => 'success',
                'message' => 'Inicio de sesión exitoso',
                'redirect' => '/DelixSystem/app/pages/dashboard_propietario/view/index.php'
            ]);
            exit;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Contraseña incorrecta'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Usuario no encontrado'
        ]);
        exit;
    }
}
