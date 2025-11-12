<?php
require_once __DIR__ . '/../../../middleware/employee_guard.php';
require_once __DIR__ . '/../php/DashboardEmpleadoController.php';
include __DIR__ . '/../../../components/header_empleado.php';
include __DIR__ . '/../../../components/control_center.php';

protectEmpleado();

if (session_status() === PHP_SESSION_NONE) session_start();

$empleadoAuth = $_SESSION['empleado_auth'] ?? null;

if (!$empleadoAuth || !isset($empleadoAuth['id'])) {
    header('Location: /DelixSystem/public/index.php');
    exit;
}

$empleado = DashboardEmpleadoController::obtenerDatosEmpleado($empleadoAuth['id']);
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
    <section class="bg-white shadow-lg rounded-2xl p-10 w-full max-w-4xl">
  <h1 class="text-3xl font-bold text-gray-800 mb-3">
    ¡Bienvenido, <?= htmlspecialchars($empleado['full_name']) ?>! 👋
  </h1>
  <p class="text-gray-600 text-lg mb-6">
    Nos alegra tenerte de vuelta en <strong><?= htmlspecialchars($empleado['restaurant_name']) ?></strong>.
    Aquí encontrarás tus herramientas laborales.
  </p>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-gray-50 rounded-xl p-5">
      <p class="text-gray-500 text-sm">Correo</p>
      <p class="font-medium text-gray-800"><?= htmlspecialchars($empleado['email']) ?></p>
    </div>
    <div class="bg-gray-50 rounded-xl p-5">
      <p class="text-gray-500 text-sm">Restaurante</p>
      <p class="font-medium text-gray-800"><?= htmlspecialchars($empleado['restaurant_name']) ?></p>
    </div>
    <div class="bg-gray-50 rounded-xl p-5">
      <p class="text-gray-500 text-sm">Documento</p>
      <p class="font-medium text-gray-800"><?= htmlspecialchars($empleado['document']) ?></p>
    </div>
    <div class="bg-gray-50 rounded-xl p-5">
  <p class="text-gray-500 text-sm">Roles asignados</p>
  <div id="rolesContainer" class="flex flex-wrap gap-2 mt-1">
  <?php if (!empty($empleado['roles'])): ?>
    <?php foreach ($empleado['roles'] as $rol): ?>
      <span class="px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-700">
        <?= htmlspecialchars($rol['nombre']) ?>
      </span>
    <?php endforeach; ?>
  <?php else: ?>
    <span class="text-gray-500 text-sm">Sin roles asignados</span>
  <?php endif; ?>
</div>
</div>

  </div>

  <div class="flex flex-wrap gap-4 justify-center">
    <a href="/DelixSystem/app/pages/gestion_pedidos/view/index.php" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2 rounded-lg font-medium transition-all">Pedidos</a>
    <a href="/DelixSystem/app/pages/gestion_mesas/view/gestion_mesas.php" class="bg-sky-500 hover:bg-sky-600 text-white px-5 py-2 rounded-lg font-medium transition-all">Mesas</a>
    <a href="/DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php" class="bg-indigo-500 hover:bg-indigo-600 text-white px-5 py-2 rounded-lg font-medium transition-all">Empleados</a>
    <a href="/DelixSystem/app/pages/dishes_manager/view/dishes_manager.php" class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2 rounded-lg font-medium transition-all">Gestor Menú</a>
  </div>
</section>

  </main>

  <!-- JS Global -->
  <script src="/DelixSystem/app/shared/js/control_center.js"></script>
  <script src="../js/roles_auto_update.js"></script>

</body>
</html>