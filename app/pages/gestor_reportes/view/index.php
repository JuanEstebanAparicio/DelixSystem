<?php
// 🔐 Cargar guardia con validación de roles
require_once __DIR__ . '/../../../middleware/employee_extended_guard.php';

// Solo los empleados con ciertos roles pueden acceder.
// Los propietarios pasan automáticamente.
$usuario = employeeExtendedGuard(['ADMIN_LOCAL','SUPERVISOR','GESTOR_REPORTES']);

// ================================
// 🔎 Determinar el ID de usuario REAL para reportes
// ================================

require_once __DIR__ . '/../../../config/supabase.php';

if ($usuario['tipo'] === 'propietario') {

    // El propietario usa su propio ID
    $id_usuario = $usuario['id'];

} else {

    // Empleado: obtener el propietario al que pertenece
    $stmt = $conexion->prepare("SELECT user_id FROM employees WHERE id = ?");
    $stmt->execute([$usuario['id']]);
    $id_usuario = $stmt->fetchColumn();

    if (!$id_usuario) {
        die("Error: No se encontró el propietario relacionado al empleado.");
    }
}

// ================================
// 📌 Cargar header y panel correctos
// ================================
if ($usuario['tipo'] === 'propietario') {

    include __DIR__ . '/../../../components/header_propietario.php';
    include __DIR__ . '/../../../components/control_center_propietario.php';

} else {

    require_once __DIR__ . '/../../../shared/bootstrap/employee_ui_bootstrap.php';

}

// ================================
// 📦 Dependencias
// ================================
require_once __DIR__ . '/../php/ReportController.php';

$reportController = new ReportController($conexion);

// ================================
// 📊 Reportes principales
// ================================
$reportes = $reportController->obtenerReportes($id_usuario);

// ================================
// 🎯 Filtros GET
// ================================
$inicio = $_GET['fecha_inicio'] ?? null;
$fin = $_GET['fecha_fin'] ?? null;
$area = $_GET['area'] ?? null;

// ================================
// 📅 Reporte por rango de fechas
// ================================
$reporteRango = null;

if ($inicio && $fin) {
    $reporteRango = $reportController->obtenerReportePorRango(
        $id_usuario,
        $inicio,
        $fin,
        $area
    );
}

// ================================
// 📈 Otros datos estadísticos
// ================================
$ventas7 = $reportController->ventasUltimos7Dias($id_usuario);
$areas = $reportController->pedidosPorArea($id_usuario);
$ventasMes = $reportController->ventasMesActual($id_usuario);
$topProductos = $reportController->topProductos($id_usuario, 5);

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Delix | Reportes</title>
    <link rel="stylesheet" href="../css/reportes.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/DelixSystem/app/shared/css/globals.css">
    <link rel="stylesheet" href="/DelixSystem/app/shared/css/control_center.css">
</head>

<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar -->
    <aside id="sidebar"
        class="bg-white w-64 shadow-xl flex flex-col justify-between fixed left-0 top-[80px] bottom-0
               rounded-tr-3xl rounded-br-3xl border-r border-gray-200 transform transition-transform duration-300
               -translate-x-full lg:translate-x-0 z-50">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="ri-bar-chart-2-line text-emerald-600"></i> Reportes
            </h2>

            <nav class="flex flex-col gap-3">
                <button class="nav-item active" data-section="resumen">
                    <i class="ri-dashboard-3-line"></i> Resumen general
                </button>
                <button class="nav-item" data-section="rango">
                    <i class="ri-calendar-line"></i> Por rango de fechas
                </button>
                <button class="nav-item" data-section="detalle">
                    <i class="ri-list-unordered"></i> Detalle diario
                </button>
                  <button class="nav-item" data-section="graficas">
                  <i class="ri-bar-chart-fill"></i> Gráficas
                </button>

                <button class="nav-item" data-section="exportar">
                    <i class="ri-download-line"></i> Exportar datos
                </button>            
              
            </nav>
        </div>

        <div class="p-6 border-t text-sm text-gray-500">
            <p>Gestor Delix v1.0</p>
        </div>
    </aside>

    <!-- Contenido -->
    <main id="mainContent" class="flex-1 pt-28 px-8 lg:ml-64 transition-all duration-300">
        <section class="reportes-container">
            
            <div class="reportes-filtros">
                <h1 class="titulo-seccion">📊 Gestor de Reportes</h1>
            </div>

            <!-- SECCIONES -->
            <section id="resumen" class="report-section active">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-6">
                    <div class="report-card bg-daily">
                        <h3>Hoy</h3>
                        <p class="amount"><?= $reportes['daily']['total_orders'] ?> pedidos</p>
                        <p>$<?= number_format($reportes['daily']['total_sales'], 0, ',', '.') ?></p>
                    </div>

                    <div class="report-card bg-weekly">
                        <h3>Últimos 7 días</h3>
                        <p class="amount"><?= $reportes['weekly']['total_orders'] ?> pedidos</p>
                        <p>$<?= number_format($reportes['weekly']['total_sales'], 0, ',', '.') ?></p>
                    </div>

                    <div class="report-card bg-monthly">
                        <h3>Este mes</h3>
                        <p class="amount"><?= $reportes['monthly']['total_orders'] ?> pedidos</p>
                        <p>$<?= number_format($reportes['monthly']['total_sales'], 0, ',', '.') ?></p>
                    </div>
                </div>
            </section>


            <section id="graficas" class="report-section hidden">
    <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
        <i class="ri-bar-chart-2-line text-emerald-600"></i> Gráficas estadísticas
    </h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Ventas últimos 7 días -->
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="font-semibold mb-3">Ventas últimos 7 días</h3>
            <canvas id="chartVentas7Dias" height="150"></canvas>
        </div>

        <!-- Pedidos por área -->
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="font-semibold mb-3">Pedidos por área</h3>
            <canvas id="chartPedidosArea" height="150"></canvas>
        </div>

        <!-- Ventas del mes -->
        <div class="bg-white p-6 rounded-xl shadow-md lg:col-span-2">
            <h3 class="font-semibold mb-3">Ventas del mes</h3>
            <canvas id="chartVentasMes" height="120"></canvas>
        </div>

        <!-- Top productos más vendidos -->
