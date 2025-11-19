<?php
// DelixSystem/app/pages/pedidos/view/listar_pedidos.php
session_start();
require_once __DIR__ . '/../../../middleware/session_guard.php';
protectPage('propietario');

// ✅ Incluimos el Control Center
include __DIR__ . '/../../../components/header_propietario.php'; 
include __DIR__ . '/../../../components/control_center_propietario.php'; 
require_once __DIR__ . '/../../../config/supabase.php';

// Aseguramos que el usuario esté logueado y obtenemos su ID
$userId = $_SESSION['usuario']['id'] ?? null;
if (!$userId) {
    echo '<p class="error">No estás autenticado. Por favor, inicia sesión.</p>';
    exit;
}

// Log: Verificar el ID de usuario
error_log("ID de Usuario: " . $userId);

// Traemos las áreas del usuario logueado, solo aquellas asociadas a su `id_usuario`
$areas = [];
$modelPath = __DIR__ . '/../../../models/AreaModel.php';
if ($userId && file_exists($modelPath)) {
    require_once $modelPath;
    try {
        $areaModel = new AreaModel($conexion);

        // Log: Verificar la ejecución de la consulta para obtener áreas
        error_log("Consultando áreas para el ID de usuario: " . $userId);
        
        // Filtramos las áreas por `id_usuario`
        $areasRaw = $areaModel->obtenerAreasAdaptable($userId, $conexion); 
        
        // Log: Verificar qué áreas estamos recuperando
        error_log("Áreas recuperadas (Raw): " . print_r($areasRaw, true));
        
        // Normalizamos las áreas a un array simple de nombres
        foreach ($areasRaw as $a) {
            if (isset($a['nombre'])) $areas[] = $a['nombre'];
            elseif (isset($a['name'])) $areas[] = $a['name'];
            elseif (isset($a['nombre_area'])) $areas[] = $a['nombre_area'];
        }
    } catch (Throwable $e) {
        $areas = [];
        error_log("Error al recuperar las áreas: " . $e->getMessage());
    }
}

// Log: Verificar el resultado de las áreas recuperadas del modelo
error_log("Áreas después de la consulta del modelo: " . print_r($areas, true));

// fallback: si no obtuvimos áreas por el modelo, las obtenemos directamente desde la tabla `areas` (filtrando por `id_usuario`)
if (empty($areas)) {
    try {
        // Aquí usamos `nombre` en lugar de `area`
        error_log("Recuperando áreas desde el fallback (sin modelo)");

        $stmtAreas = $conexion->prepare("SELECT DISTINCT nombre FROM areas WHERE id_usuario = :user_id AND nombre IS NOT NULL ORDER BY nombre ASC");
        $stmtAreas->execute(['user_id' => $userId]);
        $areas = $stmtAreas->fetchAll(PDO::FETCH_COLUMN);

        // Log: Verificar el resultado de las áreas desde el fallback
        error_log("Áreas recuperadas del fallback: " . print_r($areas, true));
    } catch (Throwable $e) {
        $areas = [];
        error_log("Error al recuperar las áreas desde el fallback: " . $e->getMessage());
    }
}


// Log: Verificar las áreas antes de usarlas
error_log("Áreas finales que se van a mostrar: " . print_r($areas, true));

// Traemos los pedidos del usuario actual, más recientes, y con el estado filtrado
$stmt = $conexion->prepare("SELECT id, restaurant_name, area, id_area, mesa, total_pedido, metodo_pago, pagado, estado, created_at
    FROM orders
    WHERE id_user = :user_id AND estado != 'Delivered'
    ORDER BY id DESC");
$stmt->execute(['user_id' => $userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si la petición es fetch=1 devolvemos solo el grid (para polling AJAX)
if (isset($_GET['fetch']) && $_GET['fetch'] == "1") {
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
        $total = number_format($o['total_pedido'] ?? 0, 0, ',', '.');
        $metodo = htmlspecialchars($o['metodo_pago'] ?? '');
        $id = htmlspecialchars($o['id']);

        $estado = htmlspecialchars($o['estado'] ?? 'pending');
        $badgeEstado = '<span class="badge-estado badge-' . $estado . '">' . ucfirst($estado) . '</span>';
        $badgePago = $paid ? '<span class="badge-paid">Pagado</span>' : '<span class="badge-unpaid">No Pagado</span>';

        // Use data-order-id and aria-label instead of adding element IDs (prevents duplicates)
        echo '<article class="' . $cardClass . '" data-area="' . $areaAttr . '" data-order-id="' . $id . '" aria-label="Pedido #' . $id . '">';
        echo '<div class="order-id">#' . $id . '</div>';
        echo '<div class="restaurant">' . $restaurant . '</div>';
        echo '<div class="meta">Área: ' . htmlspecialchars($o['area'] ?? '') . ' | Mesa: ' . $mesa . '</div>';
        echo '<div class="total">Total: $' . $total . '</div>';
        echo '<div class="method">Pago: ' . $metodo . '</div>';
        echo '<div class="state-row">' . $badgeEstado . ' &nbsp;|&nbsp; ' . $badgePago . '</div>';
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
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/DelixSystem/app/shared/css/globals.css">
  <link rel="stylesheet" href="/DelixSystem/app/shared/css/control_center.css">
</head>
<body class="bg-gray-50 min-h-screen font-sans text-gray-800 pt-28 px-6">

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

                // calculos para mostrar badges
                $estado = htmlspecialchars($o['estado'] ?? 'pending');
                $badgeEstado = '<span class="badge-estado badge-' . $estado . '">' . ucfirst($estado) . '</span>';
                $badgePago = $paid ? '<span class="badge-paid">Pagado</span>' : '<span class="badge-unpaid">No Pagado</span>';
                $orderIdEsc = htmlspecialchars($o['id']);
            ?>
            <!-- Use data-order-id and aria-label instead of element IDs to avoid duplicates -->
            <article class="<?= $cardClass ?>" data-area="<?= $areaAttr ?>" role="listitem" data-order-id="<?= $orderIdEsc ?>" aria-label="Pedido #<?= $orderIdEsc ?>">
                <div class="order-id">#<?= $orderIdEsc ?></div>

                <div class="restaurant"><?= htmlspecialchars($o['restaurant_name']) ?></div>
                <div class="meta">Área: <?= htmlspecialchars($o['area']) ?> | Mesa: <?= htmlspecialchars($o['mesa']) ?></div>

                <div class="total">Total: $<?= number_format($o['total_pedido'],0,',','.') ?></div>
                <div class="method">Pago: <?= htmlspecialchars($o['metodo_pago']) ?></div>

                <div class="state-row">
                    <?= $badgeEstado ?> &nbsp;|&nbsp; <?= $badgePago ?>
                </div>

                <div class="date"><?= htmlspecialchars($o['created_at']) ?></div>

                <a class="btn-action" href="ver_pedido.php?id=<?= urlencode($o['id']) ?>">Ver Pedido</a>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

</div>
  <!-- JS Global -->
 <script src="/DelixSystem/app/shared/js/control_center_propietario.js"></script>
<!-- JS -->
<script src="../js/pedidos.js" defer></script>

</body>
</html>
