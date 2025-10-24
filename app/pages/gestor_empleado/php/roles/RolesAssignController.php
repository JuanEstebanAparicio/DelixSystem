<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../../../config/supabase.php';

try {
    // ============================
    // 🔹 Validar entrada
    // ============================
    $empleadoId = $_POST['empleado_id'] ?? null;
    $roles = isset($_POST['roles']) ? json_decode($_POST['roles'], true) : [];

    if (!$empleadoId) {
        throw new Exception("Falta el ID del empleado.");
    }

    // ============================
    // 🔹 Limpiar roles existentes
    // ============================
    $stmt = $conexion->prepare("DELETE FROM employee_roles WHERE empleado_id = :id");
    $stmt->execute([':id' => $empleadoId]);

    // ============================
    // 🔹 Insertar nuevos roles
    // ============================
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

        // 🔹 Actualizar el campo 'role' del empleado (solo el primero)
        $firstRoleQuery = $conexion->prepare("SELECT nombre FROM roles WHERE id = :id LIMIT 1");
        $firstRoleQuery->execute([':id' => $roles[0]]);
        $rolPrincipal = $firstRoleQuery->fetchColumn();

        if ($rolPrincipal) {
            $updateEmp = $conexion->prepare("UPDATE employees SET role = :role WHERE id = :id");
            $updateEmp->execute([
                ':role' => $rolPrincipal,
                ':id' => $empleadoId
            ]);
        }
    } else {
        // Si se quitaron todos los roles, limpiamos el campo "role"
        $updateEmp = $conexion->prepare("UPDATE employees SET role = NULL WHERE id = :id");
        $updateEmp->execute([':id' => $empleadoId]);
    }

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
