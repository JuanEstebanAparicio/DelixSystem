<?php
require_once __DIR__ . '/../../../config/supabase.php';

try {
    $query = $conexion->query("SELECT * FROM dish ORDER BY category, name_dish ASC");
    $platos = $query->fetchAll(PDO::FETCH_ASSOC);
    $categorias = [];

    foreach ($platos as $dish) {
        $cat = $dish['category'] ?: 'Sin categoría';
        $categorias[$cat][] = $dish;
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
  <title>Gestor de Platos</title>
  <link rel="stylesheet" href="../css/dish_manager.css">
  <link rel="stylesheet" href="../css/modales.css">
  <link rel="stylesheet" href="../css/registroInsumo.css">
</head>

<body>
  <header class="navbar">
    <button class="hamburger" onclick="toggleSidebar()">☰</button>
    <h1 class="navbar-title">Gestor de Platos</h1>
    <button class="create-btn" onclick="newDish()">+ Crear Plato</button>
  </header>

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

  <main class="main-content container">
    <h2 class="page-title">Gestor de Platos</h2>

    <div class="card-container" id="dishGrid">
      <?php foreach ($categorias as $categoria => $items): ?>
        <?php foreach ($items as $dish): ?>
          <?php
            if (!empty($dish['photo'])) {
                if (strpos($dish['photo'], 'media/') === 0) {
                    $imgPath = "../" . htmlspecialchars($dish['photo']);
                } else {
                    $imgPath = "../media/" . htmlspecialchars($dish['photo']);
                }
            } else {
                $imgPath = "../img/default.png";
            }
          ?>

          <div class="ingredient-card card" data-category="<?= htmlspecialchars($categoria) ?>">
            <div class="card-image">
              <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($dish['name_dish']) ?>">
            </div>

            <div class="card-body">
              <h4 class="ingredient-name"><?= htmlspecialchars($dish['name_dish']) ?></h4>
              <p class="ingredient-cost">$<?= number_format($dish['price'], 0, ',', '.') ?></p>
              <p class="ingredient-state <?= strtolower($dish['state']) ?>">
                <?= htmlspecialchars($dish['state']) ?>
              </p>
              <p class="ingredient-desc"><?= htmlspecialchars($dish['description'] ?: 'Sin descripción') ?></p>
            </div>

            <div class="card-footer">
              <button class="btn btn-edit" onclick='editDish(<?= json_encode($dish) ?>)'>✏️</button>
              <a href="../php/dish_delet.php?id=<?= $dish['id'] ?>"
                 class="btn btn-delete"
                 onclick="return confirm('¿Eliminar plato?')">🗑️</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>

      <div class="ingredient-card card create-card" id="globalCreateCard" onclick="newDish()">
        <div class="card-body text-center">
          <span class="plus-icon">+</span>
          <p>Crear Plato</p>
        </div>
      </div>
    </div>
  </main>

  <div id="formModal" class="modal hidden">
    <div class="modal-content">
      <span class="close" onclick="hideModal('formModal')">&times;</span>
      <h2 id="modalTitle" class="modal-title">Registrar Plato</h2>

      <form id="dishForm"
            action="../php/dish_add.php"
            method="POST"
            enctype="multipart/form-data">

        <input type="hidden" name="id" id="dish_id">
        <input type="hidden" name="created_at" id="created_at">

        <div class="form-group">
          <label for="name_dish">Nombre del plato:</label>
          <input type="text" name="name_dish" id="name_dish" required>
        </div>

        <div class="form-group">
          <label for="price">Precio:</label>
          <input type="number" step="0.01" name="price" id="price" required>
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
          <input type="text" id="newCategoryInput" name="new_category" placeholder="Nueva categoría" class="hidden">
        </div>

        <div class="form-group">
          <label for="description">Descripción:</label>
          <textarea name="description" id="description" rows="3" placeholder="Breve descripción del plato..."></textarea>
        </div>

        <div class="form-group">
          <label for="state">Estado:</label>
          <select name="state" id="state">
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
        </div>

        <div class="form-group">
          <label for="photo">Foto:</label>
          <input type="file" name="photo" id="photo" accept="image/*">
          <div id="currentPhotoContainer" class="photo-preview hidden">
            <p>Foto actual:</p>
            <img id="currentPhoto" src="" alt="Foto actual del plato" class="preview-img">
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" id="submitBtn" class="btn btn-primary">Guardar Plato</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../js/form_handler.js"></script>
  <script src="../js/category_handler.js"></script>
  <script src="../js/sidebar_handler.js"></script>
</body>
</html>
