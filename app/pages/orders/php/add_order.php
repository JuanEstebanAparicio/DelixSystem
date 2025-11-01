<?php
session_start();
require_once __DIR__ . '/../../../../config/supabase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $id_mesa = $data['id_mesa'] ?? null;
    $items = $data['items'] ?? [];
    $total = $data['total'] ?? 0;

    if (!$id_mesa || empty($items)) {
        http_response_code(400);
        echo json_encode(["error" => "Datos incompletos"]);
        exit;
    }

    // Obtener propietario (temporal: se puede ajustar con la mesa)
    $id_user = $data['id_user'] ?? 1;

    try {
        $conexion->beginTransaction();

        $stmt = $conexion->prepare("INSERT INTO orders (id_mesa, id_user, total) VALUES (?, ?, ?)");
        $stmt->execute([$id_mesa, $id_user, $total]);
        $orderId = $conexion->lastInsertId();

        $stmtItem = $conexion->prepare("INSERT INTO order_items (order_id, dish_id, cantidad, subtotal) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmtItem->execute([$orderId, $item['id'], $item['cantidad'], $item['subtotal']]);
        }

        $conexion->commit();
        echo json_encode(["success" => true, "order_id" => $orderId]);
    } catch (PDOException $e) {
        $conexion->rollBack();
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
