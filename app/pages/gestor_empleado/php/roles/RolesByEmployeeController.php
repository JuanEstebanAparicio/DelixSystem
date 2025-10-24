<?php
header("Content-Type: application/json");
require_once __DIR__ . '/RolesController.php';

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "ID de empleado no especificado"]);
    exit;
}

try {
    $controller = new RolesController();
    $roles = $controller->listarRolesPorEmpleado($_GET['id']);

    echo json_encode([
        "status" => "success",
        "data" => $roles
    ]);
} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al obtener roles del empleado: " . $e->getMessage()
    ]);
}
