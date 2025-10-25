<?php
session_start();
if (!isset($_SESSION['empleado'])) {
  header("Location: /DelixSystem/public/login.php?error=unauthorized");
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

  <a href="/DelixSystem/src/auth/logout.php">Cerrar sesión</a>
</body>
</html>
