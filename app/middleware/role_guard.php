<?php
// DelixSystem/app/middleware/role_guard.php

require_once __DIR__ . '/../pages/dashboard_empleado/php/DashboardEmpleadoController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 🛡️ Control de permisos de empleados basado en roles.
 *
 * @param array|string $allowedRoles  Uno o varios roles permitidos para la acción.
 * @param bool $returnBool  Si es true, devuelve solo true/false sin cortar la ejecución.
 * 
 * - Propietarios siempre pueden continuar.
 * - Empleados deben tener al menos un rol permitido.
 */
function canEmployeePerform($allowedRoles, $returnBool = false) {
    // Normalizamos a array
    $allowedRoles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];

    // ✅ Si es propietario, tiene acceso total
    if (isset($_SESSION['usuario']['id'])) {
        return true;
    }

    // 🚫 Si no hay sesión de empleado
    if (!isset($_SESSION['empleado_auth']['id'])) {
        if ($returnBool) return false;
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Sesión de empleado no válida.']);
        exit;
    }

    $empleadoId = $_SESSION['empleado_auth']['id'];

    // 🔹 Obtener roles actualizados del empleado
    $empleado = DashboardEmpleadoController::obtenerDatosEmpleado($empleadoId);
    $rolesEmpleado = array_map('strtoupper', array_column($empleado['roles'], 'nombre'));

    // 🔹 Si el empleado tiene alguno de los roles requeridos
    foreach ($allowedRoles as $rol) {
        if (in_array(strtoupper($rol), $rolesEmpleado)) {
            return true;
        }
    }

    if ($returnBool) return false;

    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'No tienes permisos suficientes para realizar esta acción.'
    ]);
    exit;
}
