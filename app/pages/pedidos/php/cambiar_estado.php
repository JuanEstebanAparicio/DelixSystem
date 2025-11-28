<?php
// DelixSystem/app/pages/orders_manager/php/cambiar_estado.php

require_once __DIR__ . '/../../../middleware/controller_bootstrap.php';
require_once __DIR__ . '/../../../config/supabase.php';

$isAjax = isAjaxRequest();
$redirect = '/DelixSystem/app/pages/pedidos/view/listar_pedidos.php';

// =========================
// 🔒 CONTROL DE ACCESO
// =========================
try {
    verifyRoleAccess('pedidos', 'estado');
} catch(Throwable $e) {
    returnJson($isAjax, 'error', 'No tienes permisos para modificar pedidos.');
}

// =========================
// 🧩 Validar datos base
// =========================
if (!isset($_POST['pedido_id'])) {
    returnJson($isAjax, 'error', 'ID del pedido no especificado.');
}

$id = intval($_POST['pedido_id']);
$accion = $_POST['accion'] ?? 'estado';

// =========================
// 🔎 Obtener propietario real
// =========================
[$id_propietario, $error] = getPropietarioID($conexion);

if ($error) {
    returnJson($isAjax, 'error', $error);
}

// =========================
// 🗑 ELIMINAR PEDIDO
// =========================
if ($accion === 'delete') {

    try {
        $conexion->beginTransaction();

        // Eliminar items primero
        $stmtItems = $conexion->prepare("
            DELETE FROM order_items 
            WHERE order_id = :id
        ");
        $stmtItems->execute([":id" => $id]);

        // Eliminar pedido
        $stmtOrder = $conexion->prepare("
            DELETE FROM orders 
            WHERE id = :id AND id_user = :owner
        ");
        $stmtOrder->execute([
            ":id"    => $id,
            ":owner" => $id_propietario
        ]);

        if ($stmtOrder->rowCount() === 0) {
            $conexion->rollBack();
            returnJson($isAjax, 'error', 'No se pudo eliminar el pedido o no existe.');
        }

        $conexion->commit();

        // 📝 Auditoría
        auditLog('pedidos', 'eliminar', [
            'target_table' => 'orders',
            'target_id'    => $id
        ]);

        if ($isAjax) {
    returnJson(true, 'success', 'Pedido eliminado correctamente.');
} else {
    header("Location: {$redirect}?deleted=1");
    exit;
}


    } catch (Exception $e) {
        $conexion->rollBack();
        returnJson($isAjax, 'error', 'Error al eliminar: ' . $e->getMessage());
    }
}

// =========================
// 🔄 CAMBIAR ESTADO
// =========================
if (!isset($_POST['nuevo_estado'])) {
    returnJson($isAjax, 'error', 'Nuevo estado no especificado.');
}

$nuevo = trim($_POST['nuevo_estado']);

// Flujo válido
$flow = [
    "Pending"     => "Accepted",
    "Accepted"    => "In_progress",
    "In_progress" => "Ready",
    "Ready"       => "Delivered",
    "Canceled"    => "Canceled"
];

if (!in_array($nuevo, array_values($flow)) && !array_key_exists($nuevo, $flow)) {
    returnJson($isAjax, 'error', 'Estado no permitido.');
}

try {
    // Actualizar estado
    $stmt = $conexion->prepare("
        UPDATE orders 
        SET estado = :nuevo
        WHERE id = :id
        AND id_user = :owner
    ");

    $stmt->execute([
        ":nuevo" => $nuevo,
        ":id"    => $id,
        ":owner" => $id_propietario
    ]);

    if ($stmt->rowCount() === 0) {
        returnJson($isAjax, 'error', 'Pedido no encontrado o sin permiso.');
    }

    // Si se entrega → marcar pagado
    if ($nuevo === "Delivered") {
        $stmt2 = $conexion->prepare("
            UPDATE orders 
            SET pagado = 1 
            WHERE id = :id AND id_user = :owner
        ");
        $stmt2->execute([
            ":id"    => $id,
            ":owner" => $id_propietario
        ]);
    }

    // 📝 Auditoría
    auditLog('pedidos', 'cambiar_estado', [
        'target_table' => 'orders',
        'target_id'    => $id,
        'new'          => ['estado' => $nuevo]
    ]);

    if ($isAjax) {
    returnJson(true, 'success', "Estado actualizado a <b>$nuevo</b> correctamente.");
} else {
    header("Location: {$redirect}?updated=1");
    exit;
}


} catch (Exception $e) {
    returnJson($isAjax, 'error', 'Error al actualizar estado: ' . $e->getMessage());
}
