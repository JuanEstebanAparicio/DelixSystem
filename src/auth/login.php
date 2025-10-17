<?php
require_once __DIR__ . '/../../app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = '?select=*' . '&email=eq.' . urlencode($email);
    $response = supabase('usuarios', 'GET', null, $query, false);

    if ($response['status'] === 200 && count($response['data']) > 0) {
        $user = $response['data'][0];

        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['usuario'] = [
                'id' => $user['id'],
                'nombre' => $user['first_name'],
                'restaurante' => $user['restaurant_name']
            ];
            echo "<script>alert('✅ Bienvenido {$user['first_name']}'); window.location.href='../../app/views/dashboard.php';</script>";
        } else {
            echo "<script>alert('❌ Contraseña incorrecta'); history.back();</script>";
        }
    } else {
        echo "<script>alert('❌ Usuario no encontrado'); history.back();</script>";
    }
}
