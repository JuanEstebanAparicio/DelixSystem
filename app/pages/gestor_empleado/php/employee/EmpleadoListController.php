<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../../../config/supabase.php';
require_once __DIR__ . '/../../../../middleware/universal_guard.php';

try {
    if (session_status() === PHP_SESSION_NONE) session_start();

    // ✅ Permitir ejecución silenciosa para peticiones AJAX
    $usuario = universalGuard(true);

    if (!$usuario) {
        throw new Exception("Usuario no autenticado.");
    }

    // ✅ Determinar el user_id (del propietario)
    if ($usuario['tipo'] === 'propietario') {
        $userId = $usuario['id'];
    } elseif ($usuario['tipo'] === 'empleado') {
        // Primero intentamos leerlo de la sesión
        $userId = $usuario['restaurant_id'] ?? null;

        // 🧠 Si no viene, lo consultamos directamente desde la base
        if (!$userId && isset($usuario['id'])) {
            $stmtOwner = $conexion->prepare("SELECT user_id FROM employees WHERE id = :id LIMIT 1");
            $stmtOwner->execute(['id' => $usuario['id']]);
            $userId = $stmtOwner->fetchColumn();
        }

        if (!$userId) {
            throw new Exception("No se pudo determinar el restaurante asociado al empleado.");
        }
    } else {
        throw new Exception("Tipo de usuario no reconocido.");
    }

    // ✅ Ejecutar la consulta principal
    $stmt = $conexion->prepare("
        SELECT 
            e.id,
            e.full_name,
            e.email,
            e.document AS documento,
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
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "⚠️ " . $e->getMessage()
    ]);
    exit;
}
