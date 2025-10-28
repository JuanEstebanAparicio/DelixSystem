<?php
require_once __DIR__ . '/../../../middleware/employee_guard.php';
include __DIR__ . '/../../../components/header_empleado.php';
include __DIR__ . '/../../../components/control_center.php';
protectEmpleado();

if (session_status() === PHP_SESSION_NONE) session_start();
$empleado = $_SESSION['empleado_auth'] ?? ['full_name' => 'Empleado', 'email' => ''];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Delix | Employees</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/DelixSystem/app/shared/css/globals.css">
  <link rel="stylesheet" href="/DelixSystem/app/shared/css/control_center.css">
  <style>
    .fade-in {
      opacity: 0;
      transform: translateY(15px);
      animation: fadeInUp 0.8s ease-out forwards;
    }
    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen font-sans text-gray-800">
  <!-- 🌟 Contenido Principal -->
  <main class="pt-28 px-6 flex justify-center items-center">
    <section class="fade-in bg-white shadow-xl rounded-3xl p-10 w-full max-w-5xl flex flex-col md:flex-row justify-between items-center gap-10 border border-gray-100">
      
      <!-- 🧑‍💼 Texto de bienvenida -->
      <div class="flex-1">
        <h1 class="text-4xl font-bold text-gray-800 mb-3">
          ¡Bienvenido, <?= htmlspecialchars($empleado['full_name']) ?>! 👋
        </h1>
        <p class="text-gray-600 text-lg mb-8 leading-relaxed">
          Nos alegra verte de nuevo en <strong class="text-emerald-600">DelixSystem</strong>.  
          Este es tu panel de control para acceder a las herramientas laborales que necesitas.
        </p>

        <!-- ⚡ Accesos rápidos -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <a href="/DelixSystem/app/pages/gestion_pedidos/view/index.php" 
             class="group flex items-center gap-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-5 py-4 rounded-xl transition-all shadow-sm hover:shadow-md">
             <i class="ri-restaurant-2-line text-2xl group-hover:scale-110 transition-transform"></i>
             <span class="font-medium">Pedidos</span>
          </a>

          <a href="/DelixSystem/app/pages/gestion_mesas/view/resumen_mesas.php" 
             class="group flex items-center gap-3 bg-sky-50 hover:bg-sky-100 text-sky-700 px-5 py-4 rounded-xl transition-all shadow-sm hover:shadow-md">
             <i class="ri-layout-grid-line text-2xl group-hover:scale-110 transition-transform"></i>
             <span class="font-medium">Mesas</span>
          </a>

          <a href="/DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php" 
             class="group flex items-center gap-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-5 py-4 rounded-xl transition-all shadow-sm hover:shadow-md">
             <i class="ri-team-line text-2xl group-hover:scale-110 transition-transform"></i>
             <span class="font-medium">Empleados</span>
          </a>

          <a href="/DelixSystem/app/pages/dishes_manager/view/index.php" 
             class="group flex items-center gap-3 bg-amber-50 hover:bg-amber-100 text-amber-700 px-5 py-4 rounded-xl transition-all shadow-sm hover:shadow-md">
             <i class="ri-restaurant-line text-2xl group-hover:scale-110 transition-transform"></i>
             <span class="font-medium">Gestor Menú</span>
          </a>

          <a href="/DelixSystem/app/pages/inventory/view/index.php" 
             class="group flex items-center gap-3 bg-rose-50 hover:bg-rose-100 text-rose-700 px-5 py-4 rounded-xl transition-all shadow-sm hover:shadow-md">
             <i class="ri-archive-2-line text-2xl group-hover:scale-110 transition-transform"></i>
             <span class="font-medium">Inventario</span>
          </a>

          <a href="#" 
             class="group flex items-center gap-3 bg-gray-50 hover:bg-gray-100 text-gray-700 px-5 py-4 rounded-xl transition-all shadow-sm hover:shadow-md">
             <i class="ri-bar-chart-2-line text-2xl group-hover:scale-110 transition-transform"></i>
             <span class="font-medium">Reportes</span>
          </a>
        </div>
      </div>

      <!-- 🎨 Imagen -->
      <div class="flex-shrink-0 hidden md:block">
        <img src="https://cdn.dribbble.com/users/242220/screenshots/15658596/media/75dc88c0b1de9c72063b93c4a94cc9ee.png" 
             alt="Welcome Illustration" class="w-72 rounded-2xl shadow-md">
      </div>
    </section>
  </main>

  <!-- JS Global -->
  <script src="/DelixSystem/app/shared/js/control_center.js"></script>
</body>
</html>