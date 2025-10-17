<?php
include __DIR__ . '/../../../config/supabase.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Mesas</title>
  <link rel="stylesheet" href="../css/gestion_mesas.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <!-- 🔹 HEADER -->
  <header class="main-header">
    <h1><i class="fa-solid fa-utensils"></i> Gestión de Mesas</h1>
  </header>

  <div class="container">

    <!-- 🔹 ALERTAS -->
    <?php if (isset($_GET['error'])): ?>
      <div class="alert <?= $_GET['error'] ? 'alert-error' : 'alert-success' ?>">
        <?php if ($_GET['error'] === 'area_existente'): ?>
          ⚠️ Ya existe un área con ese nombre.
        <?php elseif ($_GET['error'] === 'mesa_existente'): ?>
          ⚠️ Ya existe una mesa con ese nombre en esta área.
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- 🔹 CREAR NUEVA ÁREA -->
    <section class="add-section">
      <h2><i class="fa-solid fa-plus"></i> Nueva área</h2>
      <form action="../php/area/AreaController.php" method="POST" data-loader>
        <input type="hidden" name="accion" value="crear">
        <input type="text" name="nombre_area" placeholder="Nombre del área" required class="input-text">
        <button type="submit" class="btn-primary">
          <i class="fa-solid fa-plus"></i> Crear área
        </button>
      </form>
    </section>

    <hr class="divider">

    <!-- 🔹 LISTADO DE ÁREAS -->
    <?php
    $areasStmt = $conexion->query("SELECT * FROM areas ORDER BY orden ASC, id_area ASC");

    $areas = $areasStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($areas as $area):
    ?>
      <div class="area-card" data-id="<?= $area['id_area'] ?>">

        <!-- 🔸 CABECERA DE ÁREA -->
        <div class="area-header">
          <h3><i class="fa-solid fa-layer-group"></i> <?= htmlspecialchars($area['nombre']) ?></h3>
          <div class="area-actions">
            <form action="../php/area/AreaController.php" method="POST" data-loader>
              <input type="hidden" name="accion" value="editar">
              <input type="hidden" name="id_area" value="<?= $area['id_area'] ?>">
              <div>
                <button 
                  type="button" 
                  class="btn-icon edit" 
                  data-modal-target="#editAreaModal"
                  data-id-area="<?= $area['id_area'] ?>"
                  data-nombre-area="<?= htmlspecialchars($area['nombre']) ?>"
                  title="Editar área">
                  <i class="fa-solid fa-pen"></i>
                </button>

                <a href="../php/area/AreaController.php?accion=eliminar&id_area=<?= $area['id_area'] ?>"
                   data-confirm="¿Eliminar esta área y sus mesas?"
                   class="btn-icon delete">
                   <i class="fa-solid fa-trash"></i>
                </a>
              </div>
            </form>
          </div>
        </div>

        <!-- 🔸 FORMULARIO CREAR MESA -->
        <form action="../php/mesa/MesaController.php" method="POST" class="add-form mesa-form" data-loader>
          <input type="hidden" name="accion" value="crear">
          <input type="hidden" name="id_area" value="<?= $area['id_area'] ?>">
          <input type="text" name="nombre_mesa" placeholder="Nombre de la mesa" required class="input-text-small">
          <button type="submit" class="btn-secondary">
            <i class="fa-solid fa-plus"></i> Añadir mesa
          </button>
        </form>

        <!-- 🔸 LISTADO DE MESAS -->
        <div class="mesas-grid">
          <?php
          $stmt = $conexion->prepare("SELECT * FROM mesas WHERE id_area = ?");
          $stmt->execute([$area['id_area']]);
          while ($mesa = $stmt->fetch(PDO::FETCH_ASSOC)):
          ?>
            <div class="mesa-card" data-id="<?= $mesa['id_mesa'] ?>">
              <h4><i class="fa-solid fa-chair"></i> <?= htmlspecialchars($mesa['nombre']) ?></h4>
              <div class="mesa-actions">

                <!-- Editar Mesa -->
                <button type="button"
                        class="btn-icon edit"
                        data-modal-target="#editMesaModal"
                        data-id-mesa="<?= $mesa['id_mesa'] ?>"
                        data-id-area="<?= $area['id_area'] ?>"
                        data-nombre-mesa="<?= htmlspecialchars($mesa['nombre']) ?>"
                        title="Editar mesa">
                        <i class="fa-solid fa-pen"></i>
                </button>

                <!-- Eliminar Mesa -->
                <a href="../php/mesa/MesaController.php?accion=eliminar&id_mesa=<?= $mesa['id_mesa'] ?>"
                   data-confirm="¿Eliminar esta mesa?"
                   class="btn-icon delete">
                   <i class="fa-solid fa-trash"></i>
                </a>

                <!-- Ver QR -->
                <button type="button" 
                        class="btn-icon qr"
                        data-modal-target="#qrModal"
                        data-id-mesa="<?= $mesa['id_mesa'] ?>"
                        title="Ver QR">
                        <i class="fa-solid fa-qrcode"></i>
                </button>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- 🔹 MODAL QR -->
  <div id="qrModal" class="modal" style="display:none;">
    <div class="modal-content">
      <button class="close">&times;</button>
      <div id="qrModalContent" class="qr-content">
        <p>Cargando QR...</p>
      </div>
    </div>
  </div>

  <!-- 🔹 MODAL EDITAR ÁREA -->
  <div id="editAreaModal" class="modal" style="display:none;">
    <div class="modal-content">
      <button class="close">&times;</button>
      <h3><i class="fa-solid fa-pen-to-square"></i> Editar área</h3>

      <form id="formEditArea" action="../php/area/AreaController.php" method="POST" data-loader>
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id_area" id="editAreaId">

        <label for="editAreaName">Nuevo nombre del área</label>
        <input type="text" name="nombre_area" id="editAreaName" required class="input-text">

        <div class="modal-actions">
          <button type="submit" class="btn-primary">
            <i class="fa-solid fa-save"></i> Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- 🔹 MODAL EDITAR MESA -->
  <div id="editMesaModal" class="modal" style="display:none;">
    <div class="modal-content">
      <button class="close">&times;</button>
      <h3><i class="fa-solid fa-pen-to-square"></i> Editar Mesa</h3>

      <form id="formEditMesa" action="../php/mesas/MesaController.php" method="POST" data-loader>
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id_mesa" id="editMesaId">
        <input type="hidden" name="id_area" id="editMesaAreaId">

        <label for="editMesaName">Nuevo nombre de la mesa</label>
        <input type="text" name="nombre_mesa" id="editMesaName" required class="input-text">

        <div class="modal-actions">
          <button type="submit" class="btn-primary">
            <i class="fa-solid fa-save"></i> Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- 🔹 SCRIPTS -->
  <script src="../js/edit.js"></script>
  <script src="/ProjectDelix/public/js/modal.js"></script>
  <script src="../js/qr_modal.js"></script>
  <script src="../js/mesas.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="/ProjectDelix/public/js/alert.js"></script>
  <script src="../js/areas.js"></script>

</body>
</html>
