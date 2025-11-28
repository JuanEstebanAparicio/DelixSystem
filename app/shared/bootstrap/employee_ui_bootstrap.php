<?php
/**
 * Bootstrap visual exclusivo para empleados.
 * Carga el header y estilos del empleado solo si la sesión activa pertenece a un empleado.
 */

if (session_status() === PHP_SESSION_NONE) session_start();

$isEmpleado = isset($_SESSION['empleado_auth']) && !empty($_SESSION['empleado_auth']);

if ($isEmpleado) {
    //  Incluir los componentes visuales del empleado
    require_once __DIR__ . '/../../components/header_empleado.php';
    require_once __DIR__ . '/../../components/control_center.php';

    //  Inyectar los estilos y scripts solo cuando el empleado esté autenticado
    echo <<<HTML
    <link rel="stylesheet" href="/DelixSystem/app/shared/css/globals.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/DelixSystem/app/shared/css/control_center.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="/DelixSystem/app/shared/js/control_center.js" defer></script>
    HTML;
}
