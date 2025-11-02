<?php
// DelixSystem/app/pages/gestor_empleado/php/employee/EmpleadoListController.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../../../config/supabase.php';
require_once __DIR__ . '/../../../../middleware/universal_guard.php';

try {
    if (session_status() === PHP_SESSION_NONE) session_start();

    // ✅ Obtener sesión (sin redirigir en caso de AJAX)
    $usuario = universalGuard(true);

    if (!$usuario) {
        throw new Exception("Usuario no autenticado.");
    }

    // ✅ Determinar ID base (propietario o restaurante del empleado)
    if ($usuario['tipo'] === 'propietario') {
        $userId = $usuario['id'];
    } elseif ($usuario['tipo'] === 'empleado') {
        $userId = $usuario['restaurant_id'];
    } else {
        throw new Exception("Tipo de usuario no reconocido.");
    }

    // ✅ Configurar conexión (ya incluida por Supabase)
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // ✅ Consulta empleados + roles asociados
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
        "message" => $e->getMessage()
    ]);
    exit;
}
