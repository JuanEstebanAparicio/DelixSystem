<?php
// DelixSystem/app/middleware/employee_guard.php

// 🔒 Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ✅ Protege páginas que requieren login de empleado.
 * Si no hay sesión, redirige al login de empleados.
 */
function protectEmpleado() {
    if (!isset($_SESSION['empleado_auth']['id'])) {
        header('Location: /DelixSystem/public/index.php');
        exit();
    }
}

/**
 * 🚪 Si ya está logueado, evita que regrese al login.
 */
function checkIfEmpleadoLoggedIn() {
    if (isset($_SESSION['empleado_auth']['id'])) {
        header('Location: /DelixSystem/app/pages/dashboard_empleado/view/index.php');
        exit();
    }
}
