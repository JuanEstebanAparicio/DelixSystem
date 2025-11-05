<?php
// DelixSystem/app/pages/pedidos/view/listar_pedidos.php
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

// traer los pedidos más recientes
$stmt = $conexion->prepare("SELECT id, restaurant_name, area, mesa, total_pedido, metodo_pago, pagado, created_at
    FROM orders
    ORDER BY id DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestor de Pedidos</title>
<link rel="stylesheet" href="../css/listar_pedidos.css">
</head>
<body>

<div class="page-container">

    <h2 class="page-title">Gestión de Pedidos</h2>

    <div class="card-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Restaurante</th>
                    <th>Área</th>
                    <th>Mesa</th>
                    <th>Total</th>
                    <th>Método Pago</th>
                    <th>Pagado</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $o){ ?>
                <tr>
                    <td><?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['restaurant_name']) ?></td>
                    <td><?= htmlspecialchars($o['area']) ?></td>
                    <td><?= htmlspecialchars($o['mesa']) ?></td>
                    <td>$<?= number_format($o['total_pedido'],0,',','.') ?></td>
                    <td><?= htmlspecialchars($o['metodo_pago']) ?></td>
                    <td>
                        <?= $o['pagado'] ? '<span class="badge-paid">Pagado</span>' : '<span class="badge-unpaid">Pendiente</span>' ?>
                    </td>
                    <td><?= $o['created_at'] ?></td>
                    <td>
                        <a class="btn-action" href="ver_pedido.php?id=<?= $o['id'] ?>">Ver</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
