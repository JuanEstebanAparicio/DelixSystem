<?php
// DelixSystem/app/components/control_center_propietario.php
if (session_status() === PHP_SESSION_NONE) session_start();

$propietario = $_SESSION['usuario'] ?? [
  'first_name' => 'Propietario',
  'last_name' => '',
  'email' => '',
  'restaurant_name' => 'Mi Restaurante'
];

$nombreCompleto = htmlspecialchars(trim($propietario['first_name'] . ' ' . $propietario['last_name']));
$correo = htmlspecialchars($propietario['email']);
$nombreRestaurante = htmlspecialchars($propietario['restaurant_name']);
?>

<!-- 🧭 Centro de Control del Propietario -->
<div id="controlCenter" 
     class="fixed top-0 right-0 w-full sm:w-[400px] h-full bg-white shadow-2xl z-50 translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">

  <div class="p-6 border-b border-gray-200 flex justify-between items-center">
    <h2 class="text-xl font-semibold text-gray-800">Centro de Control</h2>
    <button id="closeControlCenter" class="text-gray-600 hover:text-emerald-600 text-2xl transition">
      <i class="ri-close-line"></i>
    </button>
  </div>

  <!-- 👤 Perfil del Propietario -->
  <div class="p-6 border-b border-gray-100">
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 flex items-center justify-center bg-emerald-500 text-white rounded-full font-semibold text-lg">
        <?= strtoupper(substr($propietario['first_name'] ?? 'P', 0, 1)) ?>
      </div>
      <div>
        <h3 class="text-lg font-semibold text-gray-800"><?= $nombreCompleto ?></h3>
        <p class="text-sm text-gray-500"><?= $correo ?></p>
        <p class="text-sm text-gray-500 italic"><?= $nombreRestaurante ?></p>
      </div>
    </div>
  </div>

  <!-- ⚙️ Acciones principales -->
<!-- Contenido / Acciones principales -->
<div class="flex-1 overflow-y-auto p-6">
  <p class="text-xs text-gray-500 uppercase mb-4 font-medium tracking-wider">
    Gestores disponibles
  </p>

  <div class="grid grid-cols-2 gap-4">

    <!-- PANEL PRINCIPAL -->
    <a href="/DelixSystem/app/pages/dashboard_propietario/view/index.php" 
       class="group card-control bg-emerald-50 hover:bg-emerald-100">
      <i class="ri-dashboard-line text-emerald-600 text-3xl group-hover:scale-110 transition-transform"></i>
      <span>Panel Principal</span>
    </a>

    <!-- PEDIDOS -->
    <a href="/DelixSystem/app/pages/pedidos/view/listar_pedidos.php" 
       class="group card-control bg-sky-50 hover:bg-sky-100">
      <i class="ri-file-list-3-line text-sky-500 text-3xl group-hover:scale-110 transition-transform"></i>
      <span>Pedidos</span>
    </a>

    <!-- MESAS -->
    <a href="/DelixSystem/app/pages/gestion_mesas/view/gestion_mesas.php" 
       class="group card-control bg-cyan-50 hover:bg-cyan-100">
      <i class="ri-table-2 text-cyan-600 text-3xl group-hover:scale-110 transition-transform"></i>
      <span>Gestor Mesas</span>
    </a>

    <!-- GESTOR MENÚ -->
   <a href="/DelixSystem/app/pages/dishes_manager/view/dishes_manager.php" class="group card-control bg-amber-50 hover:bg-amber-100"> 
    <i class="ri-restaurant-2-fill text-amber-500 text-3xl group-hover:scale-110 transition-transform"></i>
     <span>Gestor Menú</span> 
    </a>

    <!-- INVENTARIO -->
    <a href="/DelixSystem/app/pages/inventory/view/ingredient_manager.php" 
       class="group card-control bg-violet-50 hover:bg-violet-100">
      <i class="ri-archive-drawer-line text-violet-600 text-3xl group-hover:scale-110 transition-transform"></i>
      <span>Gestor de Inventario</span>
    </a>

    <!-- EMPLEADOS -->
    <a href="/DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php" 
       class="group card-control bg-indigo-50 hover:bg-indigo-100">
      <i class="ri-user-3-line text-indigo-600 text-3xl group-hover:scale-110 transition-transform"></i>
      <span>Empleados</span>
    </a>

    <!-- REPORTES -->
    <a href="/DelixSystem/app/pages/gestor_reportes/view/index.php" 
       class="group card-control bg-fuchsia-50 hover:bg-fuchsia-100">
      <i class="ri-bar-chart-2-line text-fuchsia-600 text-3xl group-hover:scale-110 transition-transform"></i>
      <span>Reportes</span>
    </a>

    <!-- PERFIL -->
    <a href="/DelixSystem/app/pages/perfil_propietario/view/index.php" 
       class="group card-control bg-gray-50 hover:bg-gray-100">
      <i class="ri-user-settings-line text-gray-600 text-3xl group-hover:scale-110 transition-transform"></i>
      <span>Mi Perfil</span>
    </a>

    <!-- HISTORIAL -->
    <a href="/DelixSystem/app/pages/historial/view/historial.php"
       class="group card-control bg-orange-50 hover:bg-orange-100">
      <i class="ri-history-line text-orange-600 text-3xl group-hover:scale-110 transition-transform"></i>
      <span>Historial</span>
    </a>

  </div>
</div>



  <!-- 🔒 Cerrar Sesión -->
  <div class="p-6 border-t border-gray-200">
  <a href="/DelixSystem/src/auth/logout.php"
   class="flex items-center gap-3 text-red-600 hover:text-red-700 transition font-medium">
   <i class="ri-logout-box-line text-2xl"></i>
   <span>Cerrar Sesión</span>
</a>
    
  </div>
</div>
