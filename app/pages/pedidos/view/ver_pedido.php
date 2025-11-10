<?php
// DelixSystem/app/pages/pedidos/view/ver_pedido.php
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

if (!isset($_GET['id'])) {
    header("Location: listar_pedidos.php");
    exit;
}

$id = intval($_GET['id']);

// obtener pedido principal
$stmt = $conexion->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->execute([":id" => $id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<p>Pedido no encontrado.</p>";
    echo '<p><a href="listar_pedidos.php">Volver a pedidos</a></p>';
    exit;
}

// obtener items
$stmt2 = $conexion->prepare("SELECT * FROM order_items WHERE order_id = :id");
$stmt2->execute([":id" => $id]);
$items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$paid = ($order['estado'] === 'paid');

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pedido #<?= htmlspecialchars($order['id']) ?></title>
<link rel="stylesheet" href="../css/ver_pedidos.css">
</head>
<body>

<?php if(isset($_SESSION['flash_msg'])): ?>
    <div class='alert alert-success' style='margin-bottom:15px;'><?= $_SESSION['flash_msg']; ?></div>
<?php unset($_SESSION['flash_msg']); endif; ?>

<?php if(isset($_SESSION['flash_error'])): ?>
    <div class='alert alert-danger' style='margin-bottom:15px;'><?= $_SESSION['flash_error']; ?></div>
<?php unset($_SESSION['flash_error']); endif; ?>


<div class="pedido-container">

    <h2>Pedido #<?= htmlspecialchars($order['id']) ?></h2>

    <p><strong>Restaurante:</strong> <?= htmlspecialchars($order['restaurant_name']) ?></p>
    <p><strong>Área:</strong> <?= htmlspecialchars($order['area']) ?></p>
    <p><strong>Mesa:</strong> <?= htmlspecialchars($order['mesa']) ?></p>
    <p><strong>Fecha:</strong> <?= htmlspecialchars($order['created_at']) ?></p>

    <div class="estado-row">
        <?= $paid ? "<span class='badge-paid'>Pagado</span>" : "<span class='badge-unpaid'>Pendiente</span>" ?>
    </div>

    <h3>Items del Pedido</h3>

    <?php if(empty($items)): ?>
      <p>No hay items registrados para este pedido.</p>
    <?php else: ?>
    <table class="table-items">
        <thead>
            <tr>
                <th>Platillo</th>
                <th>Precio</th>
                <th>Cant</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($items as $i): ?>
            <tr>
                <td><?= htmlspecialchars($i['nombre_platillo']) ?></td>
                <td>$<?= number_format($i['precio'],0,',','.') ?></td>
                <td><?= htmlspecialchars($i['cantidad']) ?></td>
                <td>$<?= number_format((float)$i['cantidad'] * (float)$i['precio'],0,',','.') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <h3>Total Final: $<?= number_format($order['total_pedido'],0,',','.') ?></h3>

   <?php
// ---- CONTROL DE ESTADO DEL PEDIDO ----

$estadoActual = $order['estado'] ?? 'Pending';

// la ruta lógica definida
$flow = [
    "Pending" => "Accepted",
    "Accepted" => "In_progress",
    "In_progress" => "Ready",
    "Ready" => "Delivered"
];

// siguiente estado lógico según flujo
$siguienteEstado = $flow[$estadoActual] ?? null;

?>

<div class="estado-box" style="margin-top:25px;padding:18px;border-radius:10px;border:1px solid #cfcfcf;">
    <h3>Estado Actual: 
        <span class="estado-tag" style="padding:4px 10px;border-radius:6px;background:#f3f3f3;font-weight:bold;">
            <?= ucfirst(str_replace("_"," ",$estadoActual)) ?>
        </span>
    </h3>

    <?php if($siguienteEstado): ?>
        
        <form action="../php/cambiar_estado.php" method="POST" style="margin-top:12px;">
            <input type="hidden" name="pedido_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="nuevo_estado" value="<?= $siguienteEstado ?>">

            <button type="submit" class="btn btn-primary" style="padding:10px 20px;border-radius:8px;font-size:16px;font-weight:bold;">
                Cambiar a <?= ucfirst(str_replace("_"," ",$siguienteEstado)) ?>
            </button>
        </form>

    <?php else: ?>

        <div style="margin-top:12px;font-weight:bold;font-size:18px;color:green;">
            ✅ Pedido finalizado (Delivered)
        </div>

    <?php endif; ?>

</div>



    <div class="btn-container">
        <a class="btn-volver" href="listar_pedidos.php">Volver</a>
    </div>

</div>


<!-- AUDIO: SIEMPRE que hubo flash_msg reproduce -->
<?php if(isset($_SESSION['last_pagado'])): ?>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const audio = new Audio('/DelixSystem/public/audio/pagado.mp3?' + Date.now());
    audio.volume = 0.7;
    audio.play().catch(err=>console.log("autoplay bloqueado", err));
});
</script>
<?php unset($_SESSION['last_pagado']); endif; ?>

</body>
</html>
