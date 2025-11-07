<?php
// DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once __DIR__ . '/../../../shared/bootstrap/employee_ui_bootstrap.php'; // carga header si es empleado
require_once __DIR__ . '/../../../middleware/employee_extended_guard.php';

// Solo los empleados con rol de administrador o supervisor pueden acceder
$usuario = employeeExtendedGuard(['ADMIN_LOCAL', 'SUPERVISOR']);

// ✅ Protege el acceso (propietario o empleado)


// 🧠 Datos base
$userId = $usuario['id'];
$nombreUsuario = $usuario['nombre'];
$tipoUsuario = $usuario['tipo'];
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

<section class="tabla-wrapper">
  <table id="tablaEmpleados" class="tabla-empleados">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Documento</th>
        <th>Rol</th>
        <th>Estado</th>
        <th>Fecha de Registro</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</section>


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
          <button id="guardarRoles" type="button" class="btn btn-primary">Guardar</button>




        </div>
      </form>
    </div>
  </div>

  <!-- 🔧 Variables globales -->
  <script>
  const userId = <?= json_encode($usuario['id'] ?? null) ?>;
  const tipoUsuario = <?= json_encode($usuario['tipo'] ?? 'desconocido') ?>;
</script>


  <!-- 📜 JS logic -->
  <script defer src="../js/empleados.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="/DelixSystem/public/js/alert.js"></script>
  <script src="/DelixSystem/public/js/modal.js"></script>

  <script>
// ========================================
// 🧩 Verificación activa de sesión empleado
// ========================================
setInterval(async () => {
  try {
    const res = await fetch("../../php/employee/EmpleadoSessionCheck.php", { cache: "no-store" });
    const data = await res.json();

    // 🔎 Mostrar en consola para depuración
    console.log("[EmpleadoSessionCheck]", data);

    // 🚨 Si el servidor responde que ya no está activo...
    if (!data.active) {
      let msg = "Tu cuenta ha sido eliminada o desactivada.";
      if (data.reason === "offline") msg = "Has sido desconectado por un administrador.";
      if (data.reason === "deleted") msg = "Tu cuenta ha sido eliminada del sistema.";
      if (data.reason === "no_session") msg = "Tu sesión ya no es válida.";

      // 🚨 Mostrar alerta visual si tienes librería Alerts (o usar alert())
      if (typeof Alerts !== "undefined" && Alerts.error) {
        Alerts.error(msg, "Sesión finalizada");
      } else {
        alert(msg);
      }

      // 🔁 Redirigir tras breve espera
      setTimeout(() => {
        window.location.href = "/DelixSystem/src/auth/logout_empleado.php";
      }, 2000);
    }
  } catch (err) {
    console.error("[EmpleadoSessionCheck] Error de verificación:", err);
  }
}, 5000); // cada 5 segundos
</script>

</body>
</html>
