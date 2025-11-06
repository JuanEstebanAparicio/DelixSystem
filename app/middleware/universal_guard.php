<?php
// DelixSystem/app/middleware/universal_guard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/config/database.php';

function universalGuard() {
    // 👑 Propietario
    if (isset($_SESSION['usuario']['id'])) {
        return [
            'tipo' => 'propietario',
            'id' => $_SESSION['usuario']['id'],
            'nombre' => $_SESSION['usuario']['first_name'] ?? 'Propietario'
        ];
    }

    // 👷‍♂️ Empleado
    if (isset($_SESSION['empleado_auth']['id'])) {
        $empleadoId = $_SESSION['empleado_auth']['id'];
        $result = supabase('employees', 'GET', null, '?id=eq.' . $empleadoId);

        // Si no existe más → limpiar sesión
        if (!$result || empty($result['data'])) {
            unset($_SESSION['empleado_auth']);
            header('Location: /DelixSystem/public/index.php?session=expired');
            exit();
        }

        return [
            'tipo' => 'empleado',
            'id' => $empleadoId,
            'restaurant_id' => $_SESSION['empleado_auth']['user_id'] ?? null,
            'nombre' => $_SESSION['empleado_auth']['full_name'] ?? 'Empleado'
        ];
    }

    // 🚫 Nadie logueado
    header('Location: /DelixSystem/public/index.php');
    exit();
}
