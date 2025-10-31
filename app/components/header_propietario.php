<?php
// DelixSystem/app/components/header_propietario.php
if (session_status() === PHP_SESSION_NONE) session_start();

// 🔹 Datos del propietario
$propietario = $_SESSION['usuario'] ?? [
  'first_name' => 'Propietario',
  'last_name' => '',
  'email' => '',
  'restaurant_name' => 'Mi Restaurante'
];

$nombreCompleto = htmlspecialchars(trim($propietario['first_name'] . ' ' . $propietario['last_name']));
$correo = htmlspecialchars($propietario['email']);
$nombreRestaurante = htmlspecialchars($propietario['restaurant_name']);
$inicial = strtoupper(substr($propietario['first_name'] ?? 'P', 0, 1));
?>

<header class="fixed top-0 left-0 w-full bg-white shadow-sm z-40">
  <div class="flex justify-between items-center px-6 py-3">
    <!-- Logo y nombre del sistema -->
    <div class="flex items-center gap-3">
      <button id="menuToggle" class="text-2xl text-gray-700 hover:text-emerald-600 transition">
        <i class="ri-menu-line"></i>
      </button>
      <div>
        <h1 class="font-semibold text-lg text-gray-800">DelixSystem</h1>
        <p class="text-sm text-gray-500">Panel de Propietario</p>
      </div>
    </div>

    <!-- Perfil del propietario -->
    <div class="flex items-center gap-4">
      <button id="openControlCenter" 
        class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-1.5 rounded-lg transition font-medium">
        Abrir Centro de Control
      </button>

      <div class="flex items-center gap-2">
        <div class="w-9 h-9 flex items-center justify-center bg-emerald-500 text-white rounded-full font-semibold">
          <?= $inicial ?>
        </div>
        <div>
          <div class="text-gray-800 font-medium"><?= $nombreCompleto ?></div>
          <div class="text-xs text-gray-500"><?= $correo ?></div>
          <div class="text-xs text-gray-500 italic"><?= $nombreRestaurante ?></div>
        </div>
      </div>
    </div>
  </div>
</header>
