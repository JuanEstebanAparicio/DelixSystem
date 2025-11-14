<?php
// DelixSystem/app/views/mesas/view/menu.php
session_start();

include __DIR__ . '/../../../config/supabase.php';


$id_user = $_GET['u'] ?? null;

if(!$id_user){
    die("Falta owner");
}

// traer platillos activos
// traer platillos activos directo BD local (temporal)
$stmtDish = $conexion->prepare("SELECT id, name_dish, price, photo, description, category 
                                FROM dish 
                                WHERE id_user = :id_user AND state = 'Activo'");

$stmtDish->bindParam(":id_user", $id_user, PDO::PARAM_INT);
$stmtDish->execute();
$platillos = $stmtDish->fetchAll(PDO::FETCH_ASSOC);


// nombre default (evita warning)
$restaurant_name = "Restaurante";

// Si hay sesión usuario normal (cliente, no login propietario)
if (!isset($_SESSION['cliente'])) {
    $requiere_cliente_login = true;
} else {
    $requiere_cliente_login = false;
}

$id_mesa = $_GET['id'] ?? null;
if (!$id_mesa) die("⚠️ No se especificó una mesa válida.");

// ==== INTENTO 2 / RESOLUCIÓN RESTAURANT NAME ====
try {

    // Traemos mesa + area + restaurant_name
    $q = "SELECT m.id_mesa, m.nombre AS mesa, m.id_area, 
                 a.nombre AS area, a.restaurant_name
          FROM mesas m
          LEFT JOIN areas a ON a.id_area = m.id_area
          WHERE m.id_mesa = :id_mesa
          LIMIT 1";
    $sth = $conexion->prepare($q);
    $sth->bindParam(':id_mesa', $id_mesa, PDO::PARAM_INT);
    $sth->execute();
    $mesa = $sth->fetch(PDO::FETCH_ASSOC);

    if (!$mesa) {
        die("Mesa no encontrada.");
    }

    // si existe restaurant_name en areas lo tomamos
    if (!empty($mesa['restaurant_name'])) {
        $restaurant_name = $mesa['restaurant_name'];
    } else {
        // intento alternativo
        $trySql = "
            SELECT COALESCE(r.nombre, u.restaurant_name, u.nombre_restaurante) AS restaurant_name
            FROM mesas m
            LEFT JOIN areas a ON a.id_area = m.id_area
            LEFT JOIN restaurantes r ON r.id_restaurante = a.id_restaurante
            LEFT JOIN usuarios u ON u.id_usuario = a.id_usuario OR u.id_usuario = m.id_usuario
            WHERE m.id_mesa = :id_mesa
            LIMIT 1
        ";
        $sth2 = $conexion->prepare($trySql);
        $sth2->bindParam(':id_mesa', $id_mesa, PDO::PARAM_INT);
        $sth2->execute();
        $r2 = $sth2->fetch(PDO::FETCH_ASSOC);
        if ($r2 && !empty($r2['restaurant_name'])) {
            $restaurant_name = $r2['restaurant_name'];
        }
    }

} catch (Throwable $e) {
    error_log("Error obtener restaurant_name: " . $e->getMessage());
    die("Error interno obteniendo datos de mesa.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- ✅ Compatible con móviles -->
  <title><?php echo htmlspecialchars($restaurant_name); ?> — Menú</title>
 <link rel="stylesheet" href="/DelixSystem/app/views/mesas/css/menu.css">
</head>

<?php if ($requiere_cliente_login): ?>
<div id="clienteLoginModal" class="modal" style="display:block;">
  <div class="modal-content">
    <h2>Identifica tu pedido</h2>
    <p><strong>Restaurante:</strong> <?php echo htmlspecialchars($restaurant_name); ?></p>
    <p><strong>Área:</strong> <?php echo htmlspecialchars($mesa['area']); ?></p>
    <p><strong>Mesa:</strong> <?php echo htmlspecialchars($mesa['mesa']); ?></p>

    <input type="text" id="nombreCliente" placeholder="Tu nombre / Alias">

    <button id="guardarClienteBtn">Continuar</button>
  </div>
</div>
<?php endif; ?>

<body
    data-id_user="<?= htmlspecialchars($id_user) ?>"
    data-restaurant_name="<?= htmlspecialchars($restaurant_name) ?>"
    data-id_area="<?= htmlspecialchars($mesa['id_area']) ?>"
    data-area="<?= htmlspecialchars($mesa['area']) ?>"
    data-id_mesa="<?= htmlspecialchars($mesa['id_mesa']) ?>"
    data-mesa="<?= htmlspecialchars($mesa['mesa']) ?>"
>


<header>
  <div class="header-info">
    <h1>🍽 <strong>Restaurante <?php echo htmlspecialchars($restaurant_name); ?></strong></h1>
    <p>
      <strong>Área:</strong> <?php echo htmlspecialchars($mesa['area']); ?> |
      <strong>Mesa:</strong> <?php echo htmlspecialchars($mesa['mesa']); ?>
    </p>
    <p id="nombreClienteHeader" style="font-style:italic; color:#555;"></p>
  </div>
  <button id="verCarritoBtn">
    🛒 <span id="cartCount" style="background:red;color:white;padding:2px 6px;border-radius:12px;font-size:12px;position:absolute;margin-left:4px;top:6px;right:10px;">0</span>
  </button>
<button id="verPedidosBtn" <?= empty($_SESSION['cliente']) ? "disabled" : "" ?>>
    Ver mis pedidos
</button>


</header>

<?php
// armamos categorias desde el arreglo ya existente $platillos
$categoriasMenu = [];
foreach($platillos as $p){
    if(!isset($categoriasMenu[$p['category']])){
        $categoriasMenu[$p['category']] = [];
    }
    $categoriasMenu[$p['category']][] = $p;
}
?>

<nav class="menu-nav">
    <button class="nav-cat" data-cat="all">Todos</button>
    <?php foreach(array_keys($categoriasMenu) as $c): ?>
        <button class="nav-cat" data-cat="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></button>
    <?php endforeach; ?>
</nav>


<main class="menu-container">
  
<div id="contenedorPlatillos">

<?php foreach($platillos as $p): ?>

        <div class="platillo-card" data-cat="<?= htmlspecialchars($p['category']) ?>">
        
       <div class="img-box" 
     onclick="openDishModal(
         '<?= htmlspecialchars(addslashes($p['name_dish'])) ?>',
         '<?= htmlspecialchars(addslashes($p['description'])) ?>',
         '<?= htmlspecialchars($p['photo']) ?>',
         '<?= $p['price'] ?>',
         '<?= $p['id'] ?>'
     )">

    <img src="<?= htmlspecialchars($p['photo']) ?>" 
         alt="<?= htmlspecialchars($p['name_dish']) ?>">
</div>


        <div class="info-box">
    <h3><?= htmlspecialchars($p['name_dish']) ?></h3>

    <?php if (!empty($p['description'])): ?>
        <p class="dish-desc"><?= htmlspecialchars($p['description']) ?></p>
    <?php endif; ?>

    <p class="price">$<?= number_format($p['price'], 0, ',', '.') ?></p>

    <button class="add-btn"
        data-id="<?= $p['id'] ?>"
        data-nombre="<?= htmlspecialchars($p['name_dish']) ?>"
        data-precio="<?= $p['price'] ?>"
        data-descripcion="<?= htmlspecialchars($p['description'] ?? '') ?>"
    >Agregar al carrito</button>
</div>


    </div>
<?php endforeach; ?>

</div>

</main>


<!-- Modal Carrito -->
<div id="carritoModal" class="modal">
  <div class="carrito-sheet">
     <div class="cart-top">
        <h2>🛒 Tu Pedido</h2>
        <span id="cerrarCarrito" class="close-x">✕</span>
     </div>

     <ul id="carritoLista" class="cart-items"></ul>

     <div class="cart-footer">
        <p><strong>Total:</strong> $<span id="totalCarrito">0</span></p>
        <button id="pagarBtn" class="btn-pay">Proceder al Pago</button>
     </div>
  </div>
</div>

<!-- Bottom Sheet pago Premium -->
<div id="bottomSheetPago" class="bottom-sheet">
    <div class="bs-header">
        <div class="bs-handle"></div>
        <h3>Selecciona método de pago</h3>
    </div>

    <div class="bs-options">

        <div class="bs-item" data-metodo="tarjeta">
            <span class="bs-icon">💳</span>
            <span>Tarjeta</span>
        </div>

        <div class="bs-item" data-metodo="efectivo">
            <span class="bs-icon">💵</span>
            <span>Efectivo</span>
        </div>

        <div class="bs-item" data-metodo="nequi">
            <span class="bs-icon">📱</span>
            <span>Nequi</span>
        </div>

    </div>
</div>


<!-- Modal Pago -->
<div id="pagoModal" class="modal">
  <div class="modal-content">
    <h2>💳 Simulación de Pago</h2>
    <p>Método de pago:</p>
    <select id="metodoPago">
      <option value="tarjeta">Tarjeta</option>
      <option value="efectivo">Efectivo</option>
    </select>
    <div id="tarjetaInfo">
      <input type="text" placeholder="Número de Tarjeta" maxlength="16">
      <input type="text" placeholder="CVV" maxlength="3">
    </div>
    <button id="confirmarPagoBtn">Confirmar Pago</button>
    <button id="cancelarPago" class="secundario">Cancelar</button>
  </div>
</div>

<!-- Modal Tarjeta Simulada -->
<div id="simulacionTarjetaModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h2>💳 Pagar con Tarjeta</h2>
    <p>Simulación de Google Pay / Apple Pay</p>
    <div style="margin: 20px 0; padding: 20px; border: 1px solid #ccc; border-radius: 10px; text-align:center;">
      <p style="font-weight:bold;">💳 Tarjeta</p>
      <p>Número: **** **** **** 1234</p>
      <p>Total a pagar: $<span id="totalTarjeta"></span></p>
    </div>
    <button id="confirmarPagoTarjetaBtn" class="btn-pay">Pagar</button>
    <button id="cancelarPagoTarjeta" class="secundario">Cancelar</button>
  </div>
</div>

<!-- Modal PLATO -->
<div id="dishModal" class="modal">
  <div class="modal-content dish-modal-content">
    <span class="close-x" onclick="closeDishModal()">✕</span>

    <img id="dishModalImg" class="dish-big-img" src="" alt="">

    <h2 id="dishModalName"></h2>
    <p id="dishModalDesc" style="font-style:italic;color:#333;"></p>
    <p style="font-weight:bold;font-size:18px;">$<span id="dishModalPrice"></span></p>

    <button id="dishModalAdd" class="add-btn" data-id="" data-nombre="" data-precio="">
        
    </button>
  </div>
</div>



<script>
  const idMesa = "<?php echo $mesa['id_mesa']; ?>";
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/DelixSystem/app/views/mesas/js/menu.js"></script>


</body>
</html>
