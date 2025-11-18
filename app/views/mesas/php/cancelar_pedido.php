<?php
session_start();

header("Content-Type: application/json");

// ⛔ Debug: verificar ruta exacta del archivo
error_log("📍 cancelar_pedido.php está en: " . __DIR__);

// ⛔ Debug: verificar si supabase existe desde este archivo
$testPath = __DIR__ . '/../../../config/supabase.php';
error_log("🔍 Buscando supabase.php en: $testPath");

if (!file_exists($testPath)) {
    error_log("❌ ERROR: supabase.php NO existe en esa ruta");
    echo json_encode(["error" => "Ruta inválida: supabase.php no encontrado"]);
    exit;
}

require_once $testPath;

if (!isset($_POST['pedido_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta el ID del pedido"]);
    exit;
}

$id = intval($_POST['pedido_id']);

try {
    // Traer estado actual
    $stmt = $conexion->prepare("SELECT estado FROM orders WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        http_response_code(404);
        echo json_encode(["error" => "Pedido no encontrado"]);
        exit;
    }

    $estadoActual = $pedido['estado'];

    // Solo permitir cancelar si aún no está en preparación
    $estadosPermitidos = ["Pending", "Accepted"];

    if (!in_array($estadoActual, $estadosPermitidos)) {
        http_response_code(403);
        echo json_encode([
            "error" => "No se puede cancelar el pedido porque ya está en preparación o fue entregado.",
            "estado_actual" => $estadoActual
        ]);
        exit;
    }

    // Actualizar estado a "Canceled"
    $update = $conexion->prepare("UPDATE orders SET estado = 'Canceled' WHERE id = :id");
    $update->execute([':id' => $id]);

    echo json_encode(["success" => true, "mensaje" => "Pedido cancelado correctamente."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al cancelar pedido: " . $e->getMessage()]);
}
