<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../../../config/supabase.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }

    $empleadoId = $_POST['empleado_id'] ?? null;
    $roles = $_POST['roles'] ?? [];

    if (!$empleadoId) {
        throw new Exception("ID de empleado no proporcionado");
    }

    // 🔹 Convertir roles en array si llega como JSON
    if (is_string($roles)) {
        $roles = json_decode($roles, true) ?? [];
    }

    // 🔹 Eliminar roles anteriores
    $stmtDel = $conexion->prepare("DELETE FROM employee_roles WHERE empleado_id = ?");
    $stmtDel->execute([$empleadoId]);

    // 🔹 Insertar nuevos roles
    if (!empty($roles)) {
        $stmtIns = $conexion->prepare(
            "INSERT INTO employee_roles (empleado_id, rol_id) VALUES (?, ?)"
        );
        foreach ($roles as $rolId) {
            $stmtIns->execute([$empleadoId, $rolId]);
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "Roles asignados correctamente"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al asignar roles: " . $e->getMessage()
    ]);
}
