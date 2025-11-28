<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/config/database.php';

/**
 *  Protege páginas del dashboard de empleados.
 * Si no hay sesión o el empleado fue eliminado, redirige al login.
 */
function protectEmpleado() {
    if (!isset($_SESSION['empleado_auth']['id'])) {
        header('Location: /DelixSystem/public/index.php');
        exit();
    }

    $empleadoId = $_SESSION['empleado_auth']['id'] ?? null;
    if (!$empleadoId) {
        header('Location: /DelixSystem/public/index.php');
        exit();
    }

    try {
        //  Verificamos que aún exista en BD
        $result = supabaseRest('employees', 'GET', null, '?id=eq.' . $empleadoId);

        if (!$result || empty($result['data'])) {
            //  Empleado ya no existe → cerrar sesión inmediata
            unset($_SESSION['empleado_auth']);
            header('Location: /DelixSystem/public/index.php?session=invalid');
            exit();
        }

        $emp = $result['data'][0];

        //  Si fue marcado como desconectado o inactivo (opcional)
        if (isset($emp['is_active']) && !$emp['is_active']) {
            unset($_SESSION['empleado_auth']);
            header('Location: /DelixSystem/public/index.php?session=disabled');
            exit();
        }

    } catch (Throwable $e) {
        // Si hay error con la API, no arriesgamos la seguridad
        unset($_SESSION['empleado_auth']);
        header('Location: /DelixSystem/public/index.php?session=error');
        exit();
    }
}

/**
 *  Evita que empleados logueados accedan al index público o al login.
 */
function redirectIfEmpleadoLoggedIn() {
    if (isset($_SESSION['empleado_auth']['id'])) {
        header('Location: /DelixSystem/app/pages/dashboard_empleado/view/index.php');
        exit();
    }
}