<div class="bg-white p-6 rounded-xl shadow-md lg:col-span-2">
    <h3 class="font-semibold mb-3">Top productos más vendidos</h3>
    <canvas id="chartTopProductos" height="120"></canvas>
</div>


    </div>
</section>




   <section id="rango" class="report-section hidden">
    <div class="bg-white p-6 rounded-2xl shadow-md mb-8">
        <h2 class="text-2xl font-semibold mb-4 text-gray-700 flex items-center gap-2">
            <i class="ri-filter-3-line text-emerald-600"></i> Reporte por rango de fechas
        </h2>

        <form id="filtro-form" class="filtros-form grid grid-cols-1 sm:grid-cols-4 gap-6" method="GET">
            <div class="filtro-item flex flex-col">
                <label for="fecha_inicio" class="text-gray-600 font-medium">Desde:</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="input-filtro">
            </div>

            <div class="filtro-item flex flex-col">
                <label for="fecha_fin" class="text-gray-600 font-medium">Hasta:</label>
                <input type="date" name="fecha_fin" id="fecha_fin" class="input-filtro">
            </div>

            <div class="filtro-item flex flex-col">
                <label for="area" class="text-gray-600 font-medium">Área:</label>
                <select id="area" name="area" class="input-filtro">
                    <option value="">Todas</option>
                    <?php
                    $stmt = $conexion->query("SELECT DISTINCT area FROM orders ORDER BY area ASC");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['area']}'>{$row['area']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="btn-filtrar">Aplicar</button>
            </div>
        </form>
    </div>

    <div class="reportes-container bg-white p-8 rounded-2xl shadow-lg fade-in">
        <p class="text-gray-500">Selecciona un rango de fechas y un área para ver el reporte.</p>
    </div>
</section>


            <section id="detalle" class="report-section hidden">
                <?php
                $detalle = $reportController->obtenerDetalleDiario($id_usuario, $inicio, $fin);
                if (!empty($detalle)):
                ?>
                    <div class="overflow-x-auto">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Pedidos</th>
                                    <th>Total Ventas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalle as $fila): ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['fecha']) ?></td>
                                    <td><?= $fila['total_orders'] ?></td>
                                    <td>$<?= number_format($fila['total_sales'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-resultados">Aún no hay detalle disponible.</p>
                <?php endif; ?>
            </section>
        </section>
    </main>

    <!-- Modal exportar -->
    <div id="exportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-xl shadow-lg w-96 text-center">
            <h3 class="text-xl font-semibold mb-4">Exportar Reportes</h3>
            <p class="text-gray-600 mb-6">Selecciona el formato para descargar tus reportes.</p>
            <div class="flex justify-center gap-4">
                <button class="btn-secondary">PDF</button>
                <button class="btn-secondary">Excel</button>
            </div>
            <button onclick="closeModal('exportModal')" class="mt-6 text-sm text-gray-500 hover:text-gray-700">Cerrar</button>
        </div>
    </div>

    <script src="../js/reportes.js"></script>
    <script src="/DelixSystem/app/shared/js/control_center_propietario.js"></script>
    <script>
const ventas7Dias = <?= json_encode($ventas7) ?>;
const pedidosArea = <?= json_encode($areas) ?>;
const ventasMes = <?= json_encode($ventasMes) ?>;
const topProductos = <?= json_encode($topProductos) ?>;
</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
