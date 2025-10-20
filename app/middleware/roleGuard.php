<?php
// 📂 gestor_empleado/guards/roleGuard.php
function verificarPermiso($rol, $permisoRequerido) {
    $permisos = [
        'admin' => ['empleados', 'inventario', 'menu', 'mesas', 'areas'],
        'supervisor' => ['inventario', 'menu', 'mesas', 'areas'],
        'cocinero' => ['inventario', 'menu'],
        'mesero' => ['mesas', 'areas'],
        'cajero' => ['facturacion'],
    ];

    if (!isset($permisos[$rol]) || !in_array($permisoRequerido, $permisos[$rol])) {
        header("Location: /no_permiso.php");
        exit;
    }
}
?>
