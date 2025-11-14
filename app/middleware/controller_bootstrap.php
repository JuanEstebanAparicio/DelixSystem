<?php
// === Bootstrap común para todos los controladores ===
// DelixSystem/app/middleware/controller_bootstrap.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// --- CONFIG GLOBAL ---
require_once __DIR__ . '/../config/supabase.php';
require_once __DIR__ . '/role_guard.php';

// --- HEADERS COMUNES ---
header('Content-Type: application/json');

// --- INICIAR SESIÓN ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- FUNCIONES GLOBALES --- //

/**
 * Envía una respuesta JSON uniforme
 */
function returnJson($ajax, $status, $message, $data = [])
{
    if ($ajax) {
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit;
    }

    header("Location: ../../view/gestion_mesas.php?status={$status}&msg=" . urlencode($message));
    exit;
}

/**
 * Determina si es una petición AJAX
 */
function isAjaxRequest(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Obtiene el ID del propietario según la sesión
 */
function getPropietarioID(PDO $conexion)
{
    $id_usuario = $_SESSION['usuario']['id'] ?? null;
    $id_empleado = $_SESSION['empleado_auth']['id'] ?? null;

    if (!$id_usuario && !$id_empleado) {
        return [null, 'No hay sesión activa.'];
    }

    // 🔹 Si es empleado, obtener el propietario real
    if ($id_empleado && !$id_usuario) {
        $stmt = $conexion->prepare("SELECT user_id FROM employees WHERE id = ?");
        $stmt->execute([$id_empleado]);
        $id_propietario = $stmt->fetchColumn();

        if (!$id_propietario) {
            return [null, 'Empleado sin propietario asignado.'];
        }

        return [$id_propietario, null];
    }

    return [$id_usuario, null];
}

/**
 * Carga la configuración de permisos centralizada
 */
function getRoleAccess(): array
{
    return [
        'areas' => [
            'crear'    => ['GESTOR_MESAS', 'ADMIN_LOCAL'],
            'editar'   => ['GESTOR_MESAS', 'ADMIN_LOCAL'],
            'eliminar' => ['GESTOR_MESAS', 'ADMIN_LOCAL'],
            'ordenar'  => ['GESTOR_MESAS', 'ADMIN_LOCAL']
        ],
        'mesas' => [
            'crear'    => ['GESTOR_MESAS', 'ADMIN_LOCAL'],
            'editar'   => ['GESTOR_MESAS', 'ADMIN_LOCAL'],
            'eliminar' => ['GESTOR_MESAS', 'ADMIN_LOCAL']
        ],
        // 🧩 Nuevo bloque
        'empleados' => [
            'ver'           => ['ADMIN_LOCAL', 'GESTOR_EMPLEADOS', 'SUPERVISOR'],
            'crear'         => ['ADMIN_LOCAL', 'GESTOR_EMPLEADOS'],          
            'eliminar'      => ['ADMIN_LOCAL', 'GESTOR_EMPLEADOS'],
            'generar_codigo'=> ['ADMIN_LOCAL', 'GESTOR_EMPLEADOS'],          
        ],
         'roles' => [
            'ver'      => ['ADMIN_LOCAL', 'GESTOR_EMPLEADOS', 'SUPERVISOR'],
            'listar'   => ['ADMIN_LOCAL', 'GESTOR_EMPLEADOS', 'SUPERVISOR'],
            'asignar'  => ['ADMIN_LOCAL', 'GESTOR_EMPLEADOS']
         ],
         'inventario' => [
                'ver'      => ['ADMIN_LOCAL', 'GESTOR_INVENTARIO', 'SUPERVISOR'],
                'crear'    => ['ADMIN_LOCAL', 'GESTOR_INVENTARIO'],
                'editar'   => ['ADMIN_LOCAL', 'GESTOR_INVENTARIO'],
                'eliminar' => ['ADMIN_LOCAL', 'GESTOR_INVENTARIO']
        ]

    ];
}

/**
 * Valida si el rol actual puede realizar cierta acción
 */
function verifyRoleAccess(string $resource, string $action): void
{
    $rolesPermitidos = getRoleAccess()[$resource][$action] ?? [];
    canEmployeePerform($rolesPermitidos);
}
