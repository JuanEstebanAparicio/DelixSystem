<?php
require_once __DIR__ . '/../../../middleware/universal_guard.php';

// 🔥 Devuelve array con: tipo (propietario/empleado), id y restaurant_id si aplica
$user = universalGuard();

$id_usuario = ($user['tipo'] === 'propietario')
    ? $user['id']
    : $user['restaurant_id'];

require_once __DIR__ . '/../../../config/supabase.php';

try {

    $stmt = $conexion->prepare("SELECT * FROM dish WHERE id_user = :id_user ORDER BY category, name_dish ASC");
    $stmt->bindParam(':id_user', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $platos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtIng = $conexion->prepare("SELECT id, name FROM storage WHERE id_user = :id_user ORDER BY name ASC");
    $stmtIng->bindParam(':id_user', $id_usuario, PDO::PARAM_INT);
    $stmtIng->execute();
    $ingredientes = $stmtIng->fetchAll(PDO::FETCH_ASSOC);

    foreach ($platos as $i => $dish) {
        $stmt2 = $conexion->prepare("SELECT ingredient_id AS id, quantity_used, unit FROM dish_ingredient WHERE dish_id = ?");
        $stmt2->execute([$dish['id']]);
        $platos[$i]['ingredients'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    $categorias = [];
    foreach ($platos as $dish) {
        $categoria = $dish['category'] ?: 'Sin categoría';
        $categorias[$categoria][] = $dish;
    }

    $listaCategorias = array_keys($categorias);

} catch (PDOException $e) {
    die("<p class='error-msg'>Error al obtener datos desde Supabase: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestor de Platos</title>
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/cards.css">
  <link rel="stylesheet" href="../css/modal.css">
  <link rel="stylesheet" href="../css/navbar.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body>
<header class="navbar">
  <div id="sidebarOverlay" class="sidebar-overlay"></div>
  <button class="hamburger" onclick="toggleSidebar()">☰</button>
  <h1 class="navbar-title">Gestor de Platillos</h1>

  <div class="button-group">
    <button class="create-btn" onclick="newDish()">+ Crear Platillo</button>
    <button class="inventory-btn" onclick="goToInventory()">🍎 Inventario</button>
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
    <h2 class="page-title">Gestor de Platos</h2>

    <div class="button-right">
      <button id="toggleFilters">Mostrar filtros</button>
    </div>


    <div class="filters hidden-filters">
      <select id="filterState">
        <option value="Todos">Todos</option>
        <option value="Activo">Activos</option>
        <option value="Agotado">Agotados</option>
        <option value="Inactivo">Inactivos</option>
      </select>

      <input type="text" id="searchInput" placeholder="Buscar por nombre...">
    </div>

    <div class="card-container" id="dishGrid">
      <?php foreach ($categorias as $categoria => $items): ?>
        <?php foreach ($items as $dish): ?>
          <?php
            $imgPath = "../img/default.png";
            if (!empty($dish['photo'])) {
                $ruta = htmlspecialchars($dish['photo']);
                $imgPath = str_starts_with($ruta, 'media/') ? "../$ruta" : "../media/$ruta";
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

              <?php if (!empty($dish['ingredients'])): ?>
                <p><strong>Ingredientes:</strong></p>
                <ul>
                  <?php foreach ($dish['ingredients'] as $ing): ?>
                    <?php
                      $nombreIng = '';
                      foreach ($ingredientes as $i) {
                        if ($i['id'] == $ing['id']) {
                          $nombreIng = $i['name'];
                          break;
                        }
                      }
                    ?>
                    <li><?= htmlspecialchars($nombreIng) ?> (<?= $ing['quantity_used'] . ' ' . $ing['unit'] ?>)</li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>

            <div class="card-footer">
              <button class="btn btn-edit"
                onclick='editDish(<?= json_encode($dish, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>✏️</button>
              <button class="btn btn-delete"
                 onclick="deleteDish('<?= htmlspecialchars($dish['id']) ?>')">🗑️</button>
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
      <form id="dishForm" enctype="multipart/form-data">
        <input type="hidden" name="action" id="action" value="add">
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
          <input type="text" id="newCategoryInput" name="new_category" placeholder="Nueva categoría" class="hidden-input">
        </div>
        
        <div id="previousIngredients" style="margin-bottom: 10px; font-size: 14px;"></div>

        <div class="form-group">
          <label for="ingredients">Ingredientes:</label>
          <select name="ingredients[]" id="ingredients" multiple required>
            <?php foreach ($ingredientes as $ing): ?>
              <option value="<?= htmlspecialchars($ing['id']) ?>"><?= htmlspecialchars($ing['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div id="ingredientQuantities"></div>

        <div class="form-group">
          <label for="description">Descripción:</label>
          <textarea name="description" id="description" rows="3" placeholder="Breve descripción del plato..."></textarea>
        </div>

        <div class="form-group">
          <label for="state">Estado:</label>
          <select name="state" id="state">
            <option value="Activo">Activo</option>
            <option value="Agotado">Agotado</option>
            <option value="Inactivo">Inactivo</option>
          </select>
        </div>

        <div class="form-group">
          <label for="photo">Foto:</label>
          <input type="file" name="photo" id="photo" accept="image/*">
          <input type="hidden" name="current_photo" id="current_photo_input">

          <div id="currentPhotoContainer" class="photo-preview hidden-img">
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../../public/js/alert.js"></script>
<script src="../../../middleware/session_guard.php"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="../js/dishes_base.js"></script>
<script src="../js/dishes_modal.js"></script>

<script>
  function goToInventory() {
    window.location.href = "/DelixSystem/app/pages/inventory/view/ingredient_manager.php";
  }

  function goToDashboard() {
    window.location.href = "/DelixSystem/app/pages/dashboard_propietario/view/index.php";
  }
</script>

</body>
</html>
