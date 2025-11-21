<?php
// DelixSystem/app/pages/pedidos/php/ver_pedido.php
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

if (!isset($_GET['id'])) {
    echo "Error: Pedido no especificado.";
    exit;
}

$id = intval($_GET['id']);

$stmt = $conexion->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->execute([":id" => $id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<p>Pedido no encontrado.</p>";
    exit;
}

$stmt2 = $conexion->prepare("SELECT * FROM order_items WHERE order_id = :id");
$stmt2->execute([":id" => $id]);
$items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$estadoActual = $order['estado'];
$flow = [
    "Pending" => "Accepted",
    "Accepted" => "In_progress",
    "In_progress" => "Ready",
    "Ready" => "Delivered"
];
$siguienteEstado = $flow[$estadoActual] ?? null;

ob_start();
?>

<h2>Pedido #<?= htmlspecialchars($order['id']) ?></h2>

<p><strong>Restaurante:</strong> <?= htmlspecialchars($order['restaurant_name']) ?></p>
<p><strong>Área:</strong> <?= htmlspecialchars($order['area']) ?></p>
<p><strong>Mesa:</strong> <?= htmlspecialchars($order['mesa']) ?></p>
<p><strong>Fecha:</strong> <?= htmlspecialchars($order['created_at']) ?></p>

<h3 class="mt-4">Items del Pedido</h3>

<?php if(empty($items)): ?>
<p>No hay items.</p>
<?php else: ?>
<table><tbody>
<?php foreach($items as $i): ?>
<tr>
    <td><?= htmlspecialchars($i['nombre_platillo']) ?></td>
    <td>$<?= number_format($i['precio'],0,',','.') ?></td>
    <td><?= htmlspecialchars($i['cantidad']) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>

<h3>Total Final: $<?= number_format($order['total_pedido'],0,',','.') ?></h3>

<div class="mt-4">

<h3>Estado actual: <?= ucfirst(str_replace("_"," ",$estadoActual)) ?></h3>

<?php if($siguienteEstado): ?>

<form action="../php/cambiar_estado.php" method="POST">
    <input type="hidden" name="pedido_id" value="<?= $order['id'] ?>">
    <input type="hidden" name="nuevo_estado" value="<?= $siguienteEstado ?>">

    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg mt-3">
        Cambiar a <?= ucfirst(str_replace("_"," ",$siguienteEstado)) ?>
    </button>
</form>

<?php else: ?>
<p class="text-green-600 mt-2">✔ Pedido finalizado</p>
<?php endif; ?>

</div>

<?php
echo ob_get_clean();
