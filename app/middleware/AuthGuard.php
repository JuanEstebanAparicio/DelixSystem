<?php
// PROJECTDELIX/app/middleware/AuthGuard.php

session_start();

// Si no existe una sesión de admin activa, redirigir al login
if (!isset($_SESSION['admin'])) {
    header('Location: ../public/index.php');

    exit;
}

// Opcional: proteger contra inactividad (expiración de sesión)
$tiempoMaximo = 60 * 60; // 1 hora
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso'] > $tiempoMaximo)) {
    session_unset();
    session_destroy();
    header('Location: ../public/index.php?expired=true');
    exit;
}

// Actualiza el tiempo del último acceso
$_SESSION['ultimo_acceso'] = time();
