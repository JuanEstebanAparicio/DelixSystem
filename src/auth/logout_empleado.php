<?php
// ✅ DelixSystem/src/auth/logout_empleado.php
require_once __DIR__ . '/../../app/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // 🔹 Si existe sesión de empleado, lo marcamos como desconectado
    if (isset($_SESSION['empleado_auth']['id'])) {
        $empleadoId = $_SESSION['empleado_auth']['id'];

        // 🔴 Actualizar estado en la base de datos
        supabaseRest('employees', 'PATCH', ['is_online' => false], '?id=eq.' . $empleadoId);
    }

    // 🔹 Eliminar sesión del empleado
    unset($_SESSION['empleado_auth']);

    // 🔹 (Opcional) destruir toda la sesión si no hay más datos
    // session_destroy();

    // 🔁 Redirigir al login
    header("Location: /DelixSystem/public/index.php");
    exit;

} catch (Exception $e) {
    // ⚠️ Si hay error, igual intentamos cerrar sesión para evitar quedar colgado
    unset($_SESSION['empleado_auth']);
    header("Location: /DelixSystem/public/index.php?logout_error=" . urlencode($e->getMessage()));
    exit;
}
