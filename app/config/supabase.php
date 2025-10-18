<?php
$host = "aws-1-us-east-1.pooler.supabase.com"; 
$port = "6543";
$dbname = "postgres";
$user = "postgres.gqcaeecfhqdkpoatmkfs"; 
$password = "Delix2025"; 

try {
    $conexion = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a Supabase: " . $e->getMessage());
}
?>
