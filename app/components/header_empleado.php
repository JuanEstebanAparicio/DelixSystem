<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$empleado = $_SESSION['empleado_auth'] ?? null;
?>

<header class="bg-white shadow-sm border-b flex items-center justify-between px-6 py-3 fixed top-0 left-0 right-0 z-50">
  <div class="flex items-center gap-4">
    <button id="menuToggle" class="text-gray-600 hover:text-gray-800 focus:outline-none">
      <i class="ri-menu-2-line text-2xl"></i>
    </button>
    <div class="flex items-center gap-2">
      <div class="bg-emerald-600 text-white font-bold px-3 py-1 rounded-lg">D</div>
      <h1 class="text-lg font-semibold text-gray-800">DelixSystem <span class="text-gray-500 text-sm">| Employees</span></h1>
    </div>
  </div>

  <div class="flex items-center gap-4">
    <button id="controlCenterBtn" class="bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-4 py-2 rounded-md transition-all">
      Abrir Centro de Control
    </button>
    <div class="relative">
      <button id="userMenuBtn" class="flex items-center gap-2 hover:bg-gray-100 px-3 py-2 rounded-md transition-all">
        <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center font-bold text-gray-700">
          <?= strtoupper(substr($empleado['full_name'] ?? 'E', 0, 1)) ?>
        </div>
        <span class="text-gray-800 font-medium"><?= htmlspecialchars($empleado['full_name'] ?? 'Empleado') ?></span>
      </button>

      <!-- Dropdown -->
      <div id="userMenu" class="hidden absolute right-0 mt-2 w-64 bg-white shadow-lg rounded-xl p-4 border">
        <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($empleado['email'] ?? '') ?></p>
        <p class="text-sm"><strong>ID:</strong> <?= htmlspecialchars($empleado['id'] ?? '') ?></p>
        <p class="text-sm mb-3"><strong>Documento:</strong> <?= htmlspecialchars($empleado['document'] ?? '') ?></p>
        <form action="/DelixSystem/src/auth/logout_empleado.php" method="POST">
          <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-medium py-2 rounded-lg">Cerrar sesión</button>
        </form>
      </div>
    </div>
  </div>
</header>
