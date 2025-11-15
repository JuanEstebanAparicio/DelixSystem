<?php
// DelixSystem/app/helpers/audit.php
require_once __DIR__ . '/../config/supabase.php';

class Audit
{
    /**
     * 🔎 Detecta automáticamente si el actor es propietario o empleado.
     */
    private static function getCurrentActor(PDO $conexion): array
    {
        // Propietario autenticado (tabla usuarios)
        if (isset($_SESSION['usuario']['id'])) {
            return [
                'owner_id'    => $_SESSION['usuario']['id'],
                'actor_id'    => $_SESSION['usuario']['id'],
                'actor_type'  => 'owner',
                'actor_roles' => ['OWNER']
            ];
        }

        // Empleado autenticado
        if (isset($_SESSION['empleado_auth']['id'])) {

            $empleadoId = $_SESSION['empleado_auth']['id'];
            $ownerId    = $_SESSION['empleado_auth']['user_id'];

            // Obtener roles del empleado
            $stmt = $conexion->prepare("
                SELECT r.nombre
                FROM roles r
                INNER JOIN employee_roles er ON r.id = er.rol_id
                WHERE er.empleado_id = ?
            ");
            $stmt->execute([$empleadoId]);
            $roles = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

            return [
                'owner_id'    => $ownerId,
                'actor_id'    => $empleadoId,
                'actor_type'  => 'employee',
                'actor_roles' => $roles
            ];
        }

        // Sin sesión → anónimo
        return [
            'owner_id'    => null,
            'actor_id'    => null,
            'actor_type'  => 'anonymous',
            'actor_roles' => []
        ];
    }

    /**
     * 🧩 Método principal para registrar auditoría.
     */
    public static function log(array $params): bool
    {
        global $conexion;

        $actor = self::getCurrentActor($conexion);

        // Fusión de parámetros
        $data = array_merge([
            'owner_id'    => $actor['owner_id'],
            'actor_id'    => $actor['actor_id'],
            'actor_type'  => $actor['actor_type'],
            'actor_roles' => json_encode($actor['actor_roles']),
            'gestor'      => null,
            'action'      => null,
            'status'      => 'success',
            'target_table' => null,
            'target_id'    => null,
            'old'          => null,
            'new'          => null,
            'meta'         => json_encode([
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
                'agent'     => $_SERVER['HTTP_USER_AGENT'] ?? null
            ])
        ], $params);

        // Validación mínima
        if (!$data['owner_id'] || !$data['gestor'] || !$data['action']) {
            return false;
        }

        $stmt = $conexion->prepare("
            INSERT INTO audit_logs (
                owner_id, actor_id, actor_type, actor_roles,
                gestor, action, status,
                target_table, target_id,
                old, new, meta
            )
            VALUES (
                :owner_id, :actor_id, :actor_type, :actor_roles,
                :gestor, :action, :status,
                :target_table, :target_id,
                :old, :new, :meta
            )
        ");

        return $stmt->execute([
            ':owner_id'    => $data['owner_id'],
            ':actor_id'    => $data['actor_id'],
            ':actor_type'  => $data['actor_type'],
            ':actor_roles' => $data['actor_roles'],
            ':gestor'      => $data['gestor'],
            ':action'      => $data['action'],
            ':status'      => $data['status'],
            ':target_table' => $data['target_table'],
            ':target_id'    => $data['target_id'],
            ':old'          => json_encode($data['old']),
            ':new'          => json_encode($data['new']),
            ':meta'         => $data['meta']
        ]);
    }

    /**
     * 🚀 Método rápido para registrar acciones estándar.
     */
    public static function quick(string $gestor, string $action, array $data = []): bool
    {
        return self::log(array_merge([
            'gestor' => $gestor,
            'action' => $action
        ], $data));
    }
}
