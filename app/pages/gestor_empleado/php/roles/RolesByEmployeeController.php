<?php
header("Content-Type: application/json");

require_once __DIR__ . '/RolesController.php';
require_once __DIR__ . '/../employee/EmpleadoModel.php';

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "ID de empleado requerido"]);
    exit;
}

$idEmpleado = intval($_GET['id']);

try {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("
        SELECT r.id, r.nombre
        FROM employee_roles er
        INNER JOIN roles r ON er.rol_id = r.id
        WHERE er.empleado_id = :id
    ");
    $stmt->execute(['id' => $idEmpleado]);
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $roles]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error al obtener roles del empleado"]);
}
