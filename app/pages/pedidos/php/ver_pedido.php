<?php
// DelixSystem/app/pages/pedidos/php/ver_pedido.php
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

if (!isset($_GET['id'])) {
    echo "<p class='text-red-600'>Error: Pedido no especificado.</p>";
    exit;
}

$id = intval($_GET['id']);

// Buscar pedido
$stmt = $conexion->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->execute([":id" => $id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<p class='text-red-600'>Pedido no encontrado.</p>";
    exit;
}

// Buscar ítems del pedido
$stmt2 = $conexion->prepare("SELECT * FROM order_items WHERE order_id = :id");
$stmt2->execute([":id" => $id]);
$items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Estado actual
$estadoActual = $order['estado'];
$esCancelado = strtolower($estadoActual) === 'canceled';

// Flujo normal de estados
$flow = [
    "Pending"     => "Accepted",
    "Accepted"    => "In_progress",
    "In_progress" => "Ready",
    "Ready"       => "Delivered"
];

$siguienteEstado = $flow[$estadoActual] ?? null;

ob_start();
?>

<h2 class="text-xl font-bold mb-2">Pedido #<?= htmlspecialchars($order['id']) ?></h2>

<p><strong>Cliente:</strong> <?= htmlspecialchars($order['nombre_cliente'] ?? 'No registrado') ?></p>
<p><strong>Restaurante:</strong> <?= htmlspecialchars($order['restaurant_name']) ?></p>
<p><strong>Área:</strong> <?= htmlspecialchars($order['area']) ?></p>
<p><strong>Mesa:</strong> <?= htmlspecialchars($order['mesa']) ?></p>
<p><strong>Fecha:</strong> <?= htmlspecialchars($order['created_at']) ?></p>

<h3 class="mt-4 font-semibold">Items del Pedido</h3>

<?php if (empty($items)): ?>
    <p>No hay items.</p>
<?php else: ?>
    <table class="w-full mt-2 border">
        <thead class="bg-gray-100">
        <tr>
            <th class="py-1 px-2 text-left">Platillo</th>
            <th class="py-1 px-2 text-left">Precio</th>
            <th class="py-1 px-2 text-left">Cantidad</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $i): ?>
            <tr class="border-t">
                <td class="py-1 px-2"><?= htmlspecialchars($i['nombre_platillo']) ?></td>
                <td class="py-1 px-2">$<?= number_format($i['precio'],0,',','.') ?></td>
                <td class="py-1 px-2"><?= htmlspecialchars($i['cantidad']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h3 class="mt-4 text-lg">
    Total Final: $<?= number_format($order['total_pedido'], 0, ',', '.') ?>
</h3>

<div class="mt-4">
    <h3><strong>Estado actual:</strong>
        <?= ucfirst(str_replace("_", " ", $estadoActual)) ?>
    </h3>

    <?php if ($siguienteEstado && !$esCancelado): ?>
        <form action="../php/cambiar_estado.php" method="POST" class="mt-3">
            <input type="hidden" name="pedido_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="nuevo_estado" value="<?= $siguienteEstado ?>">

            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Cambiar a <?= ucfirst(str_replace("_"," ",$siguienteEstado)) ?>
            </button>
        </form>

    <?php elseif (!$esCancelado): ?>
        <p class="text-green-600 mt-2">✔ Pedido finalizado</p>
    <?php endif; ?>

    <?php if ($esCancelado): ?>
        <div class="mt-5 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-600 font-semibold mb-2">
                ⚠ Este pedido está cancelado
            </p>

            <form action="../php/cambiar_estado.php" method="POST"
      onsubmit="return confirm('¿Seguro que deseas eliminar este pedido? Esta acción no se puede deshacer?');">

    <input type="hidden" name="pedido_id" value="<?= $order['id'] ?>">
    <input type="hidden" name="accion" value="delete">

    <button type="submit"
        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg mt-4">
        🗑 Eliminar pedido
    </button>

</form>

        </div>
    <?php endif; ?>
</div>

<?php
echo ob_get_clean();
