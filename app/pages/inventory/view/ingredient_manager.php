<?php
require_once('../Model/Entity/Connection.php');
$conexion = Connection::getConnection();
$query = "SELECT *, (cantidad * costo_unitario) AS valor_total FROM insumos";
$resultado = $conexion->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de Ingredientes</title>
    <link rel="stylesheet" href="../CSS/insumos.css">
    <link rel="stylesheet" href="../CSS/modales.css">
    <link rel="stylesheet" href="../CSS/registroInsumo.css">
    <link rel="stylesheet" href="../CSS/tables.css">
    <style>
        .modal-content { max-height: 90vh; overflow-y: auto; }
    </style>
</head>
<body>

<div id="formModal" class="modal hidden">
  <div class="modal-content">
    <span class="close" onclick="hideModal('formModal')">&times;</span>
    <h2 id="modalTitle">Registrar Ingrediente</h2>
    <form id="ingredientForm" 
          action="/Proyecto_de_aula/Controller/store/inputs_add.php" 
          method="POST" enctype="multipart/form-data"
          onsubmit="return validarFechas()}">

      <input type="hidden" name="id" id="ingredient_id">

      <label>Nombre:</label>
      <input type="text" name="nombre" id="nombre" required>

      <label>Cantidad:</label>
      <input type="number" name="cantidad" id="cantidad" required>

      <label>Cantidad mínima:</label>
      <input type="number" name="cantidad_minima" id="cantidad_minima" required>

      <label>Unidad:</label>
      <select name="unidad" id="unidad" required>
        <option value="">Seleccione</option>
        <option value="Kg">Kg</option>
        <option value="Litro">Litro</option>
        <option value="Unidad">Unidad</option>
      </select>

      <label>Costo unitario:</label>
      <input type="number" step="0.01" name="costo_unitario" id="costo_unitario" required>

      <label>Categoría:</label>
      <select name="categoria" id="categoria" required>
        <option value="">Seleccione</option>
        <option value="Vegetal">Vegetal</option>
        <option value="Carne">Carne</option>
        <option value="Bebida">Bebida</option>
        <option value="Otro">Otro</option>
      </select>

      <label>Fecha ingreso:</label>
      <input type="date" name="fecha_ingreso" id="fecha_ingreso" required>

      <label>Fecha vencimiento:</label>
      <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" required>

      <label>Lote:</label>
      <input type="text" name="lote" id="lote" required>

      <label>Descripción:</label>
      <textarea name="descripcion" id="descripcion"></textarea>

      <label>Ubicación:</label>
      <input type="text" name="ubicacion" id="ubicacion" required>

      <label>Estado:</label>
      <select name="estado" id="estado" required>
        <option value="Activo">Activo</option>
        <option value="Agotado">Agotado</option>
      </select>

      <label>Proveedor:</label>
      <input type="text" name="proveedor" id="proveedor" required>

      <label>Foto:</label>
      <input type="file" name="foto" id="foto" accept="image/*">

      <button type="submit" class="modal-btn" id="submitBtn">Registrar Ingrediente</button>
    </form>
  </div>
</div>

<div class="content">
  <h2>Gestor de Ingredientes</h2>
  <button onclick="newIngredient()" class="modal-btn">+ Registrar Ingrediente</button>
</div>

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

<div class="table-container">
  <table class="styled-table">
    <thead>
      <tr>
        <th>Nombre</th><th>Estado</th><th>Cantidad</th><th>Medida</th>
        <th>Cantidad Min</th><th>Costo Unitario</th><th>Valor Total</th>
        <th>Proveedor</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $resultado->fetch()): ?>
        <tr>
          <td><?= $row['nombre']; ?></td>
          <td><?= $row['estado']; ?></td>
          <td><?= $row['cantidad']; ?></td>
          <td><?= $row['unidad']; ?></td>
          <td><?= $row['cantidad_minima']; ?></td>
          <td><?= $row['costo_unitario']; ?></td>
          <td><?= $row['valor_total']; ?></td>
          <td><?= $row['proveedor']; ?></td>
          <td>
            <a href="javascript:void(0)" 
               onclick='editIngredient(<?= json_encode($row); ?>)' 
               class="btn btn-edit">✏️ Editar</a>
            <a href="/Proyecto_de_aula/Controller/store/inputs_delete.php?id=<?= $row['id']; ?>" 
              onclick="return confirm('¿Eliminar ingrediente?');"
              class="btn btn-delete">🗑️ Eliminar</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script src="../JS/animations/modales.js"></script>
<script src="../JS/ingredient/form_handler.js"></script>

</body>
</html>
