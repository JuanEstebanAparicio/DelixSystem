<?php
// DelixSystem/app/views/mesas/view/mis_pedidos.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Si no hay cliente en sesión, intentar reconstruir desde localStorage (vía JS)
if (!isset($_SESSION['cliente'])) {
    echo "
    <script>
        let cliente = localStorage.getItem('nombre_cliente');
        let idMesa = localStorage.getItem('id_mesa');
        let idArea = localStorage.getItem('id_area');
        let mesaNombre = localStorage.getItem('mesa');

        if (cliente && idMesa && idArea) {
            fetch(location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    cliente: cliente,
                    id_mesa: idMesa,
                    id_area: idArea,
                    mesa: mesaNombre,
                    id_user: localStorage.getItem('id_user')
                })
            }).then(() => location.reload());
        }
    </script>
    ";
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
            'mesa' => $json['mesa'],
            'id_user' => $json['id_user']
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
$id_user = $_SESSION['cliente']['id_user'] ?? null;

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

<body 
    data-id_user="<?= $id_user ?? '' ?>" 
    data-id_mesa="<?= $id_mesa ?>"
>
<script>
console.log("🔥 PHP id_user recibido en mis_pedidos.php:", "<?= $id_user ?>");
console.log("🔥 id_user desde atributo BODY:", document.body.getAttribute("data-id_user"));
console.log("🔥 id_user desde localStorage:", localStorage.getItem("id_user"));
</script>

<a class="boton-volver" id="btnVolverMenu" href="#">
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
<div id="modalDetalles" class="modal">
    <div class="modal-contenido">
        <span class="cerrar">&times;</span>

        <h2>Detalles del Pedido</h2>
        <div id="detallesContenido"></div>
    </div>
</div>


<!-- =============================== -->
<!-- SCRIPT INTERNO: VER DETALLES   -->
<!-- =============================== -->
<script>
console.log("🎯 SCRIPT INTERNO SE ESTÁ EJECUTANDO");

document.addEventListener("DOMContentLoaded", () => {
    console.log("🎯 DOM cargado: iniciando mis_pedidos JS.");

    document.querySelectorAll('.detalles-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            console.log("🟢 Click en Ver Detalles, ID:", btn.dataset.id);

            const id = btn.dataset.id;

            fetch('/DelixSystem/app/views/mesas/php/obtener_items.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_pedido=${id}`
            })
            .then(r => r.json())
            .then(data => {
                console.log("📦 Respuesta del servidor:", data);

                if (!data.ok) {
                    Swal.fire('Error', data.error, 'error');
                    return;
                }

                let html = "";
                data.items.forEach(i => {
                    let subtotal = i.precio * i.cantidad;
                    html += `
                        <div class="item-row">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z"/>
                            </svg>

                            <div class="item-info">
                                <strong>${i.nombre_platillo}</strong>
                                <span>${i.cantidad} × $${Intl.NumberFormat('es-CO').format(i.precio)}</span>
                            </div>

                            <div class="item-precio">
                                <strong>$${Intl.NumberFormat('es-CO').format(subtotal)}</strong>
                            </div>
                        </div>`;
                });

                document.getElementById('detallesContenido').innerHTML = html;

                document.getElementById('modalDetalles').classList.add('active');
            });
        });
    });

    document.querySelector('.cerrar').onclick =
        () => document.getElementById('modalDetalles').classList.remove('active');

    window.onclick = (e) => {
        if (e.target.id === 'modalDetalles') {
            document.getElementById('modalDetalles').classList.remove('active');
        }
    };

});
</script>


<!-- =============================== -->
<!-- SCRIPT INTERNO: ARMAR BOTÓN    -->
<!-- =============================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    console.log("🔥 DOM listo en unificador de id_user + mesa");

    let id_user = document.body.getAttribute("data-id_user");
    if (!id_user || id_user.trim() === "") {
        console.warn("⚠️ id_user vacío en PHP, usando localStorage...");
        id_user = localStorage.getItem("id_user");
    }
    console.log("🟢 id_user FINAL:", id_user);

    let id_mesa = document.body.getAttribute("data-id_mesa");
    console.log("🟢 id_mesa FINAL:", id_mesa);

    const volverBtn = document.getElementById("btnVolverMenu");
    if (volverBtn) {
        volverBtn.href =
            `/DelixSystem/app/views/mesas/view/menu.php?id=${id_mesa}&u=${id_user}`;
        console.log("🔗BOTÓN VOLVER Generado:", volverBtn.href);
    }
});
</script>

<script src="/DelixSystem/app/views/mesas/js/mis_pedidos.js"></script>

</body>
</html>
