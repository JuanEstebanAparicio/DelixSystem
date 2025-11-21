<?php
require_once __DIR__ . '/../../../middleware/session_guard.php';
protectPage('propietario');

// ✅ Incluimos el Control Center
 include __DIR__ . '/../../../components/header_propietario.php'; 
 include __DIR__ . '/../../../components/control_center_propietario.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/supabase.php';

$usuario = $_SESSION['usuario'] ?? [];
$id_usuario = $usuario['id'] ?? null;
$nombreUsuario = $usuario['first_name'] ?? 'Usuario';
$apellidoUsuario = $usuario['last_name'] ?? '';
$email = $usuario['email'] ?? 'Sin correo';
$nombreRestaurante = $usuario['restaurant_name'] ?? 'Mi Restaurante';

$totalAreas = 0;
$totalMesas = 0;

if ($id_usuario) {
    try {
        // 🔹 Total de áreas del propietario
        $stmtAreas = $conexion->prepare("SELECT COUNT(*) AS total FROM areas WHERE id_usuario = ?");
        $stmtAreas->execute([$id_usuario]);
        $totalAreas = $stmtAreas->fetch(PDO::FETCH_ASSOC)['total'];

        // 🔹 Total de mesas (unidas a sus áreas)
        $stmtMesas = $conexion->prepare("
            SELECT COUNT(*) AS total 
            FROM mesas m
            INNER JOIN areas a ON m.id_area = a.id_area 
            WHERE a.id_usuario = ?
        ");
        $stmtMesas->execute([$id_usuario]);
        $totalMesas = $stmtMesas->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
$pedidosHoy = 0;
$ventasHoy = 0;

if ($id_usuario) {
    try {
        $stmtDaily = $conexion->prepare("
            SELECT 
                COUNT(*) AS total_orders, 
                COALESCE(SUM(total_pedido), 0) AS total_sales
            FROM orders
            WHERE id_user = ?
              AND pagado = 1
              AND DATE(created_at) = CURRENT_DATE
        ");
        
        $stmtDaily->execute([$id_usuario]);
        $daily = $stmtDaily->fetch(PDO::FETCH_ASSOC);

        $pedidosHoy = $daily['total_orders'] ?? 0;
        $ventasHoy = $daily['total_sales'] ?? 0;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}


$pedidosRecientes = [];

if ($id_usuario) {
    try {
        $stmtRecientes = $conexion->prepare("
            SELECT id, nombre_cliente, mesa, estado, total_pedido
            FROM orders
            WHERE id_user = ?
            ORDER BY created_at DESC
            LIMIT 4
        ");
        $stmtRecientes->execute([$id_usuario]);
        $pedidosRecientes = $stmtRecientes->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Delix | Propietario</title>
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

  <!-- 🧭 Control Center -->
   <?php include __DIR__ . '/../../../components/control_center_propietario.php'; ?>


  <!-- 🌟 Contenido Principal -->
  <main class="pt-28 px-6 flex justify-center items-center">
    <section class="bg-white shadow-lg rounded-2xl p-10 w-full max-w-5xl fade-in">
      
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">
            ¡Bienvenido, <?= htmlspecialchars($nombreUsuario . ' ' . $apellidoUsuario) ?>! 👋
          </h1>
          <p class="text-gray-600 text-lg">
            Nos alegra verte de nuevo en <strong><?= htmlspecialchars($nombreRestaurante) ?></strong>.
          </p>
        </div>

        <!-- Botón de perfil -->
        <button id="openProfileModal"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-full flex items-center gap-2 transition">
          <i class="ri-user-3-line text-xl"></i>
          Perfil
        </button>
      </div>

      <!-- 📋 Información del propietario -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-gray-50 rounded-xl p-5">
          <p class="text-gray-500 text-sm">Correo</p>
          <p class="font-medium text-gray-800"><?= htmlspecialchars($email) ?></p>
        </div>
        <div class="bg-gray-50 rounded-xl p-5">
          <p class="text-gray-500 text-sm">Restaurante</p>
          <p class="font-medium text-gray-800"><?= htmlspecialchars($nombreRestaurante) ?></p>
        </div>
      </div>

      <!-- 📊 Estadísticas Rápidas -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
        <div class="bg-emerald-100 p-6 rounded-xl text-center">
          <h3 class="text-emerald-800 text-lg font-semibold">Pedidos del Día</h3>
         <p class="text-3xl font-bold text-emerald-700 mt-2"><?= $pedidosHoy ?></p>
        </div>
        <div class="bg-sky-100 p-6 rounded-xl text-center">
          <h3 class="text-sky-800 text-lg font-semibold">Ventas Totales</h3>
          <p class="text-3xl font-bold text-sky-700 mt-2">
  $<?= number_format($ventasHoy, 0, ',', '.') ?>
</p>
        </div>
        <div class="bg-amber-100 p-6 rounded-xl text-center">
  <h3 class="text-amber-800 text-lg font-semibold">Resumen de Mesas</h3>
  <p class="text-lg text-amber-700 mt-2 font-medium">Áreas: <?= $totalAreas ?></p>
  <p class="text-3xl font-bold text-amber-700 mt-1"><?= $totalMesas ?> Mesas</p>
</div>

      </div>

      <!-- 🔗 Accesos directos -->
      <div class="flex flex-wrap gap-4 justify-center">
        <a href="/DelixSystem/app/pages/pedidos/view/listar_pedidos.php"
           class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2 rounded-lg font-medium transition-all">
          Pedidos
        </a>
        <a href="/DelixSystem/app/pages/gestion_mesas/view/gestion_mesas.php"
           class="bg-sky-500 hover:bg-sky-600 text-white px-5 py-2 rounded-lg font-medium transition-all">
          Mesas
        </a>
        <a href="/DelixSystem/app/pages/gestor_empleado/view/gestor_empleados.php"
           class="bg-indigo-500 hover:bg-indigo-600 text-white px-5 py-2 rounded-lg font-medium transition-all">
          Empleados
        </a>
        <a href="/DelixSystem/app/pages/dishes_manager/view/dishes_manager.php"
           class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2 rounded-lg font-medium transition-all">
          Gestor Menú
        </a>
        <a href="/DelixSystem/app/pages/gestor_reportes/view/index.php"
           class="bg-rose-500 hover:bg-rose-600 text-white px-5 py-2 rounded-lg font-medium transition-all">
          Reportes
        </a>
      </div>

      <!-- 📈 Tabla de pedidos recientes -->
      <div class="mt-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Pedidos Recientes</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-100 text-gray-700">
              <tr>
                <th class="py-3 px-4 text-left">ID Pedido</th>
                <th class="py-3 px-4 text-left">Cliente</th>
                <th class="py-3 px-4 text-left">Mesa</th>
                <th class="py-3 px-4 text-left">Estado</th>
                <th class="py-3 px-4 text-left">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
<?php if (count($pedidosRecientes) > 0): ?>
    <?php foreach ($pedidosRecientes as $pedido): ?>
        <tr>
            <td class="py-3 px-4">#<?= $pedido['id'] ?></td>
            <td class="py-3 px-4"><?= htmlspecialchars($pedido['nombre_cliente']) ?></td>
            <td class="py-3 px-4"><?= htmlspecialchars($pedido['mesa']) ?></td>
            <td class="py-3 px-4">
                <?php
                $estado = strtolower($pedido['estado']);
                $color = match ($estado) {
                    'pendiente' => 'bg-yellow-100 text-yellow-700',
                    'entregado' => 'bg-emerald-100 text-emerald-700',
                    'cancelado' => 'bg-red-100 text-red-700',
                    default => 'bg-gray-100 text-gray-700'
                };
                ?>
                <span class="<?= $color ?> px-3 py-1 rounded-full text-sm">
                    <?= htmlspecialchars($pedido['estado']) ?>
                </span>
            </td>
            <td class="py-3 px-4">
                $<?= number_format($pedido['total_pedido'], 0, ',', '.') ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="5" class="text-center py-6 text-gray-500">
            No hay pedidos recientes
        </td>
    </tr>
<?php endif; ?>
</tbody>

          </table>
        </div>
      </div>

    </section>
  </main>

  <!-- 🧑 Modal de Edición de Perfil -->
  <div id="profileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-8 relative">
      <button id="closeProfileModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Editar Perfil</h2>

      <form id="profileForm" method="POST" action="../php/profile.php" class="space-y-5">
        <div>
          <label for="first_name" class="block text-sm font-medium text-gray-600">Nombre</label>
          <input type="text" id="first_name" name="first_name"
                 value="<?= htmlspecialchars($usuario['first_name'] ?? '') ?>"
                 class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500" required>
        </div>

        <div>
          <label for="last_name" class="block text-sm font-medium text-gray-600">Apellido</label>
          <input type="text" id="last_name" name="last_name"
                 value="<?= htmlspecialchars($usuario['last_name'] ?? '') ?>"
                 class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500" required>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-600">Correo</label>
          <input type="email" id="email" name="email"
                 value="<?= htmlspecialchars($usuario['email'] ?? '') ?>"
                 class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500" required>
        </div>

        <div>
          <label for="restaurant_name" class="block text-sm font-medium text-gray-600">Restaurante</label>
          <input type="text" id="restaurant_name" name="restaurant_name"
                 value="<?= htmlspecialchars($usuario['restaurant_name'] ?? '') ?>"
                 class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500" required>
        </div>

        <button type="submit"
                class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2 rounded-lg transition-all">
          Guardar Cambios
        </button>
      </form>
    </div>
  </div>

  <!-- JS Global -->
 <script src="/DelixSystem/app/shared/js/control_center_propietario.js"></script>

  <!-- Modal Script -->
  <script>
    const profileModal = document.getElementById('profileModal');
    const openProfileModal = document.getElementById('openProfileModal');
    const closeProfileModal = document.getElementById('closeProfileModal');

    openProfileModal.addEventListener('click', () => profileModal.classList.remove('hidden'));
    closeProfileModal.addEventListener('click', () => profileModal.classList.add('hidden'));
    profileModal.addEventListener('click', (e) => {
      if (e.target === profileModal) profileModal.classList.add('hidden');
    });
  </script>

</body>
</html>