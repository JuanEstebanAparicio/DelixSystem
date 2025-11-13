<?php
// DelixSystem/app/pages/inventory/view/ingredient_manager.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🧩 Inicialización de UI y sesiones (soporte para empleado y propietario)
require_once __DIR__ . '/../../../shared/bootstrap/employee_ui_bootstrap.php';
require_once __DIR__ . '/../../../middleware/universal_guard.php';
require_once __DIR__ . '/../../../config/supabase.php';

// ✅ Detecta el tipo de usuario activo
$usuario = universalGuard();

// Determinar el ID base para filtrar el inventario
if ($usuario['tipo'] === 'propietario') {
    $id_usuario = $usuario['id'];
} elseif ($usuario['tipo'] === 'empleado') {
    // El empleado usa el ID del restaurante asociado al propietario
    $id_usuario = $usuario['restaurant_id'];
} else {
    header("Location: /DelixSystem/public/index.php");
    exit;
}

// Nombre del usuario actual (solo para mostrar en encabezados)
$nombreUsuario = $usuario['nombre'] ?? 'Usuario';

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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body>
<header class="navbar">
  <button class="hamburger" onclick="toggleSidebar()">☰</button>
  <h1 class="navbar-title">🍽️ Gestor de Ingredientes</h1>

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
    <li class="sidebar-item active" onclick="mostrarCategoria('Todos')">Todos</li>
    <?php foreach ($categorias as $categoria => $items): ?>
      <li class="sidebar-item" onclick="mostrarCategoria('<?= htmlspecialchars($categoria) ?>')">
        <?= htmlspecialchars($categoria) ?>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>

<main class="main-content container">
  <div class="header-section" style="display: flex; justify-content: space-between; align-items: center;">
    <h2 class="page-title">Inventario de Ingredientes</h2>
    <button id="toggleFilters">Mostrar filtros</button>
  </div>

  <div class="filters hidden-filters">
    <select id="filterState">
      <option value="Todos">Todos</option>
      <option value="Activo">Activos</option>
      <option value="no_disponible">No disponibles</option>
    </select>

    <input type="text" id="searchInput" placeholder="Buscar por nombre..."> 
  </div>

  <div class="card-container" id="ingredientGrid" data-user="<?= $id_usuario ?>">
    <?php foreach ($categorias as $categoria => $items): ?>
      <?php foreach ($items as $ing): ?>
        <div class="ingredient-card card" data-category="<?= htmlspecialchars($categoria) ?>">
          <div class="card-image">
            <img src="<?= htmlspecialchars($ing['photo'] ?: '../img/default.png') ?>" alt="<?= htmlspecialchars($ing['name']) ?>">
          </div>

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
          <option value="Proteína">Proteína</option>
          <option value="Carbohidrato">Carbohidrato</option>
          <option value="Vegetal">Vegetal</option>
          <option value="Lácteo">Lácteo</option>
          <option value="Bebida">Bebida</option>
          <option value="Salsa">Salsa</option>
          <option value="Fruta">Fruta</option>
          <option value="Cereal / Harina">Cereal / Harina</option>
          <option value="Snack">Snack</option>
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
          <option value="no_disponible">no disponible</option>
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
        <div id="currentPhotoContainer" class="photo-preview hidden-img">
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
