<?php
// Datos de tu proyecto Supabase
$host = "aws-1-us-east-1.pooler.supabase.com"; // host del pooler (usa el que te da Supabase)
$port = "6543"; // puerto del pooler
$dbname = "postgres";
$user = "postgres.gqcaeecfhqdkpoatmkfs"; // usuario completo del pooler
$password = "Delix2025"; // tu contraseña

try {
    $conexion = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   // echo "Conexión exitosa a Supabase";
} catch (PDOException $e) {
    die("Error de conexión a Supabase: " . $e->getMessage());
}
?>
