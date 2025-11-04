<?php
header("Content-Type: application/json");
require_once __DIR__ . '/RolesController.php';
require_once __DIR__ . '/../../../../middleware/controller_bootstrap.php';
try {
    verifyRoleAccess('roles', 'listar');

    $controller = new RolesController();
    $roles = $controller->listarRoles();

    echo json_encode([
        "status" => "success",
        "data" => $roles
    ]);
} catch (Throwable $e) {
    // Manejo seguro del error sin romper el JSON
    echo json_encode([
        "status" => "error",
        "message" => "Error al obtener roles: " . $e->getMessage()
    ]);
}
