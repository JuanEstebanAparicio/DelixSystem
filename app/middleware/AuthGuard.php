<?php
// 📂 gestor_empleado/guards/authGuard.php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) {
    header("Location: /login.php");
    exit;
}
?>
