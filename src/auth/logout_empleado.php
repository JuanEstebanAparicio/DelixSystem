<?php
session_start();
unset($_SESSION['empleado']);
session_write_close();

header("Location: /DelixSystem/public/index.php");
exit;
