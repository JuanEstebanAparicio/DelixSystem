<?php
session_name("empleado_session");
session_start();

if (!isset($_SESSION['empleado'])) {
    header("Location: /DelixSystem/public/index.php");
    exit;
}

$empleado = $_SESSION['empleado'];
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel del Empleado</title>
</head>
<body>
  <h2>Bienvenido, <?= htmlspecialchars($empleado['full_name']) ?> 👋</h2>
  <p>Correo: <?= htmlspecialchars($empleado['email']) ?></p>
  <p>Documento: <?= htmlspecialchars($empleado['document']) ?></p>
  <p>Propietario asociado (user_id): <?= htmlspecialchars($empleado['user_id']) ?></p>

  <a href="/DelixSystem/app/pages/gestor_empleado/php/employee/EmpleadoLogout.php">Cerrar sesión</a>
</body>
</html>
