<?php
require_once __DIR__ . '/../../../config/supabase.php';

if (!isset($_POST['id_pedido'])) {
    echo json_encode(['ok' => false, 'error' => 'Pedido no especificado']);
    exit;
}

$id = intval($_POST['id_pedido']);

$stmt = $conexion->prepare("
    SELECT id_platillo, nombre_platillo, precio, cantidad
    FROM order_items
    WHERE order_id = :id
");
$stmt->execute([':id' => $id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['ok' => true, 'items' => $items]);
