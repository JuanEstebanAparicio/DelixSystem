<?php
// DelixSystem/app/middleware/employee_extended_guard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/universal_guard.php';
require_once __DIR__ . '/../config/supabase.php';

/**
 * 🧩 employeeExtendedGuard()
 * 
 * - Permite acceso tanto a propietarios como empleados.
 * - Si el usuario es empleado, verifica que tenga al menos uno de los roles requeridos.
 * - Si no tiene permisos → muestra SweetAlert y lo redirige al dashboard.
 */
function employeeExtendedGuard(array $rolesPermitidos = []) {
    global $conexion;

    // ✅ Obtener datos del usuario actual
    $usuario = universalGuard();

    // 👑 Si es propietario, acceso total sin validación de roles
    if ($usuario['tipo'] === 'propietario') {
        return $usuario;
    }

    // 👷‍♂️ Si es empleado, validar roles
    if ($usuario['tipo'] === 'empleado') {
        $empleadoId = $usuario['id'];

        try {
            $stmt = $conexion->prepare("
                SELECT r.nombre
                FROM roles r
                INNER JOIN employee_roles er ON er.rol_id = r.id
                WHERE er.empleado_id = ?
            ");
            $stmt->execute([$empleadoId]);
            $rolesEmpleado = array_map('strtoupper', $stmt->fetchAll(PDO::FETCH_COLUMN));

            // 🔍 Verificamos si tiene al menos uno de los roles permitidos
            $rolesPermitidos = array_map('strtoupper', $rolesPermitidos);
            $tienePermiso = count(array_intersect($rolesEmpleado, $rolesPermitidos)) > 0;

            if (!$tienePermiso) {
                echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Acceso Denegado',
                        text: 'No tienes permisos para acceder al Gestor de Empleados.',
                        confirmButtonText: 'Volver al panel'
                    }).then(() => {
                        window.location.href = '/DelixSystem/app/pages/dashboard_empleado/view/index.php';
                    });
                </script>
                ";
                exit;
            }

            // ✅ Si tiene acceso, devolvemos el usuario
            return $usuario;

        } catch (Throwable $e) {
            error_log('Error verificando roles de empleado: ' . $e->getMessage());
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error Interno',
                    text: 'No se pudieron validar tus permisos.',
                    confirmButtonText: 'Volver al panel'
                }).then(() => {
                    window.location.href = '/DelixSystem/app/pages/dashboard_empleado/view/index.php';
                });
            </script>
            ";
            exit;
        }
    }

    // 🚫 Caso extremo: sin tipo reconocido
    header('Location: /DelixSystem/public/index.php');
    exit();
}
