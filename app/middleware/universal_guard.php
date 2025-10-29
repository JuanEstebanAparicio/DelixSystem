<?php
// DelixSystem/app/middleware/universal_guard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 🧠 universalGuard()
 * Permite acceso tanto a propietarios como a empleados.
 * Detecta automáticamente de qué tipo es la sesión.
 * Retorna un arreglo con la información básica del usuario.
 */
function universalGuard() {
    // 👑 Caso 1: Propietario logueado
    if (isset($_SESSION['usuario']['id'])) {
        return [
            'tipo' => 'propietario',
            'id' => $_SESSION['usuario']['id'],
            'nombre' => $_SESSION['usuario']['first_name'] ?? 'Propietario'
        ];
    }

    // 👷‍♂️ Caso 2: Empleado logueado
    if (isset($_SESSION['empleado_auth']['id'])) {
        return [
            'tipo' => 'empleado',
            'id' => $_SESSION['empleado_auth']['id'],
            'restaurant_id' => $_SESSION['empleado_auth']['user_id'] ?? null, // id del propietario
            'nombre' => $_SESSION['empleado_auth']['full_name'] ?? 'Empleado'
        ];
    }

    // 🚫 Nadie logueado → redirigir al inicio público
    header('Location: /DelixSystem/public/index.php');
    exit();
}

