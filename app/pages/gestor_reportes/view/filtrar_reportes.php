<?php
// filtrar_reportes.php
require_once __DIR__ . '/../../../config/supabase.php';

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$area = $_GET['area'] ?? '';

$query = "SELECT * FROM orders WHERE 1=1";
$params = [];

// === FILTROS ===
if (!empty($fecha_inicio)) {
    $query .= " AND created_at >= :fecha_inicio";
    $params[':fecha_inicio'] = $fecha_inicio;
}
if (!empty($fecha_fin)) {
    $query .= " AND created_at <= :fecha_fin";
    $params[':fecha_fin'] = $fecha_fin;
}
if (!empty($area)) {
    $query .= " AND area = :area";
    $params[':area'] = $area;
}

$query .= " ORDER BY created_at DESC";

$stmt = $conexion->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === SIN RESULTADOS ===
if (!$orders) {
    echo "<p class='no-result'>No hay resultados para los filtros seleccionados.</p>";
    exit;
}
?>

<table class="tabla-reportes">
    <thead>
        <tr>
            <th>ID</th>
            <th>Restaurante</th>
            <th>Área</th>
            <th>Mesa</th>
            <th>Fecha</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= htmlspecialchars($order['id']) ?></td>
                <td><?= htmlspecialchars($order['restaurant_name']) ?></td>
                <td><?= htmlspecialchars($order['area']) ?></td>
                <td><?= htmlspecialchars($order['mesa']) ?></td>
                <td><?= htmlspecialchars($order['created_at']) ?></td>
                <td>$<?= number_format($order['total_pedido'] ?? 0, 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
