<?php
require_once "../config.php";

// Capturar datos POST del formulario
$data = [
    "first_name" => $_POST["first_name"] ?? '',
    "last_name" => $_POST["last_name"] ?? '',
    "email" => $_POST["email"] ?? '',
    "restaurant_name" => $_POST["restaurant_name"] ?? '',
    "password_hash" => password_hash($_POST["password"], PASSWORD_BCRYPT),
    "verified" => false
];

// Validación mínima
if ($data["password_hash"] === '' || $data["email"] === '') {
    die("Datos incompletos");
}

// Enviar datos a Supabase
$url = SUPABASE_URL . "/rest/v1/admins";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, supabaseHeaders());
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode == 201 || $httpcode == 200) {
    echo "✅ Registro exitoso. Verifica tu correo.";
} else {
    echo "❌ Error al registrar admin. Código HTTP: " . $httpcode;
    echo "<pre>$response</pre>";
}
?>
