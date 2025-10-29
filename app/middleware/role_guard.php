<?php
// DelixSystem/app/middleware/role_guard.php

require_once __DIR__ . '/../pages/dashboard_empleado/php/DashboardEmpleadoController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 🔒 Permite restringir acciones a empleados con un rol específico.
 * Propietarios siempre tienen acceso total.
 */
function allowRole($requiredRoles = []) {
    // ✅ Propietarios siempre permitidos
    if (isset($_SESSION['usuario']['id'])) {
        return true;
    }

    // 🚫 Si no hay sesión de empleado, negar
    if (!isset($_SESSION['empleado_auth']['id'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Acceso no autorizado']);
        exit;
    }

    // 🔹 Obtener roles actualizados del empleado
    $empleado = DashboardEmpleadoController::obtenerDatosEmpleado($_SESSION['empleado_auth']['id']);
    $rolesEmpleado = array_map('strtoupper', array_column($empleado['roles'], 'nombre'));

    // 🔹 Verificar si cumple alguno de los roles requeridos
    foreach ($requiredRoles as $rol) {
        if (in_array(strtoupper($rol), $rolesEmpleado)) {
            return true;
        }
    }

    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No tienes permisos para esta acción.']);
    exit;
}
