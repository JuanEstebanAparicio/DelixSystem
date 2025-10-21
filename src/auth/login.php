<?php
// DelixSystem/src/auth/login.php

require_once __DIR__ . '/../../app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = '?select=*' . '&email=eq.' . urlencode($email);
    $response = supabase('usuarios', 'GET', null, $query, false);

    if ($response['status'] === 200 && count($response['data']) > 0) {
        $user = $response['data'][0];

        if (password_verify($password, $user['password'])) {
            // Start session BEFORE sending any output
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['usuario'] = [
                'id' => $user['id'],
                'nombre' => $user['first_name'] ?? $user['nombre'] ?? null,
                'restaurante' => $user['restaurant_name'] ?? $user['restaurante'] ?? null
            ];

            // Use HTTP redirect instead of printing a <script> tag
           header('Location: ../../app/pages/dashboard_propietario/view/index.php');
            exit;
        } else {
            // Incorrect password: redirect back to login with an error code
            header('Location: ../../public/login.php?error=invalid_password');
            exit;
        }
    } else {
        // User not found
        header('Location: ../../public/login.php?error=user_not_found');
        exit;
    }
}
