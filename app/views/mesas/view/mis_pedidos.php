<?php
// DelixSystem/app/views/mesas/view/mis_pedidos.php
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

// ================================
// 1️⃣ Verificar si hay cliente en sesión
// ================================
if (!isset($_SESSION['cliente'])) {
    die("<p style='color:red; font-size:18px;'>⚠️ No hay cliente identificado.</p>");
}

$cliente = $_SESSION['cliente']['nombre'];
$id_mesa = $_SESSION['cliente']['id_mesa'];
$id_area = $_SESSION['cliente']['id_area'];

// ================================
// 2️⃣ CONSULTA sin id_user
// ================================
$stmt = $conexion->prepare("
    SELECT id, total_pedido, metodo_pago, pagado, estado, created_at
    FROM orders
    WHERE nombre_cliente = :cliente
      AND id_mesa = :id_mesa
      AND id_area = :id_area
    ORDER BY id DESC
");

$stmt->execute([
    ':cliente' => $cliente,
    ':id_mesa' => $id_mesa,
    ':id_area' => $id_area
]);

$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis Pedidos</title>
<link rel="stylesheet" href="/DelixSystem/app/views/mesas/css/mis_pedidos.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<h2>📋 Mis Pedidos</h2>
<p><strong>Cliente:</strong> <?= htmlspecialchars($cliente) ?> |
   <strong>Mesa ID:</strong> <?= htmlspecialchars($id_mesa) ?></p>

<?php if (empty($pedidos)): ?>
    <p>No tienes pedidos aún.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Total</th>
            <th>Pago</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pedidos as $p): ?>
        <tr>
            <td>#<?= $p['id'] ?></td>
        <td>$<?= number_format($p['total_pedido'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($p['metodo_pago']) ?></td>
            <td><?= htmlspecialchars($p['estado']) ?></td>
            <td><?= htmlspecialchars($p['created_at']) ?></td>
            <td>
                <?php if (in_array($p['estado'], ['Pending', 'Accepted'])): ?>
                    <button class="cancelar-btn" data-id="<?= $p['id'] ?>">❌ Cancelar</button>
                <?php else: ?>
                    <span style="color:gray;">No disponible</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<script>
document.querySelectorAll('.cancelar-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;

        Swal.fire({
            title: '¿Cancelar pedido?',
            text: 'Una vez cancelado no podrás revertirlo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No',
        }).then(result => {
            if (result.isConfirmed) {
                fetch('/DelixSystem/app/pages/pedidos/php/cancelar_pedido.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `pedido_id=${id}`
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Cancelado', data.mensaje, 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.error || 'No se pudo cancelar el pedido', 'error');
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>
