<?php
// DelixSystem/app/middleware/role_guard.php
require_once __DIR__ . '/../config/supabase.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 🛡️ Control de permisos basado en roles de empleado o propietario.
 *
 * @param array|string $allowedRoles Roles permitidos para la acción.
 * @param bool $returnBool Si es true, devuelve solo true/false sin terminar el script.
 */
function canEmployeePerform($allowedRoles, $returnBool = false)
{
    global $conexion;

    // Asegurar que $allowedRoles sea array
    $allowedRoles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];

    // ✅ Propietario (tabla usuarios): acceso total
    if (isset($_SESSION['usuario']['id'])) {
        return true;
    }

    // 🚫 Si no hay sesión de empleado, negar acceso
    if (!isset($_SESSION['empleado_auth']['id'])) {
        if ($returnBool) return false;

        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Sesión de empleado no válida.']);
        exit;
    }

    $empleadoId = $_SESSION['empleado_auth']['id'];

    try {
        // 🔹 Obtener roles del empleado directamente desde la BD
        $stmt = $conexion->prepare("
            SELECT r.nombre 
            FROM roles r
            INNER JOIN employee_roles er ON r.id = er.rol_id
            WHERE er.empleado_id = ?
        ");
        $stmt->execute([$empleadoId]);
        $rolesEmpleado = array_map('strtoupper', $stmt->fetchAll(PDO::FETCH_COLUMN));

        // 🔹 Si el empleado tiene alguno de los roles permitidos
        foreach ($allowedRoles as $rol) {
            if (in_array(strtoupper($rol), $rolesEmpleado)) {
                return true;
            }
        }

        // 🚫 Si no tiene permisos suficientes
        if ($returnBool) return false;

        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No tienes permisos suficientes para realizar esta acción.'
        ]);
        exit;

    } catch (Exception $e) {
        error_log("Error en role_guard: " . $e->getMessage());
        if ($returnBool) return false;

        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al verificar permisos.']);
        exit;
    }
}
