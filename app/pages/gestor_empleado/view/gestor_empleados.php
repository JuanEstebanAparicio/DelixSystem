<!-- gestor_empleado/view/empleados.php -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestor de Empleados | Delix</title>
  <link rel="stylesheet" href="../css/gestor_empleados.css" />
</head>
<body>
  <section class="container">
    <header class="header">
      <h1>👥 Gestor de Empleados</h1>
      <button id="btnAddEmpleado" class="btn btn-primary">+ Agregar Empleado</button>
    </header>

    <section class="table-container">
      <table id="tablaEmpleados" class="tabla-empleados">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre Completo</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Fecha de Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- Aquí JS insertará los empleados -->
        </tbody>
      </table>
    </section>
  </section>

  <!-- Modal -->
  <div id="modalEmpleado" class="modal hidden">
    <div class="modal-content">
      <h2 id="modalTitle">Nuevo Empleado</h2>
      <form id="formEmpleado">
        <input type="hidden" name="id" id="empleadoId">

        <label>Nombre Completo</label>
        <input type="text" name="nombre" id="nombre" required>

        <label>Correo</label>
        <input type="email" name="correo" id="correo" required>

        <label>Documento</label>
        <input type="text" name="documento" id="documento" required>

        <label>Rol</label>
        <select name="rol" id="rol" required>
          <option value="">Seleccionar rol...</option>
          <option value="Cocinero">Cocinero</option>
          <option value="Mesero">Mesero</option>
          <option value="Cajero">Cajero</option>
          <option value="Supervisor">Supervisor</option>
        </select>

        <div class="modal-actions">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" id="btnCancelar" class="btn btn-secondary">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../js/empleados.js"></script>
</body>
</html>
