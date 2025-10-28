<?php
require_once __DIR__ . '/../../../middleware/employee_guard.php';
protectEmpleado();
include __DIR__ . '/../../../components/header_empleado.php';
$empleado = $_SESSION['empleado_auth'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Delix | Employees</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../css/dashboard_empleado.css">
</head>
<body class="bg-gray-50 min-h-screen">

  <main class="pt-24 px-6 flex justify-center items-center">
    <section class="bg-white shadow-lg rounded-2xl p-10 w-full max-w-4xl flex justify-between items-center gap-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800 mb-3">¡Bienvenido, <?= htmlspecialchars($empleado['full_name']) ?>! 👋</h1>
        <p class="text-gray-600 text-lg mb-6">Nos alegra tenerte de vuelta en <strong>DelixSystem</strong>.  
        Desde aquí puedes acceder a tus herramientas laborales.</p>

        <div class="flex flex-wrap gap-4">
          <a href="/DelixSystem/app/pages/gestion_pedidos/view/index.php" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2 rounded-lg font-medium transition-all">Pedidos</a>
          <a href="/DelixSystem/app/pages/gestion_mesas/view/resumen_mesas.php" class="bg-sky-500 hover:bg-sky-600 text-white px-5 py-2 rounded-lg font-medium transition-all">Mesas</a>
          <a href="/DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php" class="bg-indigo-500 hover:bg-indigo-600 text-white px-5 py-2 rounded-lg font-medium transition-all">Empleados</a>
          <a href="/DelixSystem/app/pages/dishes_manager/view/index.php" class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2 rounded-lg font-medium transition-all">Gestor Menú</a>
        </div>
      </div>
      <img src="https://cdn.dribbble.com/users/242220/screenshots/15658596/media/75dc88c0b1de9c72063b93c4a94cc9ee.png" 
           alt="Welcome Illustration" class="w-64 hidden md:block rounded-xl shadow-sm">
    </section>
  </main>

  <script src="../js/header_empleado.js"></script>
</body>
</html>
