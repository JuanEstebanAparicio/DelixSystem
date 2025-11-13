<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../config/supabase.php';

// ================================
// 1️⃣ Verificar parámetro recibido
// ================================
$pedido_id = $_POST['pedido_id'] ?? null;

if (!$pedido_id) {
    echo json_encode([
        "success" => false,
        "error" => "No se recibió el ID del pedido."
    ]);
    exit;
}

// ================================
// 2️⃣ Obtener el pedido actual
// ================================
$stmt = $conexion->prepare("SELECT estado FROM orders WHERE id = :id");
$stmt->execute([':id' => $pedido_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo json_encode([
        "success" => false,
        "error" => "El pedido no existe."
    ]);
    exit;
}

$estadoActual = $pedido['estado'];

// ================================
// 3️⃣ Validar si se puede cancelar
// Solo permitido desde:
//  - Pending
//  - Accepted
// ================================
$estadosPermitidos = ["Pending", "Accepted"];

if (!in_array($estadoActual, $estadosPermitidos)) {
    echo json_encode([
        "success" => false,
        "error" => "Este pedido ya no se puede cancelar (estado actual: $estadoActual)."
    ]);
    exit;
}

// ================================
// 4️⃣ Actualizar estado → "Canceled"
// ================================
$update = $conexion->prepare("
    UPDATE orders 
    SET estado = 'Canceled'
    WHERE id = :id
");

$update->execute([':id' => $pedido_id]);

echo json_encode([
    "success" => true,
    "mensaje" => "El pedido #$pedido_id ha sido cancelado correctamente."
]);
exit;
?>
