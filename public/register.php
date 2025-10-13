<?php
// public/register.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $restaurant = $_POST['restaurant_name'];
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];

    if ($password !== $confirm) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>Swal.fire('Error','Las contraseñas no coinciden','error');</script>";
        exit;
    }

    $supabaseUrl = 'https://<TU_PROJECT>.supabase.co';
    $supabaseAnonKey = '<TU_ANON_KEY>';

    // Paso 1: Crear usuario en Auth (esto dispara el correo de verificación)
    $authPayload = json_encode([
        'email' => $email,
        'password' => $password,
        'data' => [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'restaurant_name' => $restaurant,
        ]
    ]);

    $ch = curl_init("$supabaseUrl/auth/v1/signup");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "apikey: $supabaseAnonKey",
            "Authorization: Bearer $supabaseAnonKey"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $authPayload
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['id']) || isset($data['user'])) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
          Swal.fire({
            icon: 'success',
            title: '¡Registro exitoso!',
            html: 'Tu cuenta ha sido creada correctamente.<br>Revisa tu correo electrónico y verifica tu cuenta antes de iniciar sesión.',
            confirmButtonText: 'Entendido'
          }).then(() => window.location.href = '../index.html');
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
          Swal.fire('Error','No se pudo crear la cuenta. Intenta nuevamente.<br><small>".addslashes($response)."</small>','error');
        </script>";
    }
}
?>
