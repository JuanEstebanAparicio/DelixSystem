<?php
require_once __DIR__ . '/../../../middleware/employee_guard.php';
protectEmpleado(); // 🚨 Solo empleados logueados pueden entrar

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$empleado = $_SESSION['empleado_auth'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard del Empleado</title>
</head>
<body>
  <h1>Bienvenido, <?= htmlspecialchars($empleado['full_name'] ?? 'Empleado') ?> 👋</h1>

  <form action="/DelixSystem/src/auth/logout_empleado.php" method="POST">
      <button type="submit">Cerrar Sesión</button>
  </form>
</body>
</html>
