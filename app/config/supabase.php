<?php
// Datos de tu proyecto Supabase
$host = "aws-1-us-east-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.gqcaeecfhqdkpoatmkfs";
$password = "Delix2025";

try {
    // Conexión optimizada y segura
    $conexion = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // mostrar errores reales
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, // resultados en objetos
            PDO::ATTR_EMULATE_PREPARES => true, // necesario para el pooler de Supabase (pgbouncer)
            PDO::ATTR_PERSISTENT => true // mantiene la conexión viva = + velocidad
        ]
    );
} catch (PDOException $e) {
    error_log("Error de conexión a Supabase: " . $e->getMessage());
    die("Error interno al conectar con la base de datos.");
}
?>