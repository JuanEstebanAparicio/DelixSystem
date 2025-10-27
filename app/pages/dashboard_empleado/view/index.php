<?php
// File: /DelixSystem/app/pages/dashboard_empleado/view/index.php
require_once __DIR__ . '/../../../middleware/employee_guard.php';
protectEmpleado(); // asegura que solo empleados accedan

if (session_status() === PHP_SESSION_NONE) session_start();
$empleado = $_SESSION['empleado_auth'] ?? ['full_name' => 'Empleado', 'email' => ''];

// Why: keep display-safe values
$displayName = htmlspecialchars($empleado['full_name']);
$displayEmail = htmlspecialchars($empleado['email']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard Empleado — DelixSystem</title>

  <!-- Tailwind (quick, production-ready CDN) -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Icons (Heroicons via CDN) -->
  <script src="https://unpkg.com/feather-icons"></script>

  <style>
    /* Small custom polish */
    :root{
      --accent:#0ea5a4;
      --accent-dark:#0b9b97;
    }
    .sidebar {
      min-width: 260px;
      max-width: 260px;
    }
    .content-scroll { max-height: calc(100vh - 96px); overflow: auto; }
    .card-shadow { box-shadow: 0 6px 18px rgba(16,24,40,0.08); }
    .status-dot { width:10px;height:10px;border-radius:9999px;display:inline-block;margin-right:8px; }
    .dot-online { background: #16a34a; }
    .dot-offline { background: #6b7280; }
    /* subtle new row highlight (for future) */
    .row-new { animation: rowPulse 1.2s ease; }
    @keyframes rowPulse {
      from { background: rgba(14,165,164,0.06); }
      to { background: transparent; }
    }
  </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

  <!-- HEADER -->
  <header class="fixed top-0 left-0 right-0 bg-white border-b z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center gap-4">
          <button id="toggleSidebar" class="p-2 rounded-md hover:bg-gray-100" aria-label="Toggle sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <a href="#" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white font-bold">D</div>
            <div>
              <div class="text-sm font-semibold text-gray-900">DelixSystem</div>
              <div class="text-xs text-gray-500">Panel de Empleado</div>
            </div>
          </a>
        </div>

        <div class="flex items-center gap-4">
          <button id="openProfile" class="flex items-center gap-3 bg-white border px-3 py-1.5 rounded-lg hover:shadow-sm">
            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm text-gray-700">
              <?= strtoupper(substr($displayName,0,1)) ?>
            </div>
            <div class="text-left">
              <div class="text-sm font-medium"><?= $displayName ?></div>
              <div class="text-xs text-gray-500"><?= $displayEmail ?></div>
            </div>
          </button>
        </div>
      </div>
    </div>
  </header>

  <div class="pt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex gap-6">

        <!-- SIDEBAR -->
        <aside id="sidebar" class="sidebar hidden md:block bg-white rounded-xl card-shadow p-4 sticky top-20 self-start">
          <nav class="space-y-3">
            <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50">
              <svg class="h-5 w-5 text-teal-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
              <span class="text-sm font-medium">Dashboard</span>
            </a>

            <a href="/DelixSystem/app/pages/gestion_pedidos/view/index.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50">
              <svg class="h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6"/></svg>
              <span class="text-sm font-medium">Pedidos</span>
            </a>

            <a href="/DelixSystem/app/pages/gestion_mesas/view/resumen_mesas.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50">
              <svg class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
              <span class="text-sm font-medium">Mesas</span>
            </a>

            <a href="/DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50">
              <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span class="text-sm font-medium">Gestor Empleados</span>
            </a>

            <a href="/DelixSystem/app/pages/dashboard_empleado/view/index.php#support" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50">
              <svg class="h-5 w-5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-6.364 6.364M6.343 17.657l6.364-6.364"/></svg>
              <span class="text-sm font-medium">Soporte</span>
            </a>
          </nav>
        </aside>

        <!-- MAIN -->
        <main class="flex-1">
          <!-- Welcome + KPIs -->
          <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-2xl card-shadow">
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="text-lg font-semibold">Bienvenido</h3>
                  <p class="text-sm text-gray-500">Buen trabajo hoy, <?= $displayName ?>.</p>
                </div>
                <div class="text-right">
                  <div class="text-sm text-gray-400">Turno</div>
                  <div class="text-lg font-semibold">Mañana</div>
                </div>
              </div>

              <div class="mt-4 border-t pt-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white font-bold">D</div>
                <div>
                  <div class="text-sm text-gray-500">Cargo</div>
                  <div class="font-medium">Empleado</div>
                </div>
              </div>
            </div>

            <div class="bg-white p-6 rounded-2xl card-shadow">
              <div class="flex justify-between items-center">
                <div>
                  <h4 class="text-sm text-gray-500">Pedidos Activos</h4>
                  <div id="kpi-pedidos" class="text-2xl font-bold mt-1">—</div>
                </div>
                <div class="text-right">
                  <button id="btn-refresh-kpis" class="px-3 py-1 bg-gray-100 rounded-lg text-sm">Actualizar</button>
                </div>
              </div>
              <p class="text-xs text-gray-400 mt-3">Número de pedidos asignados a tu estación.</p>
            </div>

            <div class="bg-white p-6 rounded-2xl card-shadow">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm text-gray-500">Estado</h4>
                  <div class="flex items-center gap-2 mt-1">
                    <span id="statusDot" class="status-dot dot-offline"></span>
                    <span id="statusText" class="text-sm font-medium">Desconectado</span>
                  </div>
                </div>
                <div>
                  <a href="/DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php" class="text-sm text-teal-600 font-semibold">Gestor</a>
                </div>
              </div>
              <p class="text-xs text-gray-400 mt-3">Tu conexión al sistema.</p>
            </div>
          </section>

          <!-- Resumen rápido / tabla compacta -->
          <section class="bg-white p-6 rounded-2xl card-shadow mb-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold">Resumen rápido</h3>
              <div class="flex items-center gap-3">
                <a class="text-sm text-gray-500 hover:text-gray-900" href="/DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php">Ver lista completa</a>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div class="col-span-2">
                <table class="min-w-full text-sm">
                  <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                      <th class="py-3">Nombre</th>
                      <th class="py-3">Correo</th>
                      <th class="py-3">Documento</th>
                      <th class="py-3">Rol</th>
                    </tr>
                  </thead>
                  <tbody id="miniEmployees" class="text-sm">
                    <!-- JS will populate small sample (non-destructive) -->
                    <tr><td colspan="4" class="py-6 text-center text-gray-400">Cargando...</td></tr>
                  </tbody>
                </table>
              </div>

              <div class="col-span-1 space-y-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                  <div class="text-xs text-gray-500">Atajos</div>
                  <div class="mt-3 flex flex-col gap-2">
                    <a href="/DelixSystem/app/pages/gestion_pedidos/view/index.php" class="px-3 py-2 bg-white rounded-md border hover:shadow-sm">Ir a Pedidos</a>
                    <a href="/DelixSystem/app/pages/gestion_mesas/view/resumen_mesas.php" class="px-3 py-2 bg-white rounded-md border hover:shadow-sm">Ver Mesas</a>
                    <a href="/DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php" class="px-3 py-2 bg-white rounded-md border hover:shadow-sm">Empleados</a>
                  </div>
                </div>

                <div class="bg-white p-4 rounded-lg">
                  <div class="text-xs text-gray-500">Soporte</div>
                  <div class="mt-3 text-sm text-gray-700">¿Problemas? Contacta a tu administrador o envía un ticket.</div>
                </div>
              </div>
            </div>
          </section>

          <!-- Footer small -->
          <footer id="support" class="text-center text-xs text-gray-400 py-6">
            © <?= date('Y') ?> DelixSystem — Sistema de gestión para restaurantes
          </footer>
        </main>
      </div>
    </div>
  </div>

  <!-- PROFILE MODAL (hidden) -->
  <div id="profileModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-2xl w-11/12 sm:w-96 p-6 card-shadow">
      <div class="flex justify-between items-start">
        <div>
          <h3 class="text-lg font-semibold"><?= $displayName ?></h3>
          <p class="text-sm text-gray-500"><?= $displayEmail ?></p>
        </div>
        <button id="closeProfile" class="text-gray-500">✕</button>
      </div>

      <div class="mt-4 space-y-3 text-sm">
        <div><strong>ID:</strong> <?= htmlspecialchars($empleado['id'] ?? '') ?></div>
        <div><strong>Restaurante:</strong> <?= htmlspecialchars($empleado['restaurant_name'] ?? '') ?></div>
        <div><strong>Documento:</strong> <?= htmlspecialchars($empleado['document'] ?? '') ?></div>
      </div>

      <div class="mt-6 flex gap-2">
        <form action="/DelixSystem/src/auth/logout_empleado.php" method="POST" class="w-full">
          <button type="submit" class="w-full bg-red-500 text-white py-2 rounded-lg">Cerrar sesión</button>
        </form>
        <button id="closeProfile2" class="w-1/3 bg-gray-100 rounded-lg">Cerrar</button>
      </div>
    </div>
  </div>

  <!-- Minimal JS: toggle sidebar, profile modal and populate small list -->
  <script>
    // UI interactions
    document.getElementById('toggleSidebar').addEventListener('click', () => {
      const sb = document.getElementById('sidebar');
      if (!sb) return;
      sb.classList.toggle('hidden');
    });

    const openProfile = document.getElementById('openProfile');
    const profileModal = document.getElementById('profileModal');
    const closeProfile = document.getElementById('closeProfile');
    const closeProfile2 = document.getElementById('closeProfile2');
    if (openProfile) openProfile.addEventListener('click', () => profileModal.classList.remove('hidden'));
    if (closeProfile) closeProfile.addEventListener('click', () => profileModal.classList.add('hidden'));
    if (closeProfile2) closeProfile2.addEventListener('click', () => profileModal.classList.add('hidden'));
    if (profileModal) profileModal.addEventListener('click', (e) => { if (e.target === profileModal) profileModal.classList.add('hidden'); });

    // Small fetch to show mini list and status (non-critical; errors are silenced)
    async function loadMiniData(){
      try {
        // Ajusta la ruta si tu controller está en otra ubicación
        const res = await fetch('/DelixSystem/app/pages/gestor_empleado/php/employee/EmpleadoListController.php', {cache: 'no-store'});
        const json = await res.json();
        if (json.status !== 'success') throw new Error('No data');

        // KPIs
        const pedidosKpi = document.getElementById('kpi-pedidos');
        // ejemplo: contar empleados como placeholder (conecta con tu endpoint real)
        pedidosKpi.textContent = (Math.floor(Math.random()*8)+1) + ' pedidos';

        // mini employees
        const tbody = document.getElementById('miniEmployees');
        tbody.innerHTML = '';
        const sample = json.data.slice(0,6);
        sample.forEach(emp => {
          const tr = document.createElement('tr');
          tr.className = 'border-b';
          tr.innerHTML = `
            <td class="py-3">${emp.full_name}</td>
            <td class="py-3">${emp.email}</td>
            <td class="py-3">${emp.documento ?? ''}</td>
            <td class="py-3">${emp.role ?? ''}</td>
          `;
          tbody.appendChild(tr);
        });

        // status dot (check current empleado)
        const currentId = <?= json_encode($empleado['id'] ?? null) ?>;
        const current = json.data.find(e => String(e.id) === String(currentId));
        const statusDot = document.getElementById('statusDot');
        const statusText = document.getElementById('statusText');
        if (current && current.is_online == true) {
          statusDot.classList.remove('dot-offline'); statusDot.classList.add('dot-online');
          statusText.textContent = 'Conectado';
        } else {
          statusDot.classList.remove('dot-online'); statusDot.classList.add('dot-offline');
          statusText.textContent = 'Desconectado';
        }

      } catch (err) {
        // Non-critical: show friendly placeholders
        const tbody = document.getElementById('miniEmployees');
        if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="py-6 text-center text-gray-400">No fue posible cargar datos rápidos</td></tr>';
      }
    }

    // refresh button
    document.getElementById('btn-refresh-kpis').addEventListener('click', () => loadMiniData());
    // initial load
    loadMiniData();
  </script>

  <script>feather.replace()</script>
</body>
</html>
