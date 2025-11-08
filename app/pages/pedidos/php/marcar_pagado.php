<?php 
// DelixSystem/app/pages/orders_manager/marcar_pagado.php
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

// Validación mínima
if(!isset($_POST['pedido_id']) || empty($_POST['pedido_id'])){
    $_SESSION['flash_error'] = "No se envió ID de pedido válido.";
    session_write_close();
    header("Location: ../view/listar_pedidos.php");
    exit;
}

$id = intval($_POST['pedido_id']);

try {

    $stmt = $conexion->prepare("UPDATE orders SET estado = 'paid', pagado = 1 WHERE id = :id");
    $stmt->execute([":id" => $id]);

    $_SESSION['last_pagado'] = 1;   // << ESTA ES LA LÍNEA IMPORTANTE

} catch (Exception $e) {

    $_SESSION['flash_error'] = "⚠ Error al marcar como pagado. ".$e->getMessage();
}

// IMPORTANTE CERRAR SESIÓN ANTES DEL REDIRECT
session_write_close();

// volvemos a ver el pedido
header("Location: ../view/ver_pedido.php?id=".$id);
exit;
