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
  <div class="p-6 flex flex-col gap-4">
    <a href="/DelixSystem/app/pages/dashboard_propietario/view/index.php"
       class="flex items-center gap-3 text-gray-700 hover:text-emerald-600 transition">
      <i class="ri-dashboard-line text-2xl"></i>
      <span class="text-base font-medium">Panel Principal</span>
    </a>

    <a href="/DelixSystem/app/pages/gestion_mesas/view/gestion_mesas.php"
       class="flex items-center gap-3 text-gray-700 hover:text-emerald-600 transition">
      <i class="ri-restaurant-line text-2xl"></i>
      <span class="text-base font-medium">Gestión de Mesas</span>
    </a>

    <a href="/DelixSystem/app/pages/dishes_manager/view/dishes_manager.php"
       class="flex items-center gap-3 text-gray-700 hover:text-emerald-600 transition">
      <i class="ri-bowl-line text-2xl"></i>
      <span class="text-base font-medium">Gestor de Menú</span>
    </a>

    <a href="/DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php"
       class="flex items-center gap-3 text-gray-700 hover:text-emerald-600 transition">
      <i class="ri-team-line text-2xl"></i>
      <span class="text-base font-medium">Gestión de Empleados</span>
    </a>

    <a href="/DelixSystem/app/pages/reports/view/index.php"
       class="flex items-center gap-3 text-gray-700 hover:text-emerald-600 transition">
      <i class="ri-bar-chart-box-line text-2xl"></i>
      <span class="text-base font-medium">Reportes y Ventas</span>
    </a>

    <a href="/DelixSystem/app/pages/perfil_propietario/view/index.php"
       class="flex items-center gap-3 text-gray-700 hover:text-emerald-600 transition">
      <i class="ri-user-settings-line text-2xl"></i>
      <span class="text-base font-medium">Mi Perfil</span>
    </a>
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
