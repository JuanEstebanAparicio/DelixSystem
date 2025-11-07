<?php
// ✅ Verifica si el empleado sigue existiendo en BD
require_once __DIR__ . '/../../../../app/config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['empleado_auth']['id'])) {
    echo json_encode(['active' => false, 'reason' => 'no_session']);
    exit;
}

try {
    $empleadoId = $_SESSION['empleado_auth']['id'];
    $result = supabase('employees', 'GET', null, '?id=eq.' . $empleadoId);

    if (!$result || empty($result['data'])) {
        unset($_SESSION['empleado_auth']);
        echo json_encode(['active' => false, 'reason' => 'deleted']);
        exit;
    }

    echo json_encode(['active' => true]);
} catch (Throwable $e) {
    echo json_encode(['active' => false, 'reason' => 'error']);
}
