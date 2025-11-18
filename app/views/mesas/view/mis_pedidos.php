<?php
// DelixSystem/app/views/mesas/view/mis_pedidos.php
session_start();
// Si no hay cliente en sesión, intentar reconstruir desde localStorage (vía JS)
if (!isset($_SESSION['cliente'])) {
    echo "
    <script>
        let cliente = localStorage.getItem('nombre_cliente');
        let idMesa = localStorage.getItem('id_mesa');
        let idArea = localStorage.getItem('id_area');
        let mesaNombre = localStorage.getItem('mesa');

        if (cliente && idMesa && idArea) {
            // reenviar con reconstrucción en PHP vía POST
            fetch(location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    cliente: cliente,
                    id_mesa: idMesa,
                    id_area: idArea,
                    mesa: mesaNombre
                })
            }).then(() => location.reload());
        }
    </script>
    ";

    // detener ejecución (esperamos recarga)
    exit;
}

// Reconstrucción automática si vienen datos desde fetch()
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = json_decode(file_get_contents('php://input'), true);

    if (!empty($json['cliente'])) {
        $_SESSION['cliente'] = [
            'nombre' => $json['cliente'],
            'id_mesa' => $json['id_mesa'],
            'id_area' => $json['id_area'],
            'mesa' => $json['mesa']
        ];
    }

    echo json_encode(['ok' => true]);
    exit;
}


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
$mesa_text = $_SESSION['cliente']['mesa'];

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
<a class="boton-volver" 
   href="/DelixSystem/app/views/mesas/view/menu.php?id=<?= $_SESSION['cliente']['id_mesa'] ?>&u=<?= $_SESSION['usuario']['id'] ?>">
    <svg viewBox="0 0 24 24">
        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
    </svg>
</a>

<h2 class="titulo-pedidos">📋 Mis Pedidos</h2>

<div class="info-cliente">
    <span><strong><?= htmlspecialchars($cliente) ?></strong></span>
    <span class="mesa-pill">Mesa <?= htmlspecialchars($mesa_text) ?></span>
</div>

<?php if (empty($pedidos)): ?>
    <p class="no-pedidos">No tienes pedidos aún.</p>

<?php else: ?>

<div class="pedidos-list">
<?php foreach ($pedidos as $p): ?>

    <div class="pedido-card">
        <div class="pedido-top">
            <span class="pedido-id">Pedido #<?= $p['id'] ?></span>
            <span class="pedido-estado estado-<?= strtolower($p['estado']) ?>">
                <?= htmlspecialchars($p['estado']) ?>
            </span>
        </div>

        <div class="pedido-info">
            <div><strong>Total:</strong> $<?= number_format($p['total_pedido'], 0, ',', '.') ?></div>
            <div><strong>Pago:</strong> <?= htmlspecialchars($p['metodo_pago']) ?></div>
            <div class="pedido-fecha"><?= htmlspecialchars($p['created_at']) ?></div>
        </div>

        <div class="pedido-acciones">
            <?php if (in_array($p['estado'], ['Pending', 'Accepted'])): ?>
                <button class="btn-cancelar cancelar-btn" data-id="<?= $p['id'] ?>">Cancelar</button>
                <button class="btn-detalles detalles-btn" data-id="<?= $p['id'] ?>">Ver detalles</button>
            <?php else: ?>
                <span class="no-disponible">No disponible</span>
            <?php endif; ?>
        </div>
    </div>

<?php endforeach; ?>
</div>
<?php endif; ?>


<!-- MODAL DETALLES SLIDE -->
<div id="modalDetalles" class="modal-slide">
    <div class="modal-box">
        <div class="modal-header">
            <span class="cerrar">&times;</span>
            <h3>Detalles del Pedido</h3>
        </div>

        <div id="detallesContenido" class="items-container">
            Cargando...
        </div>
    </div>
</div>


<script src="/DelixSystem/app/views/mesas/js/mis_pedidos.js"></script>

</body>
</html>
