<?php
header("Content-Type: application/json");
require_once __DIR__ . '/RolesController.php';

try {
    $controller = new RolesController();
    $roles = $controller->listarRoles();

    echo json_encode([
        "status" => "success",
        "data" => $roles
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al obtener roles: " . $e->getMessage()
    ]);
}
