<?php
// DelixSystem/app/pages/pedidos/view/listar_pedidos.php
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

// (opcional) intentar usar modelo de Areas si existe
$areas = [];
$userId = $_SESSION['usuario']['id'] ?? null;
$modelPath = __DIR__ . '/../../../models/AreaModel.php';
if ($userId && file_exists($modelPath)) {
    require_once $modelPath;
    try {
        $areaModel = new AreaModel($conexion);
        $areasRaw = $areaModel->obtenerAreasAdaptable($userId, $conexion);
        // normalizamos a array simple de nombres
        foreach ($areasRaw as $a) {
            if (isset($a['nombre'])) $areas[] = $a['nombre'];
            elseif (isset($a['name'])) $areas[] = $a['name'];
            elseif (isset($a['nombre_area'])) $areas[] = $a['nombre_area'];
        }
    } catch (Throwable $e) {
        // fallback abajo
        $areas = [];
    }
}

// fallback: si no obtuvimos areas por el modelo, sacarlas desde orders (DISTINCT)
if (empty($areas)) {
    try {
        $stmtAreas = $conexion->prepare("SELECT DISTINCT area FROM orders WHERE area IS NOT NULL ORDER BY area ASC");
        $stmtAreas->execute();
        $areas = $stmtAreas->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        $areas = [];
    }
}

// traer los pedidos más recientes
$stmt = $conexion->prepare("SELECT id, restaurant_name, area, id_area, mesa, total_pedido, metodo_pago, pagado, created_at
    FROM orders
    ORDER BY id DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si la petición es fetch=1 devolvemos sólo el grid (para polling AJAX)
if (isset($_GET['fetch']) && $_GET['fetch'] == "1") {
    // render only the orders-grid markup
    if (empty($orders)) {
        echo '<p class="no-orders">No hay pedidos todavía.</p>';
        exit;
    }

    foreach ($orders as $o) {
        $areaAttr = htmlspecialchars(strtolower($o['area'] ?? ''));
        $paid = ((int)$o['pagado'] === 1);
        $cardClass = $paid ? 'order-card' : 'order-card pending';
        $created = htmlspecialchars($o['created_at'] ?? '');
        $restaurant = htmlspecialchars($o['restaurant_name'] ?? '');
        $mesa = htmlspecialchars($o['mesa'] ?? '');
        $total = number_format($o['total_pedido'] ?? 0,0,',','.');
        $metodo = htmlspecialchars($o['metodo_pago'] ?? '');
        $id = htmlspecialchars($o['id']);
        $badge = $paid ? '<span class="badge-paid">Pagado</span>' : '<span class="badge-unpaid">Pendiente</span>';

        echo '<article class="' . $cardClass . '" data-area="' . $areaAttr . '">';
        echo '<div class="order-id">#' . $id . '</div>';
        echo '<div class="restaurant">' . $restaurant . '</div>';
        echo '<div class="meta">Área: ' . htmlspecialchars($o['area'] ?? '') . ' | Mesa: ' . $mesa . '</div>';
        echo '<div class="total">Total: $' . $total . '</div>';
        echo '<div class="method">Pago: ' . $metodo . '</div>';
        echo '<div class="state-row">' . $badge . '</div>';
        echo '<div class="date">' . $created . '</div>';
        echo '<a class="btn-action" href="ver_pedido.php?id=' . urlencode($o['id']) . '">Ver Pedido</a>';
        echo '</article>';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gestor de Pedidos</title>
<link rel="stylesheet" href="../css/listar_pedidos.css">
</head>
<body>

<div class="page-container">

    <h2 class="page-title">Gestión de Pedidos</h2>

    <!-- NAV ÁREAS -->
    <nav class="nav-areas" aria-label="Filtrar por área" style="margin-bottom:18px; display:flex; gap:10px; flex-wrap:wrap;">
        <button class="nav-area active" data-area="all">Todos</button>
        <?php foreach($areas as $a):
            $aClean = trim($a);
            if ($aClean === '') continue;
            $dataArea = strtolower($aClean);
        ?>
            <button class="nav-area" data-area="<?= htmlspecialchars($dataArea) ?>"><?= htmlspecialchars($aClean) ?></button>
        <?php endforeach; ?>
    </nav>

    <div id="ordersGridContainer">
      <div class="orders-grid" role="list">
        <?php if(empty($orders)): ?>
            <p class="no-orders">No hay pedidos todavía.</p>
        <?php else: ?>
            <?php foreach($orders as $o):
                $areaAttr = htmlspecialchars(strtolower($o['area'] ?? ''));
                $paid = ((int)$o['pagado'] === 1);
                $cardClass = $paid ? 'order-card' : 'order-card pending';
            ?>
            <article class="<?= $cardClass ?>" data-area="<?= $areaAttr ?>" role="listitem" aria-labelledby="order-<?= $o['id'] ?>">
                <div class="order-id">#<?= htmlspecialchars($o['id']) ?></div>

                <div id="order-<?= $o['id'] ?>" class="restaurant"><?= htmlspecialchars($o['restaurant_name']) ?></div>
                <div class="meta">Área: <?= htmlspecialchars($o['area']) ?> | Mesa: <?= htmlspecialchars($o['mesa']) ?></div>

                <div class="total">Total: $<?= number_format($o['total_pedido'],0,',','.') ?></div>
                <div class="method">Pago: <?= htmlspecialchars($o['metodo_pago']) ?></div>

                <div class="state-row">
                    <?= $paid ? '<span class="badge-paid">Pagado</span>' : '<span class="badge-unpaid">Pendiente</span>' ?>
                </div>

                <div class="date"><?= htmlspecialchars($o['created_at']) ?></div>

                <a class="btn-action" href="ver_pedido.php?id=<?= urlencode($o['id']) ?>">Ver Pedido</a>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

</div>

<!-- JS -->
<script src="../js/pedidos.js" defer></script>
</body>
</html>
