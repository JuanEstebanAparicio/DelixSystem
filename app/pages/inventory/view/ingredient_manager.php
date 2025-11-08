<?php
require_once __DIR__ . '/../../../middleware/session_guard.php';
protectPage();

$id_usuario = $_SESSION['usuario']['id'];

require_once __DIR__ . '/../../../config/supabase.php';

try {
    $stmt = $conexion->prepare("SELECT * FROM storage WHERE id_user = :id_user ORDER BY category, name ASC");
    $stmt->bindParam(':id_user', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();

    $insumos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $categorias = [];

    foreach ($insumos as $ing) {
        $cat = !empty($ing['category']) ? $ing['category'] : 'Sin categoría';
        $categorias[$cat][] = $ing;
    }

    $listaCategorias = array_keys($categorias);

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
</head>

<body>
<header class="navbar">
  <button class="hamburger" onclick="toggleSidebar()">☰</button>
  <h1 class="navbar-title">Gestor de Ingredientes</h1>

  <div class="button-group">
    <button class="create-btn" onclick="newIngredient()">+ Crear Ingrediente</button>
    <button class="inventory-btn" onclick="goToDishes()">📦 Platillos</button>
    <button class="dashboard-btn" onclick="goToDashboard()">🏠 Dashboard</button>
  </div>
</header>

<nav class="sidebar" id="sidebarMenu">
  <h3 class="sidebar-title">Categorías</h3>
  <button id="reloadBtn" class="reload-btn" onclick="reloadCategories()">🔄 Recargar</button>

  <ul class="sidebar-list" id="categoryList">
    <li class="sidebar-item" onclick="mostrarCategoria('Todos')">Todos</li>
    <?php foreach ($categorias as $categoria => $items): ?>
      <li class="sidebar-item" onclick="mostrarCategoria('<?= htmlspecialchars($categoria) ?>')">
        <?= htmlspecialchars($categoria) ?>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>

<main class="main-content container">
  <h2 class="page-title">Gestor de Ingredientes</h2>

  <div class="card-container" id="ingredientGrid" data-user="<?= $id_usuario ?>">

    <?php foreach ($categorias as $categoria => $items): ?>
      <?php foreach ($items as $ing): ?>
        <div class="ingredient-card card" data-category="<?= htmlspecialchars($categoria) ?>">
          <?php if (!empty($ing['photo'])): ?>
            <div class="card-image">
              <img src="../<?= htmlspecialchars($ing['photo']) ?>" alt="<?= htmlspecialchars($ing['name']) ?>">
            </div>
          <?php else: ?>
            <div class="card-image">
              <img src="../img/default.png" alt="Sin imagen">
            </div>
          <?php endif; ?>

          <div class="card-body">
            <h4 class="ingredient-name"><?= htmlspecialchars($ing['name']) ?></h4>
            <p class="ingredient-cost">$<?= number_format($ing['unit_cost'], 0, ',', '.') ?></p>
            <p class="ingredient-state <?= strtolower($ing['state']) ?>"><?= htmlspecialchars($ing['state']) ?></p>
            <p><strong>Cantidad:</strong> <?= htmlspecialchars($ing['amount']) ?> <?= htmlspecialchars($ing['unit']) ?></p>
            <p><strong>Mínimo:</strong> <?= htmlspecialchars($ing['minimum_quantity']) ?></p>
            <p class="ingredient-desc"><?= htmlspecialchars($ing['description'] ?: 'Sin descripción') ?></p>
            <p><strong>Proveedor:</strong> <?= htmlspecialchars($ing['supplier'] ?: 'No especificado') ?></p>
            <p><strong>Ubicación:</strong> <?= htmlspecialchars($ing['location'] ?: 'Sin ubicación') ?></p>
          </div>

          <div class="card-footer">
            <button class="btn btn-edit" onclick='editIngredient(<?= json_encode($ing, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>✏️</button>
            <button class="btn btn-delete" onclick="deleteIngredient(<?= $ing['id'] ?>)">🗑️</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>

    <div class="ingredient-card card create-card" id="globalCreateCard" onclick="newIngredient()">
      <div class="card-body text-center">
        <span class="plus-icon">+</span>
        <p>Crear Ingrediente</p>
      </div>
    </div>
  </div>
</main>

<div id="formModal" class="modal hidden">
  <div class="modal-content">
    <span class="close" onclick="hideModal('formModal')">&times;</span>
    <h2 id="modalTitle" class="modal-title">Registrar Ingrediente</h2>

    <form id="ingredientForm" action="../php/inventario/ingredienteController.php" method="POST" enctype="multipart/form-data" onsubmit="return validarFechas()">
      <input type="hidden" name="id" id="ingredient_id">
      <input type="hidden" name="action" id="action">

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
        <select name="category" id="category" required>
          <option value="" disabled selected>Seleccione o cree una categoría</option>
          <?php foreach ($listaCategorias as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
          <?php endforeach; ?>
          <option value="__new__">+ Nueva categoría...</option>
        </select>
        <input type="text" id="newCategoryInput" name="new_category" placeholder="Nueva categoría" class="hidden-input">
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
        <label for="state">Estado:</label>
        <select name="state" id="state">
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
        <div id="currentPhotoContainer" class="photo-preview hidden">
          <p>Foto actual:</p>
          <img id="currentPhoto" src="" alt="Foto actual del ingrediente" class="preview-img">
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" id="submitBtn" class="btn btn-primary">Guardar Ingrediente</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../../public/js/alert.js"></script>
<script src="../js/form_handler.js"></script>
<script src="../js/inventario.js"></script>
<script>
  function goToDishes() {
    window.location.href = "/DelixSystem/app/pages/dishes_manager/view/dishes_manager.php";
  }

  function goToDashboard() {
    window.location.href = "/DelixSystem/app/pages/dashboard_propietario/view/index.php";
  }
</script>
</body>
</html>
