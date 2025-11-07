<?php
// ========================================
// 🧩 EmpleadoSessionCheck.php — verificación activa de sesión
// ========================================
require_once __DIR__ . '/../../../../app/config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=UTF-8');

// 🧩 1. Comprobamos sesión activa
if (empty($_SESSION['empleado_auth']['id'])) {
    echo json_encode(['active' => false, 'reason' => 'no_session']);
    exit;
}

try {
    $empleadoId = (int) $_SESSION['empleado_auth']['id'];

    // 🧩 2. Consultamos estado del empleado
    $result = supabase(
        'employees',
        'GET',
        null,
        '?id=eq.' . $empleadoId . '&select=id,is_online,deleted_at'
    );

    // --- Validar respuesta de Supabase ---
    if ($result['status'] !== 200 || empty($result['data'])) {
        session_unset();
        session_destroy();
        echo json_encode(['active' => false, 'reason' => 'not_found']);
        exit;
    }

    $empleado = $result['data'][0];

    // 🧩 3. Revisamos si está marcado como eliminado o desconectado
    $estaEliminado = isset($empleado['deleted_at']) && $empleado['deleted_at'] !== null;
    $estaOffline   = isset($empleado['is_online']) && !$empleado['is_online'];

    if ($estaEliminado || $estaOffline) {
        session_unset();
        session_destroy();
        echo json_encode(['active' => false, 'reason' => $estaEliminado ? 'deleted' : 'disabled']);
        exit;
    }

    // ✅ 4. Activo correctamente
    echo json_encode(['active' => true]);

} catch (Throwable $e) {
    error_log("[EmpleadoSessionCheck] " . $e->getMessage());
    echo json_encode(['active' => false, 'reason' => 'error']);
}
