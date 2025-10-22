<?php
require_once __DIR__ . '/../../../config/supabase.php';

try {
    $query = $conexion->query("SELECT * FROM storage");
    $insumos = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p>Error al obtener datos desde Supabase: " . $e->getMessage() . "</p>");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de Ingredientes</title>
    <link rel="stylesheet" href="../css/insumos.css">
    <link rel="stylesheet" href="../css/modales.css">
    <link rel="stylesheet" href="../css/registroInsumo.css">
    <link rel="stylesheet" href="../css/tables.css">
    <style>
        .modal-content { max-height: 90vh; overflow-y: auto; }
    </style>
</head>
<body>

<!-- MODAL FORMULARIO -->
<div id="formModal" class="modal hidden">
  <div class="modal-content">
    <span class="close" onclick="hideModal('formModal')">&times;</span>
    <h2 id="modalTitle">Registrar Ingrediente</h2>

    <form id="ingredientForm" 
          action="../php/inputs_add.php" 
          method="POST" enctype="multipart/form-data"
          onsubmit="return validarFechas()">

      <input type="hidden" name="id" id="ingredient_id">

      <label>Nombre:</label>
      <input type="text" name="name" id="name" required>

      <label>Cantidad:</label>
      <input type="number" name="amount" id="amount" required>

      <label>Cantidad mínima:</label>
      <input type="number" name="minimum_amount" id="minimum_amount" required>

      <label>Unidad:</label>
      <select name="unit" id="unit" required>
        <option value="">Seleccione</option>
        <option value="Kg">Kg</option>
        <option value="Litro">Litro</option>
        <option value="Unidad">Unidad</option>
      </select>

      <label>Costo unitario:</label>
      <input type="number" step="0.01" name="unit_cost" id="unit_cost" required>

      <label>Categoría:</label>
      <select name="category" id="category" required>
        <option value="">Seleccione</option>
        <option value="Vegetal">Vegetal</option>
        <option value="Carne">Carne</option>
        <option value="Bebida">Bebida</option>
        <option value="Otro">Otro</option>
      </select>

      <label>Fecha ingreso:</label>
      <input type="date" name="entrance_date" id="entrance_date" required>

      <label>Fecha vencimiento:</label>
      <input type="date" name="expiration_date" id="expiration_date" required>

      <label>Lote:</label>
      <input type="text" name="batch" id="batch" required>

      <label>Descripción:</label>
      <textarea name="description" id="description"></textarea>

      <label>Ubicación:</label>
      <input type="text" name="location" id="location" required>

      <label>Estado:</label>
      <select name="state" id="state" required>
        <option value="Activo">Activo</option>
        <option value="Agotado">Agotado</option>
      </select>

      <label>Proveedor:</label>
      <input type="text" name="supplier" id="supplier" required>

      <label>Foto:</label>
      <input type="file" name="photo" id="photo" accept="image/*">

      <button type="submit" class="modal-btn" id="submitBtn">Registrar Ingrediente</button>
    </form>
  </div>
</div>

<!-- ENCABEZADO -->
<div class="content">
  <h2>Gestor de Ingredientes</h2>
  <button onclick="newIngredient()" class="modal-btn">+ Registrar Ingrediente</button>
</div>

<!-- MENSAJES DE CONFIRMACIÓN -->
<div class="content">
  <?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] == 1): ?>
      <p class="success-msg">✅ Ingrediente registrado correctamente.</p>
    <?php elseif ($_GET['success'] == 2): ?>
      <p class="success-msg">✏️ Ingrediente actualizado correctamente.</p>
    <?php elseif ($_GET['success'] == 3): ?>
      <p class="success-msg">🗑️ Ingrediente eliminado correctamente.</p>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- TABLA DE INSUMOS -->
<div class="table-container">
  <table class="styled-table">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Estado</th>
        <th>Cantidad</th>
        <th>Unidad</th>
        <th>Cantidad Mín.</th>
        <th>Costo Unitario</th>
        <th>Valor Total</th>
        <th>Proveedor</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($insumos)): ?>
        <?php foreach ($insumos as $row): ?>
          <?php $valor_total = $row['amount'] * $row['unit_cost']; ?>
          <tr>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['state']); ?></td>
            <td><?= htmlspecialchars($row['amount']); ?></td>
            <td><?= htmlspecialchars($row['unit']); ?></td>
            <td><?= htmlspecialchars($row['minimum_quantity']); ?></td>
            <td><?= htmlspecialchars($row['unit_cost']); ?></td>
            <td><?= htmlspecialchars(number_format($valor_total, 2)); ?></td>
            <td><?= htmlspecialchars($row['supplier']); ?></td>
            <td>
              <a href="javascript:void(0)" 
                 onclick='editIngredient(<?= json_encode($row); ?>)' 
                 class="btn btn-edit">✏️ Editar</a>
              <a href="../php/inputs_delete.php?id=<?= $row['id']; ?>" 
                 onclick="return confirm('¿Eliminar ingrediente?');"
                 class="btn btn-delete">🗑️ Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="9">No hay registros de ingredientes.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script src="../js/form_handler.js"></script>
<script src="../js/modales.js"></script>

</body>
</html>
