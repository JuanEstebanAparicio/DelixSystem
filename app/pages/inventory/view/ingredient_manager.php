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
    die("<p>Error al obtener datos desde Supabase: " . $e->getMessage() . "</p>");
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

<!-- Sidebar -->
<nav class="sidebar">
  <h3>Categorías</h3>
  <ul>
    <?php foreach ($categorias as $categoria => $items): ?>
      <li onclick="mostrarCategoria('<?= htmlspecialchars($categoria) ?>')">
        <?= htmlspecialchars($categoria) ?>
      </li>
    <?php endforeach; ?>
  </ul>
  <button class="create-btn" onclick="newIngredient()">+ Registrar Ingrediente</button>
</nav>

<!-- Main content -->
<main class="main-content">
  <h2>Gestor de Ingredientes</h2>

  <?php foreach ($categorias as $categoria => $items): ?>
    <section class="categoria" id="<?= htmlspecialchars($categoria) ?>">
      <h3><?= htmlspecialchars($categoria) ?></h3>
      <div class="card-container">

        <?php foreach ($items as $ing): ?>
          <?php
            // ✅ Construir ruta segura de la imagen
            $imgPath = !empty($ing['photo'])
              ? "../" . htmlspecialchars($ing['photo'])
              : "../img/default.png";

            // Si el archivo no existe localmente, usar imagen por defecto
            if (!file_exists(__DIR__ . "/../" . $ing['photo'])) {
              $imgPath = "../img/default.png";
            }
          ?>
          <div class="ingredient-card">
            <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($ing['name']) ?>">
            <h4><?= htmlspecialchars($ing['name']) ?></h4>
            <p><strong>$<?= number_format($ing['unit_cost'], 0, ',', '.') ?></strong></p>
            <p class="estado <?= strtolower($ing['state']) ?>">
              <?= htmlspecialchars($ing['state']) ?>
            </p>
            <p class="cantidad"><?= htmlspecialchars($ing['amount']) ?> <?= htmlspecialchars($ing['unit']) ?></p>
            <p class="desc"><?= htmlspecialchars($ing['description'] ?: '') ?></p>

            <div class="card-actions">
              <button class="btn-edit" onclick='editIngredient(<?= json_encode($ing) ?>)'>✏️</button>
              <a href="../php/inputs_delete.php?id=<?= $ing['id'] ?>" 
                 class="btn-delete" onclick="return confirm('¿Eliminar ingrediente?')">🗑️</a>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- Crear nuevo -->
        <div class="ingredient-card create-card" onclick="newIngredient()">
          + Crear Ingrediente
        </div>
      </div>
    </section>
  <?php endforeach; ?>
</main>

<!-- Modal -->
<div id="formModal" class="modal hidden">
  <div class="modal-content">
    <span class="close" onclick="hideModal('formModal')">&times;</span>
    <h2 id="modalTitle">Registrar Ingrediente</h2>

    <form id="ingredientForm" 
          action="../php/inputs_add.php" 
          method="POST" 
          enctype="multipart/form-data"
          onsubmit="return validarFechas()">

      <input type="hidden" name="id" id="ingredient_id">

      <label for="name">Nombre:</label>
      <input type="text" name="name" id="name" required>

      <label for="amount">Cantidad:</label>
      <input type="number" name="amount" id="amount" required>

      <label for="minimum_quantity">Cantidad mínima:</label>
      <input type="number" name="minimum_quantity" id="minimum_quantity" required>

      <label for="unit">Unidad:</label>
      <select name="unit" id="unit" required>
        <option value="Kg">Kg</option>
        <option value="Litro">Litro</option>
        <option value="Unidad">Unidad</option>
      </select>

      <label for="unit_cost">Costo unitario:</label>
      <input type="number" step="0.01" name="unit_cost" id="unit_cost" required>

      <label for="category">Categoría:</label>
      <input type="text" name="category" id="category" required>

      <label for="batch">Lote:</label>
      <input type="text" name="batch" id="batch" placeholder="Ej: Lote-2025-A">

      <label for="description">Descripción:</label>
      <textarea name="description" id="description" rows="3" placeholder="Breve descripción del insumo..."></textarea>

      <label for="location">Ubicación en almacén:</label>
      <input type="text" name="location" id="location" placeholder="Ej: Estante A3 - Nivel 2">

      <label for="status">Estado:</label>
      <select name="status" id="status">
        <option value="Activo">Activo</option>
        <option value="Agotado">Agotado</option>
      </select>

      <label for="supplier">Proveedor:</label>
      <input type="text" name="supplier" id="supplier" required>

      <label for="fecha_ingreso">Fecha ingreso:</label>
      <input type="date" name="fecha_ingreso" id="fecha_ingreso">

      <label for="fecha_vencimiento">Fecha vencimiento:</label>
      <input type="date" name="fecha_vencimiento" id="fecha_vencimiento">

      <label for="photo">Foto:</label>
      <input type="file" name="photo" id="photo" accept="image/*">

      <button type="submit" id="submitBtn" class="modal-btn">Registrar Ingrediente</button>
    </form>
  </div>
</div>

<script src="../js/form_handler.js"></script>
</body>
</html>
