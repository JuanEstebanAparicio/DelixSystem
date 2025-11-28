<?php
session_start();

// ==========================
// 🔐 UNIVERSAL GUARD
// ==========================
require_once __DIR__ . '/../../../middleware/universal_guard.php';
$usuario = universalGuard();

// || IMPORTANTE ||
// Si es propietario → su propio ID
// Si es empleado → debemos buscar el propietario por restaurant_name
if ($usuario['tipo'] === 'propietario') {

    $userId = $usuario['id'];

} elseif ($usuario['tipo'] === 'empleado') {

    // El login de empleados guarda restaurant_name
    $restaurantName = $_SESSION['empleado_auth']['restaurant_name'] ?? null;

    if (!$restaurantName) {
        echo '<p class="error">No se pudo determinar el restaurante del empleado.</p>';
        exit;
    }

    // Buscar dueño real del restaurante
    $rest = supabaseRest(
        'usuarios',
        'GET',
        null,
        '?restaurant_name=eq.' . urlencode($restaurantName)
    );

    if (!$rest || empty($rest['data'])) {
        echo '<p class="error">No se encontró el propietario del restaurante.</p>';
        exit;
    }

    // ID REAL DEL DUEÑO
    $userId = $rest['data'][0]['id'];

} else {
    echo '<p class="error">No estás autenticado. Por favor, inicia sesión.</p>';
    exit;
}

// Validación final
if (!$userId) {
    echo '<p class="error">No estás autenticado. Por favor, inicia sesión.</p>';
    exit;
}

// ==========================
// 🔗 CONEXIÓN SUPABASE
// ==========================
require_once __DIR__ . '/../../../config/supabase.php';

error_log("ID de Usuario/Dueño asignado para pedidos: " . $userId);


// ==========================
// 📌 OBTENER ÁREAS
// ==========================
$areas = [];
$modelPath = __DIR__ . '/../../../models/AreaModel.php';

if ($userId && file_exists($modelPath)) {
    require_once $modelPath;

    try {
        $areaModel = new AreaModel($conexion);

        error_log("Consultando áreas desde AreaModel para usuario: $userId");

        $areasRaw = $areaModel->obtenerAreasAdaptable($userId, $conexion);

        error_log("Áreas crudas: " . print_r($areasRaw, true));

        foreach ($areasRaw as $a) {
            if (isset($a['nombre'])) $areas[] = $a['nombre'];
            elseif (isset($a['name'])) $areas[] = $a['name'];
            elseif (isset($a['nombre_area'])) $areas[] = $a['nombre_area'];
        }
    } catch (Throwable $e) {
        error_log("Error obteniendo áreas desde modelo: " . $e->getMessage());
    }
}

// fallback si no hay resultado
if (empty($areas)) {
    try {
        error_log("Fallback: buscando áreas directamente en BD.");

        $stmtAreas = $conexion->prepare("
            SELECT DISTINCT nombre FROM areas 
            WHERE id_usuario = :user_id 
              AND nombre IS NOT NULL 
            ORDER BY nombre ASC
        ");
        $stmtAreas->execute(['user_id' => $userId]);
        $areas = $stmtAreas->fetchAll(PDO::FETCH_COLUMN);

        error_log("Áreas obtenidas por fallback: " . print_r($areas, true));
    } catch (Throwable $e) {
        $areas = [];
        error_log("Error fallback áreas: " . $e->getMessage());
    }
}

error_log("Áreas finales: " . print_r($areas, true));


// ==========================
// 📦 OBTENER PEDIDOS
// ==========================
$stmt = $conexion->prepare("
    SELECT id, restaurant_name, area, id_area, mesa, total_pedido, metodo_pago, pagado, estado, created_at
    FROM orders
    WHERE id_user = :user_id 
      AND estado != 'Delivered'
    ORDER BY id DESC
");

$stmt->execute(['user_id' => $userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================
// 🔄 FETCH AJAX (ACTUALIZACIÓN AUTOMÁTICA)
// ==========================
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

        echo '<article class="' . $cardClass . '" data-area="' . $areaAttr . '" data-order-id="' . $id . '" aria-label="Pedido #' . $id . '">';
        echo '<div class="order-id">#' . $id . '</div>';
        echo '<div class="restaurant">' . $restaurant . '</div>';
        echo '<div class="meta">Área: ' . htmlspecialchars($o['area'] ?? '') . ' | Mesa: ' . $mesa . '</div>';
        echo '<div class="total">Total: $' . $total . '</div>';
        echo '<div class="method">Pago: ' . $metodo . '</div>';
        echo '<div class="state-row">' . $badgeEstado . ' &nbsp;|&nbsp; ' . $badgePago . '</div>';
        echo '<div class="date">' . $created . '</div>';
        echo '<button class="btn-action verPedidoBtn" data-id="' . htmlspecialchars($o['id']) . '">Ver Pedido</button>';
        echo '</article>';
    }
    exit;
}


// ==========================
// 🟦 CONTROL CENTER (SOLO PROPIETARIO)
// ==========================
include __DIR__ . '/../../../components/control_center_propietario.php';

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
<body class="bg-gray-50 min-h-screen font-sans text-gray-800 px-6">
<header class="cc-header">

  <div class="cc-left">
    <div class="cc-logo">
      <i class="ri-restaurant-2-line"></i>
    </div>

    <div class="cc-title">
      <h1>Gestor de pedidos</h1>
      <span>Panel de control</span>
    </div>
  </div>

  <div class="cc-right">
    <a href="../../../../../DelixSystem/app/pages/dashboard_propietario/view/index.php" class="cc-btn-back">
      <i class="ri-arrow-left-line"></i>
      Volver al Dashboard
    </a>
  </div>

</header>
<div class="page-container">
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

                <button class="btn-action verPedidoBtn" data-id="<?= htmlspecialchars($o['id']) ?>">
    Ver Pedido
</button>

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


<!-- MODAL VER PEDIDO -->
<div id="modalVerPedido"
     class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-[9999]">

    <div class="bg-white rounded-xl shadow-xl w-[90%] max-w-3xl p-6 relative">
        
        <!-- Cerrar -->
        <button id="cerrarModalPedido" 
            class="absolute top-3 right-3 bg-gray-200 hover:bg-gray-300 rounded-full p-2">
            ✕
        </button>

        <!-- CONTENIDO DINÁMICO -->
        <div id="modalPedidoContenido">
            <p class="text-center text-gray-500">Cargando...</p>
        </div>

    </div>
</div>

</body>
</html>