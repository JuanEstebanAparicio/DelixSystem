<?php
// Inicia sesión solo si no está activa
// DelixSystem/app/middleware/session_guard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Protección de páginas: 
 * Si el usuario NO está logueado, redirige al index público
 */
function protectPage() {
    if (!isset($_SESSION['usuario']['id'])) {
        header('Location: ../../../../public/index.php');
        exit();
    }
}

/**
 * Verificación inversa:
 * Si el usuario YA está logueado, lo redirige al dashboard
 */
function checkIfLoggedIn() {
    if (isset($_SESSION['usuario']['id'])) {
        header('Location: ../../DelixSystem/app/pages/dashboard_propietario/view/index.php');
        exit();
    }
}
?>
