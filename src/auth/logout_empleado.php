<?php
// DelixSystem/src/auth/logout_empleado.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Solo eliminamos la sesión del empleado
if (isset($_SESSION['empleado_auth'])) {
    unset($_SESSION['empleado_auth']);
}

// ✅ (Opcional) destruir toda la sesión si solo se usa para empleados
// session_destroy();

// 🔁 Redirigir al login de empleados
header("Location: /DelixSystem/public/index.php");
exit;
