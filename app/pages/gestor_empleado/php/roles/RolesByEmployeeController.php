<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../../../config/supabase.php';
require_once __DIR__ . '/../../../../middleware/controller_bootstrap.php';
try {
    verifyRoleAccess('roles', 'ver');
    if (!isset($_GET['id'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Falta parámetro ID del empleado"
        ]);
        exit;
    }

    $empleado_id = intval($_GET['id']);

    // ✅ Obtener todos los roles asignados al empleado (relación muchos-a-muchos)
    $stmt = $conexion->prepare("
        SELECT r.id, r.nombre, r.descripcion
        FROM roles r
        INNER JOIN employee_roles er ON er.rol_id = r.id
        WHERE er.empleado_id = :empleado_id
    ");
    $stmt->bindParam(':empleado_id', $empleado_id, PDO::PARAM_INT);
    $stmt->execute();

    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $roles
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al obtener roles del empleado",
        "details" => $e->getMessage()
    ]);
}
