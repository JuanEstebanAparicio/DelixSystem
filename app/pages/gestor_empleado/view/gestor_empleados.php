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
  <div class="page-wrapper">
    <header class="main-header">
      <h1>👥 Gestor de Empleados</h1>
      <button id="btnAddEmpleado" class="btn btn-primary">+ Agregar Empleado</button>
    </header>

    <!-- 🌟 BLOQUE DE CÓDIGO DINÁMICO -->
    <section class="codigo-dinamico">
      <div class="codigo-card">
        <div class="card-header">
          <div>
            <h2>🔐 Código Dinámico</h2>
            <p>Compártelo con tus empleados para que puedan acceder a tu restaurante.</p>
          </div>
          <button id="nuevoCodigo" class="btn btn-nuevo">🔄 Regenerar</button>
        </div>

        <div class="codigo-body">
          <div class="codigo-box">
            <span id="codigoDinamico">••••••••</span>
            <button id="copiarCodigo" class="btn btn-copiar">📋 Copiar</button>
          </div>

          <div class="timer-box">
            <div class="ring">
              <svg viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" class="bg" />
                <circle cx="50" cy="50" r="45" class="progress" />
              </svg>
              <div id="timerText" class="timer-text">60s</div>
            </div>
            <p id="estadoCodigo" class="estado activo">Código activo</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 🧾 TABLA DE EMPLEADOS -->
    <section class="tabla-empleados-wrapper">
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
        <tbody></tbody>
      </table>
    </section>
  </div>

  <!-- Modal CRUD -->
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
</body>
</html>
