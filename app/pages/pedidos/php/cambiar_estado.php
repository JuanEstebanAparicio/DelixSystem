<?php
// DelixSystem/app/pages/orders_manager/php/cambiar_estado.php

require_once __DIR__ . '/../../../middleware/controller_bootstrap.php';  // ← Bootstrap + roles + auditoría
require_once __DIR__ . '/../../../config/supabase.php';

$isAjax = isAjaxRequest();

// =========================
// 🔒 CONTROL DE ACCESO
// =========================
try {
    verifyRoleAccess('pedidos', 'estado');  // <--- PERMISOS
} catch(Throwable $e) {
    returnJson($isAjax, 'error', 'No tienes permisos para cambiar el estado de pedidos.');
}

// =========================
// 🧩 Validar datos
// =========================
if (!isset($_POST['pedido_id']) || !isset($_POST['nuevo_estado'])) {
    returnJson($isAjax, 'error', 'Datos incompletos para cambiar estado.');
}

$id = intval($_POST['pedido_id']);
$nuevo = trim($_POST['nuevo_estado']);

// Flujo válido de estados
$flow = [
    "Pending"     => "Accepted",
    "Accepted"    => "In_progress",
    "In_progress" => "Ready",
    "Ready"       => "Delivered"
];

if (!array_key_exists($nuevo, $flow) && !in_array($nuevo, $flow)) {
    returnJson($isAjax, 'error', 'Estado no permitido.');
}

// =========================
// 🔎 Obtener propietario real
// =========================
[$id_propietario, $error] = getPropietarioID($conexion);

if ($error) {
    returnJson($isAjax, 'error', $error);
}

// =========================
// 🔄 Actualizar estado
// =========================
try {
    // Marcar nuevo estado
    $stmt = $conexion->prepare("
        UPDATE orders 
        SET estado = :nuevo_estado
        WHERE id = :id
          AND id_user = :owner
    ");

    $stmt->execute([
        ":nuevo_estado" => $nuevo,
        ":id"           => $id,
        ":owner"        => $id_propietario
    ]);

    // Si llega a delivered → marcar pagado
    if ($nuevo === "Delivered") {
        $stmt2 = $conexion->prepare("
            UPDATE orders SET pagado = 1 
            WHERE id = :id AND id_user = :owner
        ");
        $stmt2->execute([
            ":id" => $id,
            ":owner" => $id_propietario
        ]);
    }

    // =========================
    // 📝 AUDITORÍA
    // =========================
    auditLog('pedidos', 'cambiar_estado', [
        'target_table' => 'orders',
        'target_id'    => $id,
        'new'          => ['estado' => $nuevo]
    ]);

    returnJson($isAjax, 'success', "Estado actualizado a <b>$nuevo</b> correctamente.");

} catch (Exception $e) {
    returnJson($isAjax, 'error', 'Error al actualizar estado: ' . $e->getMessage());
}
