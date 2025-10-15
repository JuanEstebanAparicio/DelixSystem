<?php
include __DIR__ . '/../../config/supabase.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Mesas</title>
  <link rel="stylesheet" href="../../CSS/gestion_mesas.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="main-header">
  <h1><i class="fa-solid fa-utensils"></i> Gestión de Mesas</h1>
</header>

<div class="container">

  <?php if (isset($_GET['error'])): ?>
    <div class="alert <?= $_GET['error'] ? 'alert-error' : 'alert-success' ?>">
      <?php if ($_GET['error'] === 'area_existente'): ?>
        ⚠️ Ya existe un área con ese nombre.
      <?php elseif ($_GET['error'] === 'mesa_existente'): ?>
        ⚠️ Ya existe una mesa con ese nombre en esta área.
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <section class="add-section">
    <h2><i class="fa-solid fa-plus"></i> Nueva área</h2>
    <form action="crud_areas.php" method="POST" class="add-form">
      <input type="hidden" name="accion" value="crear">
      <input type="text" name="nombre_area" placeholder="Nombre del área" required class="input-text">
      <button type="submit" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Crear área
      </button>
    </form>
  </section>

  <hr class="divider">

  <?php
  $areasStmt = $conexion->query("SELECT * FROM areas ORDER BY id_area");
  $areas = $areasStmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($areas as $area):
  ?>
  <div class="area-card">
    <div class="area-header">
      <h3><i class="fa-solid fa-layer-group"></i> <?= htmlspecialchars($area['nombre']) ?></h3>
      <div class="area-actions">
        <form action="crud_areas.php" method="POST" class="inline-form">
          <input type="hidden" name="accion" value="editar">
          <input type="hidden" name="id_area" value="<?= $area['id_area'] ?>">
          <input type="text" name="nombre_area" placeholder="Nuevo nombre" required class="input-text-small">
          <button type="submit" class="btn-icon edit"><i class="fa-solid fa-pen"></i></button>
        </form>
        <a href="crud_areas.php?accion=eliminar&id_area=<?= $area['id_area'] ?>" onclick="return confirm('¿Eliminar esta área y sus mesas?');" class="btn-icon delete">
          <i class="fa-solid fa-trash"></i>
        </a>
      </div>
    </div>

    <form action="crud_mesas.php" method="POST" class="add-form mesa-form">
      <input type="hidden" name="accion" value="crear">
      <input type="hidden" name="id_area" value="<?= $area['id_area'] ?>">
      <input type="text" name="nombre_mesa" placeholder="Nombre de la mesa" required class="input-text-small">
      <button type="submit" class="btn-secondary">
        <i class="fa-solid fa-plus"></i> Añadir mesa
      </button>
    </form>

    <div class="mesas-grid">
      <?php
      $stmt = $conexion->prepare("SELECT * FROM mesas WHERE id_area = ?");
      $stmt->execute([$area['id_area']]);
      while ($mesa = $stmt->fetch(PDO::FETCH_ASSOC)):
      ?>
      <div class="mesa-card">
        <h4><i class="fa-solid fa-chair"></i> <?= htmlspecialchars($mesa['nombre']) ?></h4>
        <div class="mesa-actions">
          <form action="ver_qr.php" method="GET" target="_blank" class="inline-form">
            <input type="hidden" name="id" value="<?= htmlspecialchars($mesa['id_mesa']) ?>">
            <button type="submit" class="btn-icon qr"><i class="fa-solid fa-qrcode"></i></button>
          </form>

          <form action="crud_mesas.php" method="POST" class="inline-form">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_mesa" value="<?= $mesa['id_mesa'] ?>">
            <input type="hidden" name="id_area" value="<?= $area['id_area'] ?>">
            <input type="text" name="nombre_mesa" placeholder="Nuevo nombre" required class="input-text-small">
            <button type="submit" class="btn-icon edit"><i class="fa-solid fa-pen"></i></button>
          </form>

          <a href="crud_mesas.php?accion=eliminar&id_mesa=<?= $mesa['id_mesa'] ?>" onclick="return confirm('¿Eliminar esta mesa?');" class="btn-icon delete">
            <i class="fa-solid fa-trash"></i>
          </a>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endforeach; ?>

</div>
</body>
</html>
