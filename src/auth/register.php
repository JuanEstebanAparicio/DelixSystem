<?php
// registerPrueba.php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $restaurant_name = $_POST['restaurant_name'] ?? '';
    $password = $_POST['password'] ?? '';

    // Crear hash simple del password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Crear el arreglo de datos que enviaremos a Supabase
    $data = [
        "first_name" => $first_name,
        "last_name" => $last_name,
        "email" => $email,
        "restaurant_name" => $restaurant_name,
        "verified" => false,
        "created_at" => date('c')
    ];

    // Hacer el insert con la función de database.php
    $response = supabaseRequest('/rest/v1/admins', 'POST', [$data]);

    // Mostrar respuesta cruda para depuración
    echo "<pre>";
    print_r($response);
    echo "</pre>";
} else {
    echo "Método no permitido.";
}
