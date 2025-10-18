<?php

session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: ../public/index.php');

    exit;
}

$tiempoMaximo = 60 * 60; 
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso'] > $tiempoMaximo)) {
    session_unset();
    session_destroy();
    header('Location: ../public/index.php?expired=true');
    exit;
}

$_SESSION['ultimo_acceso'] = time();
