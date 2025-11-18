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

// Obtener los detalles del pedido
$query = "SELECT o.*, oi.nombre_platillo, oi.cantidad, oi.precio
          FROM orders o
          JOIN order_items oi ON oi.order_id = o.id
          WHERE o.id = :id";
$stmt = $conexion->prepare($query);
$stmt->execute(['id' => $pedidoId]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo json_encode(['ok' => false, 'message' => 'Pedido no encontrado']);
    exit;
}

// Obtener los artículos del pedido
$queryArticulos = "SELECT oi.nombre_platillo, oi.cantidad
                   FROM order_items oi
                   WHERE oi.order_id = :id";
$stmtArticulos = $conexion->prepare($queryArticulos);
$stmtArticulos->execute(['id' => $pedidoId]);
$pedido['articulos'] = $stmtArticulos->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['ok' => true, 'pedido' => $pedido]);
