<?php
// DelixSystem/app/views/mesas/view/menu.php
session_start();

include __DIR__ . '/../../../config/supabase.php';

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
  <link rel="stylesheet" href="../css/menu.css">
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

<body>
<header>
  <div class="header-info">
    <h1>🍽 <strong>DELIX SYSTEM</strong></h1>
    <p>
      <strong>Área:</strong> <?php echo htmlspecialchars($mesa['area']); ?> |
      <strong>Mesa:</strong> <?php echo htmlspecialchars($mesa['mesa']); ?>
    </p>
  </div>
  <button id="verCarritoBtn">🛒</button>
</header>

<main>
  <h2>Ofertas del Día</h2>
  <div class="menu-section">
    <div class="item">
      <img src="https://cdn-icons-png.flaticon.com/512/3075/3075977.png" alt="Hamburguesa">
      <h3>Hamburguesa Doble</h3>
      <p>$18.000</p>
      <button class="add-btn" data-nombre="Hamburguesa Doble" data-precio="18000">Agregar</button>
    </div>
    <div class="item">
      <img src="https://cdn-icons-png.flaticon.com/512/3075/3075975.png" alt="Pizza">
      <h3>Pizza Personal</h3>
      <p>$20.000</p>
      <button class="add-btn" data-nombre="Pizza Personal" data-precio="20000">Agregar</button>
    </div>
  </div>

  <h2>Menú Regular</h2>
  <div class="menu-section">
    <div class="item">
      <img src="https://cdn-icons-png.flaticon.com/512/1046/1046784.png" alt="Pasta">
      <h3>Pasta Carbonara</h3>
      <p>$22.000</p>
      <button class="add-btn" data-nombre="Pasta Carbonara" data-precio="22000">Agregar</button>
    </div>
    <div class="item">
      <img src="https://cdn-icons-png.flaticon.com/512/3075/3075979.png" alt="Bebida">
      <h3>Jugo Natural</h3>
      <p>$6.000</p>
      <button class="add-btn" data-nombre="Jugo Natural" data-precio="6000">Agregar</button>
    </div>
  </div>
</main>

<!-- Modal Carrito -->
<div id="carritoModal" class="modal">
  <div class="modal-content">
    <h2>🛒 Tu Pedido</h2>
    <ul id="carritoLista"></ul>
    <p><strong>Total: </strong>$<span id="totalCarrito">0</span></p>
    <button id="pagarBtn">Proceder al Pago</button>
    <button id="cerrarCarrito" class="secundario">Cerrar</button>
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

<script>
  const idMesa = "<?php echo $mesa['id_mesa']; ?>";
</script>
<script src="../js/menu.js"></script>
</body>
</html>
