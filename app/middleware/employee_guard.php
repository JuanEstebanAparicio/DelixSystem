<?php
// DelixSystem/app/middleware/employee_guard.php

// 🧱 Iniciar sesión si aún no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ✅ Protege páginas del dashboard de empleados.
 * Si no hay sesión, redirige al login de empleados.
 */
function protectEmpleado() {
    if (!isset($_SESSION['empleado_auth']['id'])) {
        header('Location: /DelixSystem/public/index.php');
        exit();
    }
}

/**
 * 🚫 Evita que empleados logueados accedan al index público o al login.
 * Si ya hay sesión, los manda directo a su dashboard.
 */
function redirectIfEmpleadoLoggedIn() {
    if (isset($_SESSION['empleado_auth']['id'])) {
        header('Location: /DelixSystem/app/pages/dashboard_empleado/view/index.php');
        exit();
    }
}
