<?php
// DelixSystem/app/config/supabase.php

// ============================
// 🔹 CONFIGURACIÓN SUPABASE
// ============================
$host = "aws-1-us-east-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.gqcaeecfhqdkpoatmkfs";
$password = "Delix2025";

// ============================
// 🔹 CONEXIÓN CON PDO
// ============================
try {
    $conexion = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::ATTR_PERSISTENT => true
        ]
    );
} catch (PDOException $e) {
    error_log("❌ Error de conexión a Supabase: " . $e->getMessage());
    die(json_encode([
        'status' => 'error',
        'message' => 'Error interno al conectar con la base de datos.'
    ]));
}

// ============================
// 🔹 FUNCIÓN GENERAL supabase()
// ============================
// Esta función reemplaza la API REST y usa directamente PDO
function supabase($table, $method, $data = null, $condition = null)
{
    global $conexion;

    try {
        if (strtoupper($method) === 'POST') {
            // INSERTAR REGISTRO
            $keys = array_keys($data);
            $columns = implode(', ', $keys);
            $placeholders = ':' . implode(', :', $keys);

            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders) RETURNING *";
            $stmt = $conexion->prepare($sql);
            $stmt->execute($data);

            $result = $stmt->fetchAll();

            return [
                'status' => 201,
                'data' => $result
            ];
        } elseif (strtoupper($method) === 'GET') {
            // CONSULTAR REGISTROS
            $sql = "SELECT * FROM $table";
            if ($condition) {
                $sql .= " WHERE $condition";
            }

            $stmt = $conexion->query($sql);
            $result = $stmt->fetchAll();

            return [
                'status' => 200,
                'data' => $result
            ];
        } else {
            return [
                'status' => 400,
                'error' => 'Método no soportado'
            ];
        }
    } catch (PDOException $e) {
        return [
            'status' => 500,
            'error' => $e->getMessage()
        ];
    }
}
?>
