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
$pagado = ($data['metodo_pago'] === 'tarjeta') ? 1 : 0;

// estado inicial
$estado_inicial = "Pending";


// =============================================================
// 🔥 CONVERSIÓN SIMPLE SOLO PARA G ↔ KG y ML ↔ L
// =============================================================
function convertirUnidad($cantidad, $origen, $destino) {

    // ✅ Si ya están en la misma unidad, no convertir
    if ($origen === $destino) {
        return $cantidad;
    }

    // ---- PESO ----
    if (($origen === "g" || $origen === "kg") && ($destino === "g" || $destino === "kg")) {
        if ($origen === "kg") $cantidad = $cantidad * 1000;
        if ($destino === "kg") return $cantidad / 1000;
        return $cantidad;
    }

    // ---- VOLUMEN ----
    if (($origen === "ml" || $origen === "l") && ($destino === "ml" || $destino === "l")) {
        if ($origen === "l") $cantidad = $cantidad * 1000;
        if ($destino === "l") return $cantidad / 1000;
        return $cantidad;
    }

    throw new Exception("Unidades incompatibles: $origen → $destino");
}




try {
    $conexion->beginTransaction();

    // ===============================
    // 1. CREAR ORDEN
    // ===============================
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

    // ===============================
    // 2. GUARDAR ITEMS
    // ===============================
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

    // ============================================================
    // 3. DESCONTAR INGREDIENTES POR CADA PLATILLO DEL PEDIDO
    // ============================================================

    // datos del ingrediente del platillo
    $stmtIng = $conexion->prepare("
        SELECT ingredient_id, quantity_used, unit
        FROM dish_ingredient
        WHERE dish_id = :dish_id
    ");

    // stock
    $stmtStock = $conexion->prepare("
        SELECT amount, unit
        FROM storage
        WHERE id = :ingredient_id
        FOR UPDATE
    ");

    // update
    $stmtUpdateStock = $conexion->prepare("
        UPDATE storage
        SET amount = amount - :consumo
        WHERE id = :ingredient_id
    ");

    foreach($data['items'] as $item){

        $dish_id = $item['id_platillo'];
        $cantidadPedido = $item['cantidad'];

        // ingredientes del platillo
        $stmtIng->execute([":dish_id"=>$dish_id]);
        $ingredientes = $stmtIng->fetchAll(PDO::FETCH_ASSOC);

        foreach($ingredientes as $ing){

            $ingredient_id = $ing['ingredient_id'];
            $qty_plato = floatval($ing['quantity_used']);
            $unidad_ing = $ing['unit'];

            // stock
            $stmtStock->execute([":ingredient_id"=>$ingredient_id]);
            $stock = $stmtStock->fetch(PDO::FETCH_ASSOC);

            if(!$stock){
                throw new Exception("Ingrediente ID $ingredient_id no existe.");
            }

            $unidad_storage = $stock['unit'];

            // convertir de unidad del platillo a unidad del almacenamiento
            $qty_convertida = convertirUnidad(
                $qty_plato,
                $unidad_ing,
                $unidad_storage
            );

            // consumo total
            $consumo = $qty_convertida * $cantidadPedido;

            if(floatval($stock['amount']) < $consumo){
                throw new Exception("Stock insuficiente para ingrediente ID $ingredient_id");
            }

            // actualizar stock
            $stmtUpdateStock->execute([
                ":consumo"=>$consumo,
                ":ingredient_id"=>$ingredient_id
            ]);
        }
    }

    // ===============================
    // 4. CONFIRMAR
    // ===============================
    $conexion->commit();

    echo json_encode(["success"=>true,"order_id"=>$order_id]);

} catch (Throwable $e){
    $conexion->rollBack();
    error_log("pedidoController error: ".$e->getMessage());
    echo json_encode(["success"=>false,"error"=>$e->getMessage()]);
}
