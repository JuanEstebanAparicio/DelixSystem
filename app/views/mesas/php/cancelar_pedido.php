<?php
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['ok' => false, 'message' => 'ID de pedido no válido']);
    exit;
}

$pedidoId = intval($_GET['id']);

// Verificar si el cliente está autenticado
if (empty($_SESSION['cliente'])) {
    echo json_encode(['ok' => false, 'message' => 'No estás autenticado']);
    exit;
}

// Actualizar el estado del pedido en la base de datos
$query = "UPDATE pedidos SET estado = 'Cancelado' WHERE id = :id";
$stmt = $conexion->prepare($query);
$stmt->execute(['id' => $pedidoId]);

// Comprobar si la actualización fue exitosa
if ($stmt->rowCount() > 0) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'message' => 'No se pudo cancelar el pedido']);
}
