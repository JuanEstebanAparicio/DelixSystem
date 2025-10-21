<!-- -- DelixSystem/app/pages/dashboard_propietario/view/index.php -->
<?php
require_once __DIR__ . '/../../../middleware/session_guard.php';
protectPage(); // Evita que accedan al dashboard sin login
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Restaurante</title>
  <link rel="stylesheet" href="../css/style.css">
  <script defer src="../js/script.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

  <aside class="sidebar">
    <h2>🍴 MiRestaurante</h2>
    <ul>
      <li class="active"><i>🏠</i> Dashboard</li>
      <li><i>🧾</i> Pedidos</li>
      <li><i>🍔</i> Menú</li>
      <li><i>🪑</i> Mesas</li>
      <li><i>👥</i> Clientes</li>
      <li><i>📊</i> Reportes</li>
      <li><i>⚙️</i> Configuración</li>
    </ul>
  </aside>

  <main class="main">
    <header>
      <h1>Panel de Control</h1>
      <div class="user-info">
        <img src="https://cdn-icons-png.flaticon.com/512/2202/2202112.png" alt="Admin">
        <span>Admin</span>
       <a href="../../../../src/auth/logout.php" class="logout-btn">Cerrar sesión</a>
      </div>
    </header>

    <section class="stats">
      <div class="stat">
        <h3>Pedidos del Día</h3>
        <p>45</p>
      </div>
      <div class="stat">
        <h3>Ventas Totales</h3>
        <p>$1,250.000</p>
      </div>
      <div class="stat">
        <h3>Mesas Ocupadas</h3>
        <p>8 / 12</p>
      </div>
    </section>

    <section class="table-section">
      <h2>Pedidos Recientes</h2>
      <table>
        <thead>
          <tr>
            <th>ID Pedido</th>
            <th>Cliente</th>
            <th>Mesa</th>
            <th>Estado</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>#1021</td>
            <td>Carlos Pérez</td>
            <td>5</td>
            <td><span class="badge pendiente">Pendiente</span></td>
            <td>$45.000</td>
          </tr>
          <tr>
            <td>#1020</td>
            <td>Ana Torres</td>
            <td>2</td>
            <td><span class="badge entregado">Entregado</span></td>
            <td>$72.000</td>
          </tr>
          <tr>
            <td>#1019</td>
            <td>Luis García</td>
            <td>1</td>
            <td><span class="badge entregado">Entregado</span></td>
            <td>$33.000</td>
          </tr>
        </tbody>
      </table>
    </section>
  </main>

</body>
</html>
