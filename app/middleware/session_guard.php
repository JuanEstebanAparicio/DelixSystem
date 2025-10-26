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
        header('Location: /DelixSystem/public/index.php');
        exit();
    }
}

function checkIfLoggedIn() {
    if (isset($_SESSION['usuario']['id'])) {
        header('Location: /DelixSystem/app/pages/dashboard_propietario/view/index.php');
        exit();
    }
}

?>
