<?php
session_start();

if (isset($_SESSION['empleado'])) {
    unset($_SESSION['empleado']);
    session_destroy();
}

header("Location: /DelixSystem/index.php");
exit;
