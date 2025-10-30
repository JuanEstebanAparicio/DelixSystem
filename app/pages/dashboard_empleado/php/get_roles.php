<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../php/DashboardEmpleadoController.php';

session_start();

if (!isset($_SESSION['empleado_auth']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$empleadoId = $_SESSION['empleado_auth']['id'];

// Usamos el mismo controller para mantener consistencia
$empleado = DashboardEmpleadoController::obtenerDatosEmpleado($empleadoId);

echo json_encode([
    'roles' => $empleado['roles']
]);
