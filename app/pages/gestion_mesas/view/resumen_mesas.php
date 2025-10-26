<?php
// --- DelixSystem/app/pages/gestion_mesas/view/resumen_mesas.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../middleware/session_guard.php';
protectPage(); // Aquí ya se inicia la sesión internamente (no la repitas)

require_once __DIR__ . '/../../../config/supabase.php';

// ⚠️ No vuelvas a poner session_start() aquí
// ya está iniciada por session_guard.php

$id_usuario = $_SESSION['usuario']['id'] ?? null;
$nombreUsuario = $_SESSION['usuario']['first_name'] ?? 'Usuario';

if (!$id_usuario) {
  header("Location: /DelixSystem/app/pages/login.php");
  exit;
}


try {
  // Total de áreas
  $stmtAreas = $conexion->prepare("SELECT COUNT(*) AS total FROM areas WHERE id_usuario = ?");
  $stmtAreas->execute([$id_usuario]);
  $totalAreas = $stmtAreas->fetch(PDO::FETCH_ASSOC)['total'];

  // Total de mesas
  $stmtMesas = $conexion->prepare("
    SELECT COUNT(*) AS total 
    FROM mesas m 
    INNER JOIN areas a ON m.id_area = a.id_area 
    WHERE a.id_usuario = ?
  ");
  $stmtMesas->execute([$id_usuario]);
  $totalMesas = $stmtMesas->fetch(PDO::FETCH_ASSOC)['total'];

} catch (Exception $e) {
  $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Resumen de Mesas</title>
  <link rel="stylesheet" href="../../dashboard_propietario/css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

  <!-- 🔹 Sidebar -->
  <aside class="sidebar">
    <h2>
      <span class="logo-full">🍴 MiRestaurante</span>
      <span class="logo-mini">DELIX</span>
    </h2>
    <ul>
      <li><a href="../../dashboard_propietario/view/index.php"><i>🏠</i><span>Dashboard</span></a></li>
      <li><i>🧾</i><span>Pedidos</span></li>
      <li><i>🍔</i><span>Menú</span></li>
      <li class="active"><i class="fa-solid fa-chair">🪑</i><span>Mesas</span></li>
      <li><i>👥</i><span>Clientes</span></li>
      <li><i>📊</i><span>Reportes</span></li>
      <li><i>⚙️</i><span>Configuración</span></li>
    </ul>
  </aside>

  <!-- 🔹 Contenido principal -->
  <main class="main">
    <header>
      <button id="toggleSidebar" class="toggle-btn">☰</button>
      <h1>Resumen General de Mesas</h1>
      <div class="user-info">
        <img src="https://cdn-icons-png.flaticon.com/512/2202/2202112.png" alt="Usuario">
        <span><?= htmlspecialchars($nombreUsuario) ?></span>
        <a href="../../../../src/auth/logout.php" class="logout-btn">Cerrar sesión</a>
      </div>
    </header>

    <?php if (isset($error)): ?>
      <div class="alert alert-error" style="color:red; background:#fee; padding:10px; border-radius:8px;">
        ❌ <?= htmlspecialchars($error) ?>
      </div>
    <?php else: ?>
      <div class="resumen-container">
        <h2>Resumen General</h2>

        <div class="resumen-cards">
          <div class="card">
            <h3>Áreas Registradas</h3>
            <p><?= $totalAreas ?></p>
          </div>
          <div class="card">
            <h3>Mesas Creadas</h3>
            <p><?= $totalMesas ?></p>
          </div>
        </div>

        <a href="gestion_mesas.php" class="btn-primary">
          <i class="fa-solid fa-arrow-left"></i> Ir al gestor de Mesas
        </a>
      </div>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../../dashboard_propietario/js/script.js"></script>
</body>
</html>