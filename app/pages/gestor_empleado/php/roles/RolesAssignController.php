<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../../../config/supabase.php';
require_once __DIR__ . '/../../../../middleware/controller_bootstrap.php'; 
try {
    verifyRoleAccess('roles', 'asignar');

$empleadoId = $_POST['empleado_id'] ?? null;
$roles = isset($_POST['roles']) ? json_decode($_POST['roles'], true) : [];

if (!$empleadoId) {
    throw new Exception("Falta el ID del empleado.");
}

// 🟡 Obtener OLD roles
$stmtOld = $conexion->prepare("
    SELECT rol_id 
    FROM employee_roles 
    WHERE empleado_id = :id
");
$stmtOld->execute([':id' => $empleadoId]);
$oldRoles = array_column($stmtOld->fetchAll(PDO::FETCH_ASSOC), 'rol_id');

// 🟠 Borrar roles actuales
$stmt = $conexion->prepare("DELETE FROM employee_roles WHERE empleado_id = :id");
$stmt->execute([':id' => $empleadoId]);

// 🟢 Insertar nuevos roles
if (!empty($roles)) {
    $stmt = $conexion->prepare("
        INSERT INTO employee_roles (empleado_id, rol_id)
        VALUES (:empleado_id, :rol_id)
    ");
    foreach ($roles as $rolId) {
        $stmt->execute([
            ':empleado_id' => $empleadoId,
            ':rol_id' => $rolId
        ]);
    }
}

// 🟢 Auditoría
auditLog('roles', 'asignar', [
    'target_table' => 'employee_roles',
    'target_id' => $empleadoId,
    'old' => ['roles' => $oldRoles],
    'new' => ['roles' => $roles]
]);

echo json_encode([
    "status" => "success",
    "message" => "Roles actualizados correctamente"
]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al actualizar roles: " . $e->getMessage()
    ]);
}
?>
