<?php
require_once __DIR__ . '/../../../shared/bootstrap/employee_ui_bootstrap.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Actividades | Delix</title>
    <link rel="stylesheet" href="../css/historial.css">
</head>

<body>

<div class="historial-wrapper">

    <!-- 🔹 TÍTULO PRINCIPAL -->
    <header class="header">
        <h1>📜 Historial de Actividades</h1>
        <p class="sub">Visualiza y analiza todas las acciones realizadas dentro del sistema.</p>
    </header>

    <!-- 🔍 FILTROS -->
    <section class="filtros">

        <div class="filtro">
            <label>Usuario</label>
            <select id="filtroUsuario">
                <option value="">Todos</option>
            </select>
        </div>

        <div class="filtro">
            <label>Gestor</label>
            <select id="filtroGestor">
                <option value="">Todos</option>
                <option value="areas">Áreas</option>
                <option value="mesas">Mesas</option>
                <option value="empleados">Empleados</option>
                <option value="ordenes">Órdenes</option>
                <option value="productos">Productos</option>
            </select>
        </div>

        <div class="filtro">
            <label>Acción</label>
            <select id="filtroAccion">
                <option value="">Todas</option>
                <option value="crear">Crear</option>
                <option value="editar">Editar</option>
                <option value="eliminar">Eliminar</option>
                <option value="ordenar">Ordenar</option>
                <option value="login">Login</option>
            </select>
        </div>

        <div class="filtro">
            <label>Fecha</label>
            <input type="date" id="filtroFecha">
        </div>

        <button id="btnFiltrar" class="btn-filtrar">Aplicar filtros</button>
    </section>

    <!-- 🧾 TABLA HISTORIAL -->
    <section class="tabla-section">

        <table class="tabla-historial" id="tablaHistorial">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Gestor</th>
                    <th>Acción</th>
                    <th>Detalles</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <!-- LÍNEAS DINÁMICAS CARGADAS DESDE historial.js -->
            </tbody>
        </table>

    </section>

</div>

<script src="../js/historial.js"></script>

</body>
</html>
