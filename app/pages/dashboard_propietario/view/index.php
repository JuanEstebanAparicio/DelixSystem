<!-- -- DelixSystem/app/pages/dashboard_propietario/view/index.php -->
<?php
require_once __DIR__ . '/../../../middleware/session_guard.php';
protectPage('propietario'); // Evita que accedan al dashboard sin login

// Iniciamos sesión solo si no está activa (por seguridad extra)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//🪑 Obtenemos el nombre del usuario desde la sesión
$nombreUsuario = $_SESSION['usuario']['nombre'] ?? 'Usuario';
$nombreRestaurante = $_SESSION['usuario']['restaurant_name'] ?? 'MiRestaurante';

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Restaurante</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

  <aside class="sidebar">
  <h2>
    <span class="logo-full">🍴 <?= htmlspecialchars($nombreRestaurante) ?></span>
    <span class="logo-mini">DELIX</span>
  </h2>
  <ul>
    <li class="active"><i>🏠</i><span>Dashboard</span></li>
    <li><i>🧾</i><span>Pedidos</span></li>
    <li><i>🍔</i><span>Menú</span></li>
  <a href="../../gestion_mesas/view/resumen_mesas.php" class="menu-link">
    <i class="fa-solid fa-chair">🪑</i> <span>Mesas</span>
  </a>
</li>
    <li><i>👥</i><span>Clientes</span></li>
    <li><i>📊</i><span>Reportes</span></li>
    <li><i>⚙️</i><span>Configuración</span></li>
    <a href="../../gestor_empleado/view/gestor_empleados.php">
    <li><i>👨‍💼</i><span>Admin</span></li>
    </a>

  </ul>
  </aside>

  <div id="transitionOverlay" style="
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(255,255,255,0.9);
  z-index: 9999;
  justify-content: center;
  align-items: center;
  font-size: 1.5rem;
  color: #333;
  font-weight: bold;
">
  Cargando...
</div>

  <main class="main">
   <header>
  <button id="toggleSidebar" class="toggle-btn">☰</button>
  <h1>Panel de Control</h1>
  <div class="user-info">
    <img src="https://cdn-icons-png.flaticon.com/512/2202/2202112.png" alt="Usuario" id="openProfileModal">
    <span><?= htmlspecialchars($_SESSION['usuario']['first_name'] ?? 'Usuario') ?></span>
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
    <!-- Modal Editar Perfil -->
<div id="profileModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Editar Perfil</h2>
  <form id="profileForm" method="POST" action="../php/profile.php">
  <div class="form-group">
    <label for="first_name">Nombre:</label>
    <input type="text" id="first_name" name="first_name" 
           value="<?= htmlspecialchars($_SESSION['usuario']['first_name'] ?? '') ?>" required>
  </div>

  <div class="form-group">
    <label for="last_name">Apellido:</label>
    <input type="text" id="last_name" name="last_name" 
           value="<?= htmlspecialchars($_SESSION['usuario']['last_name'] ?? '') ?>" required>
  </div>

  <div class="form-group">
    <label for="email">Correo:</label>
    <input type="email" id="email" name="email" 
           value="<?= htmlspecialchars($_SESSION['usuario']['email'] ?? '') ?>" required>
  </div>

  <div class="form-group">
    <label for="restaurant_name">Restaurante:</label>
    <input type="text" id="restaurant_name" name="restaurant_name" 
           value="<?= htmlspecialchars($_SESSION['usuario']['restaurant_name'] ?? '') ?>" required>
  </div>

  <button type="submit" class="btn-save">Guardar Cambios</button>
</form>
  </div>
</div>

  </main>



  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/script.js"></script>
</body>
</html>