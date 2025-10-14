<?php
// gestion_mesas.php
include __DIR__ . '/../../config/supabase.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Mesas (Supabase)</title>
    <link rel="stylesheet" href="../../CSS/gestion_mesas.css">
</head>
<body>

<h1>Gestión de Mesas (Supabase)</h1>

<!-- Crear nueva área -->
<h3>Agregar nueva área</h3>
<form action="crud_areas.php" method="POST">
  <input type="hidden" name="accion" value="crear">
  <input type="text" name="nombre_area" placeholder="Nombre del área" required>
  <button type="submit" class="add-btn">+ Área</button>
</form>

<hr>

<?php
$areasStmt = $conexion->query("SELECT * FROM areas ORDER BY id_area");
$areas = $areasStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($areas as $area):
?>
  <div class="area">
    <h2>
      <?= htmlspecialchars($area['nombre']) ?>

      <span>
        <!-- Editar área -->
        <form action="crud_areas.php" method="POST" style="display:inline;">
          <input type="hidden" name="accion" value="editar">
          <input type="hidden" name="id_area" value="<?= $area['id_area'] ?>">
          <input type="text" name="nombre_area" placeholder="Nuevo nombre" required>
          <button type="submit" class="edit-btn">Editar</button>
        </form>

        <!-- Eliminar área -->
        <a href="crud_areas.php?accion=eliminar&id_area=<?= $area['id_area'] ?>"
           onclick="return confirm('¿Eliminar esta área y sus mesas?');">
           <button class="delete-btn">Eliminar</button>
        </a>
      </span>
    </h2>

    <!-- Crear mesa en esta área -->
    <form action="crud_mesas.php" method="POST" style="margin-top:10px;">
      <input type="hidden" name="accion" value="crear">
      <input type="hidden" name="id_area" value="<?= $area['id_area'] ?>">
      <input type="text" name="nombre_mesa" placeholder="Nombre de la mesa" required>
      <button type="submit" class="add-btn">+ Mesa</button>
    </form>

    <!-- Listado de mesas -->
    <div class="mesas">
      <?php
      $stmt = $conexion->prepare("SELECT * FROM mesas WHERE id_area = ?");
      $stmt->execute([$area['id_area']]);
      while ($mesa = $stmt->fetch(PDO::FETCH_ASSOC)):
      ?>
        <div class="mesa">
          <?= htmlspecialchars($mesa['nombre']) ?>
          <span>
            <!-- Ver QR -->
            <form action="ver_qr.php" method="GET" target="_blank">
              <input type="hidden" name="id" value="<?= htmlspecialchars($mesa['id_mesa']) ?>">
              <button type="submit" class="qr-btn">QR</button>
            </form>

            <!-- Editar mesa -->
            <form action="crud_mesas.php" method="POST" style="display:inline;">
              <input type="hidden" name="accion" value="editar">
              <input type="hidden" name="id_mesa" value="<?= $mesa['id_mesa'] ?>">
              <input type="hidden" name="id_area" value="<?= $area['id_area'] ?>">
              <input type="text" name="nombre_mesa" placeholder="Nuevo nombre" required>
              <button type="submit" class="edit-btn">✏️</button>
            </form>

            <!-- Eliminar mesa -->
            <a href="crud_mesas.php?accion=eliminar&id_mesa=<?= $mesa['id_mesa'] ?>"
               onclick="return confirm('¿Eliminar esta mesa?');">
               <button class="delete-btn">🗑️</button>
            </a>
          </span>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
<?php endforeach; ?>

</body>
</html>
