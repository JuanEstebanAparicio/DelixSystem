<?php
session_start();

if (!isset($_SESSION['empleado_auth']) || $_SESSION['empleado_auth']['auth_type'] !== 'empleado') {
    header("Location: /DelixSystem/app/pages/login_empleado.php");
    exit;
}

$empleado = $_SESSION['empleado_auth'];
?>



<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel del Empleado</title>
</head>
<body>


</body>
</html>
