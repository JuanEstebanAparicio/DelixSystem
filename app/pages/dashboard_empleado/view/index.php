<?php
// ✅ Incluir el middleware de empleados
require_once __DIR__ . '/../../../middleware/employee_guard.php';

// ✅ Proteger acceso (redirige si no hay sesión de empleado)
protectEmpleado();

// ✅ Iniciar sesión solo si aún no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Obtener datos del empleado logueado
$empleado = $_SESSION['empleado_auth'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel del Empleado</title>
</head>
<body>

  <h1>Bienvenido, <?= htmlspecialchars($empleado['full_name'] ?? 'Empleado') ?> 👋</h1>

</body>
</html>
