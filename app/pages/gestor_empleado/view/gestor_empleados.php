<?php
require_once __DIR__ . '/../../../middleware/session_guard.php';
protectPage(); // Asegura que el usuario esté logueado

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['usuario']['id'] ?? null;

if (!$userId) {
    die("⚠️ No se encontró el ID de usuario en la sesión.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestor de Empleados | Delix</title>
  <link rel="stylesheet" href="../css/gestor_empleados.css" />
</head>
<body>
  <div class="page-wrapper">
    <!-- 🔹 Header -->
    <header class="main-header">
      <h1>Gestor de Empleados</h1>
    </header>

    <!-- 🔐 Código dinámico -->
    <section class="codigo-dinamico">
      <div class="codigo-card">
        <div class="codigo-top">
          <h2>Código Dinámico de Acceso</h2>
          <button id="nuevoCodigo" class="btn btn-outline">Regenerar</button>
        </div>

        <div class="codigo-body">
          <div class="codigo-left">
            <p class="codigo-label">Código actual</p>
            <div class="codigo-box">
              <span id="codigoDinamico">••••••••</span>
              <button id="copiarCodigo" class="btn btn-copy">Copiar</button>
            </div>
            <p id="estadoCodigo" class="estado activo">Código activo</p>
          </div>

          <!-- ⏱️ Timer circular -->
          <div class="codigo-timer">
            <svg viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="45" class="bg" />
              <circle cx="50" cy="50" r="45" class="progress" />
            </svg>
            <div id="timerText" class="timer-text">60s</div>
          </div>
        </div>
      </div>
    </section>

    <!-- 📋 Tabla de empleados -->
    <section class="tabla-wrapper">
      <table id="tablaEmpleados" class="tabla-empleados">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
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

  <!-- 🧩 Modal para asignar roles -->
  <div id="modalRoles" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h3>Asignar roles al empleado</h3>
      <form id="formRoles">
        <input type="hidden" id="empleadoId">
        
        <div id="rolesContainer" class="roles-container">
          <!-- Aquí se inyectarán los checkboxes dinámicamente -->
        </div>
        <br>
        <div class="modal-actions">
          <button type="button" class="btn btn-outline close">Cancelar</button>
          <button id="guardarRoles" class="btn btn-primary">Guardar</button>

        </div>
      </form>
    </div>
  </div>

  <!-- 🔧 Variables globales -->
  <script>
    // ID del usuario actual logueado (inyectado desde PHP)
    const userId = <?= json_encode($_SESSION['usuario']['id'] ?? null) ?>;
    if (!userId) {
      console.error("⚠️ No se encontró el ID del usuario en la sesión.");
    } else {
      console.log("👤 Usuario logueado ID:", userId);
    }
  </script>

  <!-- 📜 JS logic -->
  <script defer src="../js/empleados.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="/DelixSystem/public/js/alert.js"></script>
  <script src="/DelixSystem/public/js/modal.js"></script>
</body>
</html>
