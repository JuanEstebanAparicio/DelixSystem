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
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/modal.css">
</head>

<body>
  <!-- === Sidebar === -->
  <button class="toggle-btn" id="toggleSidebar">▶</button>
  <div class="overlay" id="overlay"></div>
  <div class="sidebar" id="sidebar">
    <button onclick="goToDashboard()">🏠</button>
    <button onclick="goToDishes()">🍽️</button>
    <button onclick="goToInventory()">📦</button>
  </div>

  <!-- === Contenido principal === -->
  <div class="main-content" id="content">
    <div class="header">
      <h1>🍱 Combos del Día</h1>
      <button class="btn-add" onclick="newCombo()">+ Nuevo Combo</button>
    </div>

    <div class="combo-grid" id="comboGrid">
      <p class="loading">Cargando combos...</p>
    </div>
  </div>
<!-- === Modal para Crear/Editar Combo === -->
<div class="modal hidden" id="comboModal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="modalTitle">Nuevo Combo</h2>
      <button class="close-btn" onclick="closeModal()">×</button>
    </div>

    <div class="modal-body">
      <form id="comboForm" enctype="multipart/form-data">
        <div class="form-group">
          <label for="dish">Seleccione un plato</label>
          <select id="dish" name="id_dish" required></select>
        </div>

        <div class="form-group">
          <label for="type">Tipo de Combo</label>
          <select id="type" name="type" required>
            <option value="Estatico">Estatico</option>
            <option value="Dinamico">Dinamico</option>
          </select>
        </div>

        <div class="form-group">
          <label for="state">Estado</label>
          <select id="state" name="state">
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
        </div>

        <div class="form-group">
          <label for="description">Descripción</label>
          <textarea id="description" name="description" rows="3" placeholder="Describe el combo..."></textarea>
        </div>

        <div class="form-group">
          <label for="start_hour">Hora de inicio</label>
          <input type="time" id="start_hour" name="start_hour" required>
        </div>

        <div class="form-group">
          <label for="end_hour">Hora de fin</label>
          <input type="time" id="end_hour" name="end_hour" required>
        </div>

        <div class="form-group">
          <label for="start_date">Fecha de inicio</label>
          <input type="date" id="start_date" name="start_date" required>
        </div>

        <div class="form-group">
          <label for="end_date">Fecha de fin</label>
          <input type="date" id="end_date" name="end_date" required>
        </div>

        <div class="form-group">
          <label for="photo">Foto del combo</label>
          <input type="file" id="photo" name="photo" accept="image/*">
        </div>

        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
          <button type="submit" class="btn-save">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

  <!-- === Scripts === -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/base.js"></script>
  <script src="../js/modal.js"></script>
</body>
</html>
