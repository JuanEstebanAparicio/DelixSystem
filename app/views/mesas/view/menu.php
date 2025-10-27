<?php
// DelixSystem/app/views/mesas/view/menu.php
include __DIR__ . '/../../../config/supabase.php';

$id_mesa = $_GET['id'] ?? null;
if (!$id_mesa) die("No se especificó una mesa válida.");

$stmt = $conexion->prepare("
    SELECT m.id_mesa, m.nombre AS mesa, a.nombre AS area 
    FROM mesas m 
    JOIN areas a ON a.id_area = m.id_area 
    WHERE m.id_mesa = ?
");
$stmt->execute([$id_mesa]);
$mesa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mesa) die("Mesa no encontrada.");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Menú del Restaurante</title>
  <link rel="stylesheet" href="../css/menu.css">
</head>
<body>
 <header>
  <div class="header-info">
    <h1>🍽 Restaurante Delix</h1>
    <p><strong>Área:</strong> <?php echo htmlspecialchars($mesa['area']); ?> | 
       <strong>Mesa:</strong> <?php echo htmlspecialchars($mesa['mesa']); ?></p>
  </div>
  <button id="verCarritoBtn">🛒 Ver Carrito</button>
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
      <button id="cerrarCarrito">Cerrar</button>
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
      <button id="cancelarPago">Cancelar</button>
    </div>
  </div>

  <script>
    const idMesa = "<?php echo $mesa['id_mesa']; ?>";
  </script>
  <script src="../js/menu.js"></script>
</body>
</html>
