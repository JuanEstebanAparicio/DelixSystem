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

    // ======================================================
    // 🟡 1. Obtener OLD roles (IDs + nombres)
    // ======================================================
    $stmtOld = $conexion->prepare("
        SELECT r.id, r.nombre 
        FROM roles r
        INNER JOIN employee_roles er ON er.rol_id = r.id
        WHERE er.empleado_id = :id
    ");
    $stmtOld->execute([':id' => $empleadoId]);
    $oldRoles = $stmtOld->fetchAll(PDO::FETCH_ASSOC); 
    // Formato: [ ['id'=>3,'nombre'=>'Mesero'], ... ]


    // ======================================================
    // 🟠 2. Borrar roles actuales
    // ======================================================
    $stmt = $conexion->prepare("DELETE FROM employee_roles WHERE empleado_id = :id");
    $stmt->execute([':id' => $empleadoId]);


    // ======================================================
    // 🟢 3. Insertar NEW roles y obtener nombres
    // ======================================================
    $newRoles = [];

    if (!empty($roles)) {

        // Insertar nuevos
        $stmtInsert = $conexion->prepare("
            INSERT INTO employee_roles (empleado_id, rol_id)
            VALUES (:empleado_id, :rol_id)
        ");

        // Preparar consulta para obtener nombre
        $stmtName = $conexion->prepare("SELECT id, nombre FROM roles WHERE id = :id");

        foreach ($roles as $rolId) {
            // Insertar el rol
            $stmtInsert->execute([
                ':empleado_id' => $empleadoId,
                ':rol_id' => $rolId
            ]);

            // Obtener nombre
            $stmtName->execute([':id' => $rolId]);
            $row = $stmtName->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $newRoles[] = $row; // ['id'=>2, 'nombre'=>'Cajero']
            }
        }
    }


    // ======================================================
    // 🟢 4. AUDITORÍA final con datos completos
    // ======================================================
    auditLog('roles', 'asignar', [
        'target_table' => 'employee_roles',
        'target_id' => $empleadoId,
        'old' => ['roles' => $oldRoles],
        'new' => ['roles' => $newRoles]
    ]);


    // ======================================================
    // 🟢 5. Respuesta final
    // ======================================================
    echo json_encode([
        "status" => "success",
        "message" => "Roles actualizados correctamente."
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al actualizar roles: " . $e->getMessage()
    ]);
}
?>
