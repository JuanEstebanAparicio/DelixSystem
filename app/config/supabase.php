<?php
// DelixSystem/app/config/supabase.php
// Datos de tu proyecto Supabase
$host = "aws-1-us-east-1.pooler.supabase.com"; // host del pooler (usa el que te da Supabase)
$port = "6543"; // puerto del pooler
$dbname = "postgres";
$user = "postgres.gqcaeecfhqdkpoatmkfs"; // usuario completo del pooler
$password = "Delix2025"; // tu contraseña

try {
    // ============================
    // 🔹 Conexión optimizada y segura
    // ============================
    $conexion = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // mostrar errores reales
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, // resultados en objetos
            PDO::ATTR_EMULATE_PREPARES => true, // 🔸 necesario para el pooler de Supabase (pgbouncer)
            PDO::ATTR_PERSISTENT => true // 🔸 mantiene la conexión viva = + velocidad
        ]
    );

    // Opcional: test rápido solo si necesitas depurar
    // echo "✅ Conexión exitosa a Supabase";
} catch (PDOException $e) {
    // 🔸 No muestres info sensible en producción
    error_log("❌ Error de conexión a Supabase: " . $e->getMessage());
    die("Error interno al conectar con la base de datos.");
}
?>