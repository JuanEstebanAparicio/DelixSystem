<?php
require_once __DIR__ . '/../../../config/supabase.php';

try {
    $query = $conexion->query("SELECT * FROM storage ORDER BY category, name ASC");
    $insumos = $query->fetchAll(PDO::FETCH_ASSOC);
    $categorias = [];

    foreach ($insumos as $ing) {
        $cat = $ing['category'] ?: 'Sin categoría';
        $categorias[$cat][] = $ing;
    }
} catch (PDOException $e) {
    die("<p class='error-msg'>Error al obtener datos desde Supabase: " . $e->getMessage() . "</p>");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestor de Ingredientes</title>
  <link rel="stylesheet" href="../css/ingredient_manager.css">
  <link rel="stylesheet" href="../css/modales.css">
  <link rel="stylesheet" href="../css/registroInsumo.css">
</head>
<body>

<!-- === NAVBAR SUPERIOR === -->
<header class="navbar">
  <button class="hamburger" onclick="toggleSidebar()">☰</button>
  <h1 class="navbar-title">Gestor de Ingredientes</h1>
  <button class="create-btn" onclick="newIngredient()">+ Crear Ingrediente</button>
</header>

<!-- === SIDEBAR === -->
<nav class="sidebar hidden" id="sidebarMenu">
  <h3 class="sidebar-title">Categorías</h3>
  <ul class="sidebar-list">
    <li class="sidebar-item" onclick="mostrarCategoria('Todos')">Todos</li>
    <?php foreach ($categorias as $categoria => $items): ?>
      <li class="sidebar-item" onclick="mostrarCategoria('<?= htmlspecialchars($categoria) ?>')">
        <?= htmlspecialchars($categoria) ?>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>

<!-- === CONTENIDO PRINCIPAL === -->
<main class="main-content container">
  <h2 class="page-title">Gestor de Ingredientes</h2>

  <?php foreach ($categorias as $categoria => $items): ?>
    <section class="categoria-section" id="<?= htmlspecialchars($categoria) ?>">
      <h3 class="categoria-title"><?= htmlspecialchars($categoria) ?></h3>
      <div class="card-container">

        <?php foreach ($items as $ing): ?>
          <?php
            $imgPath = !empty($ing['photo'])
              ? "../" . htmlspecialchars($ing['photo'])
              : "../img/default.png";

            if (!file_exists(__DIR__ . "/../" . $ing['photo'])) {
              $imgPath = "../img/default.png";
            }
          ?>
          <div class="ingredient-card card">
            <div class="card-image">
              <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($ing['name']) ?>">
            </div>

            <div class="card-body">
              <h4 class="ingredient-name"><?= htmlspecialchars($ing['name']) ?></h4>
              <p class="ingredient-cost">$<?= number_format($ing['unit_cost'], 0, ',', '.') ?></p>
              <p class="ingredient-state <?= strtolower($ing['state']) ?>">
                <?= htmlspecialchars($ing['state']) ?>
              </p>
              <p class="ingredient-amount"><?= htmlspecialchars($ing['amount']) ?> <?= htmlspecialchars($ing['unit']) ?></p>
              <p class="ingredient-desc"><?= htmlspecialchars($ing['description'] ?: 'Sin descripción') ?></p>
            </div>

            <div class="card-footer">
              <button class="btn btn-edit" onclick='editIngredient(<?= json_encode($ing) ?>)'>✏️</button>
              <a href="../php/inputs_delete.php?id=<?= $ing['id'] ?>" 
                 class="btn btn-delete"
                 onclick="return confirm('¿Eliminar ingrediente?')">🗑️</a>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="ingredient-card card create-card" onclick="newIngredient()">
          <div class="card-body text-center">
            <span class="plus-icon">+</span>
            <p>Crear Ingrediente</p>
          </div>
        </div>
      </div>
    </section>
  <?php endforeach; ?>
</main>

<!-- === MODAL === -->
<div id="formModal" class="modal hidden">
  <div class="modal-content">
    <span class="close" onclick="hideModal('formModal')">&times;</span>
    <h2 id="modalTitle" class="modal-title">Registrar Ingrediente</h2>

    <form id="ingredientForm" 
          action="../php/inputs_add.php" 
          method="POST" 
          enctype="multipart/form-data"
          onsubmit="return validarFechas()">

      <input type="hidden" name="id" id="ingredient_id">

      <div class="form-group">
        <label for="name">Nombre:</label>
        <input type="text" name="name" id="name" required>
      </div>

      <div class="form-group">
        <label for="amount">Cantidad:</label>
        <input type="number" name="amount" id="amount" required>
      </div>

      <div class="form-group">
        <label for="minimum_quantity">Cantidad mínima:</label>
        <input type="number" name="minimum_quantity" id="minimum_quantity" required>
      </div>

      <div class="form-group">
        <label for="unit">Unidad:</label>
        <select name="unit" id="unit" required>
          <option value="Kg">Kg</option>
          <option value="Litro">Litro</option>
          <option value="Unidad">Unidad</option>
        </select>
      </div>

      <div class="form-group">
        <label for="unit_cost">Costo unitario:</label>
        <input type="number" step="0.01" name="unit_cost" id="unit_cost" required>
      </div>

      <div class="form-group">
        <label for="category">Categoría:</label>
        <input type="text" name="category" id="category" required>
      </div>

      <div class="form-group">
        <label for="batch">Lote:</label>
        <input type="text" name="batch" id="batch" placeholder="Ej: Lote-2025-A">
      </div>

      <div class="form-group">
        <label for="description">Descripción:</label>
        <textarea name="description" id="description" rows="3" placeholder="Breve descripción del insumo..."></textarea>
      </div>

      <div class="form-group">
        <label for="location">Ubicación en almacén:</label>
        <input type="text" name="location" id="location" placeholder="Ej: Estante A3 - Nivel 2">
      </div>

      <div class="form-group">
        <label for="status">Estado:</label>
        <select name="status" id="status">
          <option value="Activo">Activo</option>
          <option value="Agotado">Agotado</option>
        </select>
      </div>

      <div class="form-group">
        <label for="supplier">Proveedor:</label>
        <input type="text" name="supplier" id="supplier" required>
      </div>

      <div class="form-group inline">
        <label for="fecha_ingreso">Fecha ingreso:</label>
        <input type="date" name="fecha_ingreso" id="fecha_ingreso">
      </div>

      <div class="form-group inline">
        <label for="fecha_vencimiento">Fecha vencimiento:</label>
        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento">
      </div>

      <div class="form-group">
        <label for="photo">Foto:</label>
        <input type="file" name="photo" id="photo" accept="image/*">
      </div>

      <div class="modal-footer">
        <button type="submit" id="submitBtn" class="btn btn-primary">Guardar Ingrediente</button>
      </div>
    </form>
  </div>
</div>

<!-- === JS === -->
<script src="../js/form_handler.js"></script>
<script>
  function toggleSidebar() {
    document.getElementById('sidebarMenu').classList.toggle('hidden');
  }

  function mostrarCategoria(cat) {
    const sections = document.querySelectorAll('.categoria-section');
    sections.forEach(sec => {
      if (cat === 'Todos' || sec.id === cat) sec.style.display = 'block';
      else sec.style.display = 'none';
    });
  }

  // Cerrar sidebar si se hace clic fuera (móvil)
  document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebarMenu');
    const hamburger = document.querySelector('.hamburger');
    if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
      sidebar.classList.add('hidden');
    }
  });
</script>
</body>
</html>
