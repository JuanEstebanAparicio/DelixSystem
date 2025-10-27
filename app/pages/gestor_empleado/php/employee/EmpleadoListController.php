<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

require_once __DIR__ . '/../../../../config/supabase.php';

try {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $userId = $_SESSION['usuario']['id'] ?? null;
    if (!$userId) throw new Exception("Usuario no autenticado.");

    $conexion = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Construcción del query:
    // - Agregamos los roles con STRING_AGG (separados por coma)
    // - Si no hay roles devolvemos cadena vacía para compatibilidad con el frontend
    $stmt = $conexion->prepare("
    SELECT 
        e.id,
        e.full_name,
        e.email,
        e.document AS documento,  -- 👈 alias para coincidir con el JS
        COALESCE(STRING_AGG(r.nombre, ', ' ORDER BY r.nombre), '') AS role,
        e.is_online,
        e.created_at
    FROM employees e
    LEFT JOIN employee_roles er ON e.id = er.empleado_id
    LEFT JOIN roles r ON er.rol_id = r.id
    WHERE e.user_id = :user_id
    GROUP BY e.id, e.full_name, e.email, e.document, e.is_online, e.created_at
    ORDER BY e.created_at DESC
");


    $stmt->execute(['user_id' => $userId]);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $empleados
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
