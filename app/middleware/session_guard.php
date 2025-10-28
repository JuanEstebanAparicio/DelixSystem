<?php
// DelixSystem/app/middleware/session_guard.php

// 🔒 Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ✅ Protege páginas que requieren login y rol específico
 * Si el usuario no tiene sesión o no tiene el rol correcto,
 * será redirigido al inicio público.
 */
function protectPage($requiredRole = null) {
    // Si no está logueado
    if (!isset($_SESSION['usuario']['id'])) {
        header('Location: /DelixSystem/public/index.php');
        exit();
    }

    // Si se requiere un rol específico (por ejemplo: propietario)
    if ($requiredRole !== null) {
        $rolUsuario = $_SESSION['usuario']['rol'] ?? null;

        if ($rolUsuario !== $requiredRole) {
            // Puedes redirigir a otra página según el rol, o simplemente al inicio
            header('Location: /DelixSystem/public/index.php');
            exit();
        }
    }
}

/**
 * 🚪 Si ya está logueado, evita que regrese al login
 */
function checkIfLoggedIn() {
    if (isset($_SESSION['usuario']['id'])) {
        header('Location: /DelixSystem/app/pages/dashboard_propietario/view/index.php');
        exit();
    }
}
