<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once __DIR__ . '/../../../shared/bootstrap/employee_ui_bootstrap.php'; // carga header si es empleado
require_once __DIR__ . '/../../../middleware/employee_extended_guard.php';

$usuario = employeeExtendedGuard(['ADMIN_LOCAL','SUPERVISOR']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Actividades | Delix</title>

    <!-- CSS principal -->
    <link rel="stylesheet" href="../css/historial.css">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

  <div class="resumen-btn-container" style="text-align:right; margin: 15px 30px;">
    <a href="../../../../../DelixSystem/app/pages/dashboard_propietario/view/index.php" class="btn-summary" title="Ver resumen general">
      <i class="fa-solid fa-chart-pie"></i> Volver al Dashboard
    </a>
  </div>
  
<br><br><br>

<div class="historial-wrapper fade-in">

    <!-- 🌙 MODO OSCURO / CLARO -->
    <div class="theme-toggle">
        <i class="fas fa-sun"></i>

        <label class="switch">
            <input type="checkbox" id="themeSwitch">
            <span class="slider round"></span>
        </label>

        <i class="fas fa-moon"></i>
    </div>

    <!-- 🔹 TÍTULO PRINCIPAL -->
    <header class="header fade-in">
        <h1>📜 Historial de Actividades</h1>
        <p class="sub">Visualiza y analiza todas las acciones realizadas dentro del sistema.</p>
    </header>


    <!-- 📊 MINI GRÁFICO DE ACTIVIDAD -->
    <section class="actividad-card fade-in">
        <h3><i class="fa-solid fa-chart-line"></i> Actividad reciente</h3>

        <div class="mini-chart">
            <div class="bar" style="height: 20%"></div>
            <div class="bar" style="height: 45%"></div>
            <div class="bar" style="height: 70%"></div>
            <div class="bar" style="height: 30%"></div>
            <div class="bar" style="height: 60%"></div>
            <div class="bar" style="height: 90%"></div>
        </div>
    </section>


    <!-- 🔍 FILTROS -->
    <section class="filtros fade-in">

        <div class="filtro">
            <label><i class="fa-solid fa-user"></i> Usuario</label>
            <select id="filtroUsuario">
                <option value="">Todos</option>
            </select>
        </div>

        <div class="filtro">
            <label><i class="fa-solid fa-layer-group"></i> Gestor</label>
            <select id="filtroGestor">
                <option value="">Todos</option>
                <option value="areas">Áreas</option>
                <option value="mesas">Mesas</option>
                <option value="empleados">Empleados</option>
                <option value="inventario">Inventario</option>
                <option value="roles">Roles</option>
                
            </select>
        </div>

        <div class="filtro">
            <label><i class="fa-solid fa-wand-magic-sparkles"></i> Acción</label>
            <select id="filtroAccion">
                <option value="">Todas</option>
                <option value="crear">Crear</option>
                <option value="editar">Editar</option>
                <option value="eliminar">Eliminar</option>
                <option value="ordenar">Ordenar</option>
            </select>
        </div>

        <div class="filtro">
            <label><i class="fa-solid fa-calendar-day"></i> Fecha</label>
            <input type="date" id="filtroFecha">
        </div>

        <button id="btnFiltrar" class="btn-filtrar">
            <i class="fa-solid fa-filter"></i> Aplicar filtros
        </button>
        <button id="btnReset" class="btn-filtrar">
            <i class="fa-solid fa-rotate-left"></i> Resetear filtros
        </button>

    </section>


    <!-- 🧾 TABLA HISTORIAL -->
    <section class="tabla-section fade-in">

        <table class="tabla-historial" id="tablaHistorial">
            <thead>
                <tr>
                    <th><i class="fa-solid fa-user"></i> Usuario</th>
                    <th><i class="fa-solid fa-layer-group"></i> Gestor</th>
                    <th><i class="fa-solid fa-bolt"></i> Acción</th>
                    <th><i class="fa-solid fa-info-circle"></i> Detalles</th>
                    <th><i class="fa-solid fa-calendar-alt"></i> Fecha</th>
                </tr>
            </thead>
            <tbody>
                <!-- Filas generadas por historial.js -->
            </tbody>
        </table>

    </section>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- JS -->
<script src="../js/historial.js"></script>
<script src="../../../shared/auditoria/diccionario_auditoria.js"></script>
</body>
</html>
