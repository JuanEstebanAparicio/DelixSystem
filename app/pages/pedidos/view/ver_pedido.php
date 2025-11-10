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
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/ver_pedidos.css">
</head>
<body>

<?php if(isset($_SESSION['flash_msg'])): ?>
    <div class='alert alert-success'><?= $_SESSION['flash_msg']; ?></div>
<?php unset($_SESSION['flash_msg']); endif; ?>

<?php if(isset($_SESSION['flash_error'])): ?>
    <div class='alert alert-danger'><?= $_SESSION['flash_error']; ?></div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="top-bar">
    <a href="listar_pedidos.php" class="back-button">
        <i class="ri-arrow-left-line"></i>
    </a>
    <h1>Detalles del Pedido #<?= htmlspecialchars($order['id']) ?></h1>
</div>

<div class="pedido-container">
    <!-- Info Cards Grid -->
    <div class="info-cards">
        <div class="info-card">
            <i class="ri-store-2-line"></i>
            <div>
                <label>Restaurante</label>
                <span><?= htmlspecialchars($order['restaurant_name']) ?></span>
            </div>
        </div>
        <div class="info-card">
            <i class="ri-map-pin-2-line"></i>
            <div>
                <label>Área</label>
                <span><?= htmlspecialchars($order['area']) ?></span>
            </div>
        </div>
        <div class="info-card">
            <i class="ri-restaurant-line"></i>
            <div>
                <label>Mesa</label>
                <span><?= htmlspecialchars($order['mesa']) ?></span>
            </div>
        </div>
        <div class="info-card">
            <i class="ri-time-line"></i>
            <div>
                <label>Fecha</label>
                <span><?= htmlspecialchars($order['created_at']) ?></span>
            </div>
        </div>
    </div>

    <!-- Estado y Pago -->
    <div class="status-section">
        <div class="payment-status">
            <i class="ri-bank-card-line"></i>
            <?= $paid ? 
                "<span class='badge-paid'><i class='ri-checkbox-circle-line'></i> Pagado</span>" : 
                "<span class='badge-unpaid'><i class='ri-time-line'></i> Pendiente</span>" 
            ?>
        </div>

    <div class="productos-section">
        <h3>Items del Pedido</h3>
        
        <?php if(empty($items)): ?>
            <p class="no-items">No hay items registrados para este pedido.</p>
        <?php else: ?>
            <div class="productos-lista">
                <?php foreach($items as $i): ?>
                    <div class="producto-item">
                        <div class="producto-info">
                            <span class="producto-cantidad"><?= htmlspecialchars($i['cantidad']) ?></span>
                            <span class="producto-nombre"><?= htmlspecialchars($i['nombre_platillo']) ?></span>
                        </div>
                        <div class="producto-precio">
                            $<?= number_format((float)$i['cantidad'] * (float)$i['precio'],0,',','.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="total-section">
                <span class="total-label">Total Final</span>
                <span class="total-amount">$<?= number_format($order['total_pedido'],0,',','.') ?></span>
            </div>
        <?php endif; ?>

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

    <!-- Barra de Progreso del Estado -->
    <div class="order-progress">
        <h3>Estado del Pedido</h3>
        <div class="progress-bar">
            <?php
            $estados = ['Pending', 'Accepted', 'In_progress', 'Ready', 'Delivered'];
            $currentIdx = array_search($estadoActual, $estados);
            foreach ($estados as $idx => $estado):
                $isActive = $idx <= $currentIdx;
                $icon = match($estado) {
                    'Pending' => 'ri-timer-line',
                    'Accepted' => 'ri-check-line',
                    'In_progress' => 'ri-loader-4-line',
                    'Ready' => 'ri-restaurant-2-line',
                    'Delivered' => 'ri-flag-line',
                    default => 'ri-circle-line'
                };
            ?>
            <div class="progress-step <?= $isActive ? 'active' : '' ?>">
                <div class="step-icon">
                    <i class="<?= $icon ?>"></i>
                </div>
                <span class="step-label"><?= str_replace('_', ' ', $estado) ?></span>
            </div>
            <?php if ($idx < count($estados) - 1): ?>
                <div class="progress-line <?= $idx < $currentIdx ? 'active' : '' ?>"></div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if($siguienteEstado): ?>
        <form action="../php/cambiar_estado.php" method="POST" class="estado-form">
            <input type="hidden" name="pedido_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="nuevo_estado" value="<?= $siguienteEstado ?>">
            <button type="submit" class="btn-next-state">
                <i class="ri-arrow-right-line"></i>
                Avanzar a <?= ucfirst(str_replace("_"," ",$siguienteEstado)) ?>
            </button>
        </form>
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
