<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestor de Empleados | Delix</title>
  <link rel="stylesheet" href="../css/gestor_empleados.css" />
  <script defer src="../js/empleados.js"></script>
</head>
<body>
  <section class="container">

    <!-- ✅ TARJETA DEL CÓDIGO DINÁMICO -->
    <section class="codigo-dinamico-card">
      <div class="codigo-header">
        <h2>🔐 Clave Dinámica de Acceso</h2>
        <p>Comparte esta clave con tus empleados para que puedan unirse a tu cuenta.</p>
      </div>

      <div class="codigo-body">
        <div class="codigo-display">
          <span id="codigoDinamico" class="codigo">••••••••</span>
          <button id="copiarCodigo" class="btn btn-copiar">📋 Copiar</button>
        </div>

        <div class="codigo-timer">
          <svg class="countdown-ring" width="80" height="80">
            <circle class="ring-bg" cx="40" cy="40" r="34" />
            <circle class="ring-progress" cx="40" cy="40" r="34" />
          </svg>
          <div class="timer-text" id="timerText">60s</div>
        </div>
      </div>

      <div class="codigo-footer">
        <button id="nuevoCodigo" class="btn btn-nuevo">🔄 Generar nuevo código</button>
        <p id="estadoCodigo" class="estado">Código activo</p>
      </div>
    </section>
    <!-- FIN TARJETA -->

    <!-- 🧍 GESTOR DE EMPLEADOS -->
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
          <!-- JS insertará empleados aquí -->
        </tbody>
      </table>
    </section>
  </section>

  <!-- Modal CRUD de empleado -->
  <div id="modalEmpleado" class="modal hidden">
    <div class="modal-content">
      <h2 id="modalTitle">Nuevo Empleado</h2>
      <form id="formEmpleado">
        <input type="hidden" name="id" id="empleadoId" />

        <label>Nombre Completo</label>
        <input type="text" name="nombre" id="nombre" required />

        <label>Correo</label>
        <input type="email" name="correo" id="correo" required />

        <label>Documento</label>
        <input type="text" name="documento" id="documento" required />

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
</body>
</html>
