<?php
// DelixSystem/app/pages/pedidos/php/pedidosController.php

header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

$data = json_decode(file_get_contents("php://input"), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["success"=>false,"error"=>"JSON inválido"]);
    exit;
}

$required = ['id_user','restaurant_name','id_area','area','id_mesa','mesa','total','metodo_pago','items'];
foreach($required as $r){
    if(!isset($data[$r])){
        http_response_code(400);
        echo json_encode(["success"=>false,"error"=>"Falta campo $r"]);
        exit;
    }
}

// regla final general
// si paga tarjeta = TRUE
// otros metodos = FALSE
$pagado = ($data['metodo_pago'] === 'tarjeta') ? 1 : 0;

// estado inicial de un pedido NUEVO
$estado_inicial = "Pending";

try {
    $conexion->beginTransaction();

    $stmt = $conexion->prepare("INSERT INTO orders
        (id_user, restaurant_name, id_area, area, id_mesa, mesa, nombre_cliente, total_pedido, metodo_pago, pagado, estado)
        VALUES (:id_user,:restaurant_name,:id_area,:area,:id_mesa,:mesa,:nombre_cliente,:total_pedido,:metodo_pago,:pagado,:estado)
        RETURNING id");

    $stmt->execute([
        ":id_user"=>$data['id_user'],
        ":restaurant_name"=>$data['restaurant_name'],
        ":id_area"=>$data['id_area'],
        ":area"=>$data['area'],
        ":id_mesa"=>$data['id_mesa'],
        ":mesa"=>$data['mesa'],
        ":nombre_cliente"=>$data['nombre_cliente'] ?? null,
        ":total_pedido"=>$data['total'],
        ":metodo_pago"=>$data['metodo_pago'],
        ":pagado"=>$pagado,
        ":estado"=>$estado_inicial
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$order || !isset($order['id'])) throw new Exception("No se creó la orden");

    $order_id = $order['id'];

    $stmtItem = $conexion->prepare("INSERT INTO order_items
        (order_id,id_platillo,nombre_platillo,precio,cantidad)
        VALUES(:order_id,:id_platillo,:nombre_platillo,:precio,:cantidad)");

    foreach($data['items'] as $i){
        $stmtItem->execute([
            ":order_id"=>$order_id,
            ":id_platillo"=>$i['id_platillo'],
            ":nombre_platillo"=>$i['nombre_platillo'],
            ":precio"=>$i['precio'],
            ":cantidad"=>$i['cantidad']
        ]);
    }

    $conexion->commit();
    echo json_encode(["success"=>true,"order_id"=>$order_id]);

} catch (Throwable $e){
    $conexion->rollBack();
    error_log("pedidoController error: ".$e->getMessage());
    echo json_encode(["success"=>false,"error"=>$e->getMessage()]);
}
