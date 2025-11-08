<?php
// DelixSystem/app/pages/orders_manager/php/cambiar_estado.php
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

if(!isset($_POST['pedido_id']) || !isset($_POST['nuevo_estado'])){
    $_SESSION['flash_error'] = "Datos incompletos para cambiar estado.";
    session_write_close();
    header("Location: ../view/listar_pedidos.php");
    exit;
}

$id = intval($_POST['pedido_id']);
$nuevo = trim($_POST['nuevo_estado']);

$flow = [
    "Pending" => "Accepted",
    "Accepted" => "In_progress",
    "In_progress" => "Ready",
    "Ready" => "Delivered"
];

// validación para impedir estados locos
$flow = [
    "Pending" => "Accepted",
    "Accepted" => "In_progress",
    "In_progress" => "Ready",
    "Ready" => "Delivered"
];

if(!in_array($nuevo, $flow)){
    $_SESSION['flash_error'] = "Estado no permitido.";
    session_write_close();
    header("Location: ../view/ver_pedido.php?id=".$id);
    exit;
}


try{
    $stmt = $conexion->prepare("UPDATE orders SET estado = :nuevo_estado WHERE id = :id");
    $stmt->execute([
        ":nuevo_estado" => $nuevo,
        ":id" => $id
    ]);
    // si llegó a estado final delivered -> marcar pagado automáticamente
if ($nuevo === "Delivered") {
    $stmtP = $conexion->prepare("UPDATE orders SET pagado = 1 WHERE id = :id");
    $stmtP->execute([":id" => $id]);
}
    $_SESSION['flash_msg'] = "Estado actualizado a <b>$nuevo</b> correctamente.";

}catch(Exception $e){
    $_SESSION['flash_error'] = "Error al actualizar estado: ".$e->getMessage();
}

session_write_close();
header("Location: ../view/ver_pedido.php?id=".$id);
exit;
