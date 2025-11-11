<?php
require_once __DIR__ . '/../../../middleware/universal_guard.php';
$user = universalGuard();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Combos del Día | DelixSystem</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    /* ==============================
       🔹 BASE GENERAL
    ============================== */
    body {
      font-family: 'Poppins', sans-serif;
      background: #f1f5f9;
      color: #1e293b;
      margin: 0;
      overflow-x: hidden;
    }

    /* ==============================
       🔹 CONTENIDO PRINCIPAL
    ============================== */
    .main-content {
      padding: 40px;
      min-height: 100vh;
      transition: filter 0.3s ease;
      position: relative;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 35px;
    }

    .header h1 {
      font-weight: 700;
      color: #0f172a;
      font-size: 2rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .btn-add {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      color: #fff;
      border: none;
      padding: 12px 20px;
      border-radius: 10px;
      font-weight: 500;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
      transition: all 0.3s ease;
    }

    .btn-add:hover {
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      transform: translateY(-3px);
      box-shadow: 0 6px 18px rgba(37, 99, 235, 0.4);
    }

    .combo-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
      gap: 28px;
    }

    .combo-card {
      background: #fff;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 4px 18px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
    }

    .combo-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }

    .combo-card img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-bottom: 1px solid #e2e8f0;
    }

    .combo-card .content {
      padding: 18px;
    }

    .combo-card h3 {
      font-weight: 600;
      margin-bottom: 8px;
      color: #0f172a;
    }

    .combo-card p {
      color: #475569;
      font-size: 0.9rem;
      margin-bottom: 10px;
    }

    .combo-card .price {
      color: #16a34a;
      font-weight: 600;
    }

    .action-btns {
      display: flex;
      justify-content: space-between;
      margin-top: 12px;
    }

    .btn-edit {
      background: #facc15;
      color: #1e293b;
      border: none;
      border-radius: 8px;
      padding: 6px 14px;
      font-size: 0.85rem;
      transition: all 0.3s;
    }

    .btn-edit:hover {
      background: #eab308;
      transform: translateY(-2px);
    }

    .btn-delete {
      background: #ef4444;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 6px 14px;
      font-size: 0.85rem;
      transition: all 0.3s;
    }

    .btn-delete:hover {
      background: #dc2626;
      transform: translateY(-2px);
    }

    /* ==============================
       🔹 SIDEBAR DESLIZABLE DERECHA
    ============================== */
    .sidebar {
      position: fixed;
      top: 0;
      right: -90px;
      width: 90px;
      height: 100vh;
      background: linear-gradient(180deg, #0f172a, #1e293b);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 30px;
      transition: right 0.4s ease;
      box-shadow: -4px 0 20px rgba(0, 0, 0, 0.25);
      z-index: 1002;
    }

    .sidebar.open {
      right: 0;
    }

    .sidebar button {
      width: 55px;
      height: 55px;
      border-radius: 15px;
      border: none;
      background: rgba(255, 255, 255, 0.08);
      color: #f8fafc;
      font-size: 1.5rem;
      margin: 12px 0;
      cursor: pointer;
      transition: all 0.3s;
    }

    .sidebar button:hover {
      background: rgba(255, 255, 255, 0.18);
      transform: scale(1.1);
    }

    /* 🔹 BOTÓN FLOTANTE PARA ABRIR */
    .toggle-btn {
      position: fixed;
      top: 50%;
      right: 15px;
      transform: translateY(-50%);
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: #1e3a8a;
      color: white;
      border: none;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      font-size: 1.3rem;
      z-index: 1003;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .toggle-btn:hover {
      background: #2563eb;
      transform: translateY(-50%) scale(1.1);
    }

    .toggle-btn.rotate {
      transform: translateY(-50%) rotate(180deg);
    }

    /* 🔹 FONDO OSCURO */
    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.4s ease;
      z-index: 1001;
    }

    .overlay.active {
      opacity: 1;
      pointer-events: all;
    }

    @media (max-width: 768px) {
      .main-content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>

  <!-- 🔹 BOTÓN FLOTANTE -->
  <button class="toggle-btn" id="toggleSidebar">▶</button>

  <!-- 🔹 FONDO OSCURO -->
  <div class="overlay" id="overlay"></div>

  <!-- 🔹 SIDEBAR -->
  <div class="sidebar" id="sidebar">
    <button onclick="goToDashboard()">🏠</button>
    <button onclick="goToDishes()">🍽️</button>
    <button onclick="goToInventory()">📦</button>
  </div>

  <!-- 🔹 CONTENIDO PRINCIPAL -->
  <div class="main-content" id="content">
    <div class="header">
      <h1>🍱 Combos del Día</h1>
      <button class="btn-add" data-bs-toggle="modal" data-bs-target="#modalNuevoCombo">+ Nuevo Combo</button>
    </div>

    <div class="combo-grid">
      <div class="combo-card">
        <img src="https://images.unsplash.com/photo-1600891964091-1e43c78e7a3d" alt="Combo">
        <div class="content">
          <h3>Combo Ejecutivo</h3>
          <p>Incluye arroz, pollo a la plancha y ensalada.</p>
          <span class="price">$18.000</span>
          <div class="action-btns">
            <button class="btn-edit">Editar</button>
            <button class="btn-delete">Eliminar</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL NUEVO COMBO -->
  <div class="modal fade" id="modalNuevoCombo" tabindex="-1" aria-labelledby="modalNuevoComboLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalNuevoComboLabel">Crear nuevo combo 🍛</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formNuevoCombo">
            <div class="mb-3">
              <label for="comboName" class="form-label">Nombre del combo</label>
              <input type="text" id="comboName" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="comboDesc" class="form-label">Descripción</label>
              <textarea id="comboDesc" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
              <label for="comboPrice" class="form-label">Precio</label>
              <input type="number" id="comboPrice" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="comboImage" class="form-label">Imagen (URL)</label>
              <input type="url" id="comboImage" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Guardar Combo</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Redirecciones
    function goToDashboard() {
      window.location.href = "/DelixSystem/app/pages/dashboard_propietario/view/index.php";
    }
    function goToDishes() {
      window.location.href = "/DelixSystem/app/pages/dishes_manager/view/dishes_manager.php";
    }
    function goToInventory() {
      window.location.href = "/DelixSystem/app/pages/inventory/view/ingredient_manager.php";
    }

    // Sidebar toggle
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");
    const overlay = document.getElementById("overlay");

    toggleBtn.addEventListener("click", () => {
      const open = sidebar.classList.toggle("open");
      overlay.classList.toggle("active", open);
      toggleBtn.classList.toggle("rotate", open);
    });

    overlay.addEventListener("click", () => {
      sidebar.classList.remove("open");
      overlay.classList.remove("active");
      toggleBtn.classList.remove("rotate");
    });
  </script>
</body>
</html>
