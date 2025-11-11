<?php
require_once __DIR__ . '/../../../config/database.php';

class DashboardEmpleadoController
{
    public static function obtenerDatosEmpleado($empleadoId)
    {
        if (!$empleadoId) {
            return self::empleadoVacio();
        }

        // 🔹 Consultar los datos básicos del empleado
        $response = supabaseRest(
            'employees',
            'GET',
            null,
            '?id=eq.' . $empleadoId . '&select=id,full_name,email,document,is_online,user_id'
        );

        if ($response['status'] !== 200 || empty($response['data'])) {
            error_log("❌ Error obteniendo empleado: " . print_r($response, true));
            return self::empleadoVacio();
        }

        $empleado = $response['data'][0];

        // 🔹 Agregar el nombre del restaurante desde la sesión (para no hacer join)
        $empleado['restaurant_name'] = $_SESSION['empleado_auth']['restaurant_name'] ?? 'Restaurante no identificado';

        // 🔹 Obtener roles del empleado
        $rolesResponse = supabaseRest(
            'employee_roles',
            'GET',
            null,
            '?empleado_id=eq.' . $empleadoId . '&select=roles(nombre,descripcion)'
        );

        $roles = [];

        if ($rolesResponse['status'] === 200 && !empty($rolesResponse['data'])) {
            foreach ($rolesResponse['data'] as $rel) {
                if (!empty($rel['roles']['nombre'])) {
                    $roles[] = [
                        'nombre' => $rel['roles']['nombre'],
                        'descripcion' => $rel['roles']['descripcion'] ?? ''
                    ];
                }
            }
        }

        $empleado['roles'] = $roles;

        return $empleado;
    }

    private static function empleadoVacio()
    {
        return [
            'id' => null,
            'full_name' => 'Empleado',
            'email' => '',
            'document' => '',
            'restaurant_name' => '',
            'is_online' => false,
            'roles' => []
        ];
    }
}
