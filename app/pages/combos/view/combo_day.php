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
    /* =========================
       🌈 ESTILOS GLOBALES
    ========================= */
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8fafc;
      padding: 30px;
      color: #333;
      display: flex;
      overflow-x: hidden;
    }

    /* Animación de entrada suave */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* =========================
       🔹 SIDEBAR LATERAL
    ========================= */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 85px;
      height: 100vh;
      background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 25px;
      gap: 25px;
      box-shadow: 4px 0 15px rgba(0, 0, 0, 0.15);
      z-index: 10;
    }

    .sidebar button {
      background: rgba(255, 255, 255, 0.05);
      border: none;
      color: #e2e8f0;
      font-size: 1.5rem;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 60px;
      height: 60px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .sidebar button:hover {
      background-color: #334155;
      color: #fff;
      transform: translateY(-3px) scale(1.08);
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .sidebar .tooltip {
      position: absolute;
      left: 95px;
      background: #1e293b;
      color: white;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 0.85rem;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.25s ease-in-out, transform 0.25s ease;
      transform: translateY(-10px);
    }

    .sidebar button:hover + .tooltip {
      opacity: 1;
      transform: translateY(0);
    }

    /* =========================
       🔹 CONTENIDO PRINCIPAL
    ========================= */
    .main-content {
      flex: 1;
      margin-left: 110px;
      animation: fadeIn 0.6s ease;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .header h1 {
      font-weight: 700;
      color: #1e293b;
      letter-spacing: 0.5px;
    }

    /* =========================
       🔹 BOTÓN "NUEVO COMBO"
    ========================= */
    .btn-add {
      background: linear-gradient(135deg, #007bff, #4f9cff);
      color: #fff;
      font-weight: 500;
      border: none;
      border-radius: 12px;
      padding: 12px 22px;
      box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
      transition: all 0.3s ease;
    }

    .btn-add:hover {
      background: linear-gradient(135deg, #0069d9, #3d89f5);
      transform: translateY(-3px);
      box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
    }

    /* =========================
       🔹 GRID DE COMBOS
    ========================= */
    .combo-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
      gap: 30px;
    }

    .combo-card {
      background: #ffffff;
      border-radius: 18px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      overflow: hidden;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      position: relative;
      animation: fadeIn 0.5s ease;
    }

    .combo-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.12);
    }

    .combo-card img {
      width: 100%;
      height: 190px;
      object-fit: cover;
      transition: transform 0.4s ease;
    }

    .combo-card:hover img {
      transform: scale(1.05);
    }

    .combo-card .content {
      padding: 20px;
    }

    .combo-card h3 {
      font-size: 1.15rem;
      font-weight: 600;
      margin-bottom: 6px;
      color: #1e293b;
    }

    .combo-card p {
      color: #555;
      font-size: 0.9rem;
      margin-bottom: 12px;
      line-height: 1.4;
    }

    .combo-card .price {
      font-weight: 600;
      color: #16a34a;
      font-size: 1rem;
    }

    /* =========================
       🔹 BOTONES DE ACCIÓN
    ========================= */
    .action-btns {
      display: flex;
      justify-content: space-between;
      margin-top: 12px;
    }

    .btn-edit, .btn-delete {
      border: none;
      padding: 7px 14px;
      border-radius: 8px;
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.25s ease;
    }

    .btn-edit {
      background-color: #facc15;
      color: #222;
    }

    .btn-edit:hover {
      background-color: #eab308;
      transform: translateY(-2px);
    }

    .btn-delete {
      background-color: #ef4444;
      color: #fff;
    }

    .btn-delete:hover {
      background-color: #dc2626;
      transform: translateY(-2px);
    }

    /* =========================
       🔹 MODAL NUEVO COMBO
    ========================= */
    .modal-content {
      border-radius: 14px;
      padding: 15px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.1);
      border: none;
    }

    .modal-title {
      font-weight: 600;
      color: #1e293b;
    }

    .form-label {
      font-weight: 500;
      color: #374151;
    }

    .form-control {
      border-radius: 10px;
      border: 1px solid #d1d5db;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-control:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    /* =========================
       ⚙️ RESPONSIVE
    ========================= */
    @media (max-width: 768px) {
      .main-content {
        margin-left: 90px;
      }
      .header h1 {
        font-size: 1.5rem;
      }
      .combo-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <!-- 🔹 SIDEBAR LATERAL -->
  <div class="sidebar">
    <div>
      <button onclick="goToDashboard()">🏠</button>
      <div class="tooltip">Dashboard</div>
    </div>
    <div>
      <button onclick="alert('Abrir gestor de platos 🍽️')">🍽️</button>
      <div class="tooltip">Gestor de Platos</div>
    </div>
    <div>
      <button onclick="alert('Abrir Inventario 📦')">📦</button>
      <div class="tooltip">Inventario</div>
    </div>
  </div>

  <!-- 🔹 CONTENIDO PRINCIPAL -->
  <div class="main-content">
    <div class="header">
      <h1>🍱 Combos del Día</h1>
      <button class="btn-add" data-bs-toggle="modal" data-bs-target="#modalNuevoCombo">+ Nuevo Combo</button>
    </div>

    <!-- 🔹 GRID DE COMBOS -->
    <div class="combo-grid" id="comboGrid">
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

      <div class="combo-card">
        <img src="https://images.unsplash.com/photo-1604908177522-0409beaa6df6" alt="Combo Vegetariano">
        <div class="content">
          <h3>Combo Vegetariano</h3>
          <p>Tofu, verduras salteadas y arroz integral.</p>
          <span class="price">$16.000</span>
          <div class="action-btns">
            <button class="btn-edit">Editar</button>
            <button class="btn-delete">Eliminar</button>
          </div>
        </div>
      </div>

      <div class="combo-card">
        <img src="https://images.unsplash.com/photo-1565958011705-44e211a1b61e" alt="Combo Familiar">
        <div class="content">
          <h3>Combo Familiar</h3>
          <p>4 porciones de arroz, carne y papas a la francesa.</p>
          <span class="price">$42.000</span>
          <div class="action-btns">
            <button class="btn-edit">Editar</button>
            <button class="btn-delete">Eliminar</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 🧩 MODAL NUEVO COMBO -->
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
              <input type="text" id="comboName" class="form-control" placeholder="Ej. Combo Ejecutivo" required>
            </div>
            <div class="mb-3">
              <label for="comboDesc" class="form-label">Descripción</label>
              <textarea id="comboDesc" class="form-control" rows="3" placeholder="Describe el combo..." required></textarea>
            </div>
            <div class="mb-3">
              <label for="comboPrice" class="form-label">Precio</label>
              <input type="number" id="comboPrice" class="form-control" placeholder="Ej. 18000" required>
            </div>
            <div class="mb-3">
              <label for="comboImage" class="form-label">Imagen (URL)</label>
              <input type="url" id="comboImage" class="form-control" placeholder="https://..." required>
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
    function goToDashboard() {
      window.location.href = "/DelixSystem/app/pages/dashboard_propietario/view/index.php";
    }

    document.getElementById("formNuevoCombo").addEventListener("submit", (e) => {
      e.preventDefault();
      alert("✅ Combo creado correctamente (simulación)");
      const modal = bootstrap.Modal.getInstance(document.getElementById("modalNuevoCombo"));
      modal.hide();
    });
  </script>
</body>
</html>
