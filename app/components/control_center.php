<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$empleado = $_SESSION['empleado_auth'] ?? ['full_name' => 'Empleado', 'email' => ''];
$displayName = htmlspecialchars($empleado['full_name']);
$displayEmail = htmlspecialchars($empleado['email']);
?>

<div id="controlCenter" class="fixed inset-0 bg-black/30 backdrop-blur-md hidden z-50 transition-opacity duration-500 ease-in-out opacity-0">
  <div id="controlPanel" class="absolute right-0 top-0 h-full w-full sm:w-[420px] bg-white shadow-2xl transform translate-x-full transition-transform duration-500 ease-in-out rounded-l-3xl flex flex-col">

    <!-- Header del panel -->
    <div class="flex justify-between items-center border-b p-5">
      <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
        <i class="ri-grid-fill text-emerald-500 text-xl"></i> Centro de Control
      </h2>
      <button id="closeControlCenter" class="text-gray-500 hover:text-gray-700 text-xl">
        <i class="ri-close-line"></i>
      </button>
    </div>

    <!-- Perfil -->
    <div class="flex items-center gap-3 p-5 border-b bg-gray-50">
      <div class="w-12 h-12 bg-emerald-500 text-white flex items-center justify-center rounded-full font-semibold text-lg shadow-sm">
        <?= strtoupper(substr($displayName, 0, 1)) ?>
      </div>
      <div>
        <div class="font-semibold text-gray-800"><?= $displayName ?></div>
        <div class="text-sm text-gray-500"><?= $displayEmail ?></div>
      </div>
    </div>

    <!-- Contenido -->
    <div class="flex-1 overflow-y-auto p-6">
      <p class="text-xs text-gray-500 uppercase mb-4 font-medium tracking-wider">Gestores disponibles</p>

      <div class="grid grid-cols-2 gap-4">
        <a href="/DelixSystem/app/pages/gestion_pedidos/view/index.php" class="group card-control bg-emerald-50 hover:bg-emerald-100">
          <i class="ri-shopping-bag-3-fill text-emerald-600 text-3xl group-hover:scale-110 transition-transform"></i>
          <span>Pedidos</span>
        </a>

        <a href="/DelixSystem/app/pages/gestion_mesas/view/gestion_mesas.php" class="group card-control bg-sky-50 hover:bg-sky-100">
          <i class="ri-restaurant-line text-sky-500 text-3xl group-hover:scale-110 transition-transform"></i>
          <span>Mesas</span>
        </a>

        <a href="/DelixSystem/app/pages/dishes_manager/view/index.php" class="group card-control bg-amber-50 hover:bg-amber-100">
          <i class="ri-restaurant-2-fill text-amber-500 text-3xl group-hover:scale-110 transition-transform"></i>
          <span>Gestor Menú</span>
        </a>

        <a href="/DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php" class="group card-control bg-indigo-50 hover:bg-indigo-100">
          <i class="ri-team-fill text-indigo-500 text-3xl group-hover:scale-110 transition-transform"></i>
          <span>Empleados</span>
        </a>

        <a href="/DelixSystem/app/pages/inventory/view/ingredient_manager.php" class="group card-control bg-rose-50 hover:bg-rose-100">
          <i class="ri-archive-2-fill text-rose-500 text-3xl group-hover:scale-110 transition-transform"></i>
          <span>Inventario</span>
        </a>

        <a href="/DelixSystem/app/pages/reportes/view/index.php" class="group card-control bg-fuchsia-50 hover:bg-fuchsia-100">
          <i class="ri-bar-chart-2-fill text-fuchsia-500 text-3xl group-hover:scale-110 transition-transform"></i>
          <span>Reportes</span>
        </a>
        
        <a href="/DelixSystem/app/pages/dashboard_empleado/view/index.php" class="group card-control bg-cyan-50 hover:bg-cyan-100">
          <i class="ri-dashboard-fill text-cyan-600 text-3xl group-hover:scale-110 transition-transform"></i>
          <span>Dashboard</span>
        </a>

        <a href="/DelixSystem/app/pages/historial/view/historial.php" class="group card-control bg-orange-50 hover:bg-orange-100">
          <i class="ri-history-line text-orange-500 text-3xl group-hover:scale-110 transition-transform"></i>
          <span>Historial</span>
        </a>
      </div>
    </div>

    <!-- Logout -->
    <div class="border-t p-5">
      <form action="/DelixSystem/src/auth/logout_empleado.php" method="POST" class="w-full">
        <button type="submit" class="w-full py-2.5 rounded-lg bg-red-500 text-white font-medium hover:bg-red-600 transition flex items-center justify-center gap-2">
          <i class="ri-logout-box-line text-lg"></i> Cerrar sesión
        </button>
      </form>
    </div>

  </div>
</div>
