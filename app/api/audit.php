<?php
// DelixSystem/app/helpers/audit.php

require_once __DIR__ . '/../middleware/universal_guard.php';
require_once __DIR__ . '/../config/supabase.php';

class Audit
{
    /**
     * Detecta actor REAL basándose en universalGuard
     */
    private static function getActor(PDO $conexion): array
    {
        $u = universalGuard(); // ← YA maneja propietario y empleado correctamente

        // Propietario
        if ($u['tipo'] === 'propietario') {
            return [
                'owner_id'    => $u['id'],
                'actor_id'    => $u['id'],
                'actor_type'  => 'owner',
                'actor_roles' => ['OWNER']
            ];
        }

        // Empleado
        if ($u['tipo'] === 'empleado') {

            $empleadoId = $u['id'];
            $ownerId    = $u['restaurant_id']; // ← AQUÍ VIENE DEL UNIVERSAL GUARD

            if (!$ownerId) {
                // Fallback para seguridad
                // Buscar owner desde employees
                $stmt = $conexion->prepare("SELECT user_id FROM employees WHERE id = ?");
                $stmt->execute([$empleadoId]);
                $ownerId = $stmt->fetchColumn() ?: null;
            }

            // Obtener roles
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

        // Anónimo
        return [
            'owner_id'    => null,
            'actor_id'    => null,
            'actor_type'  => 'anonymous',
            'actor_roles' => []
        ];
    }

    public static function log(array $params): bool
    {
        global $conexion;

        $actor = self::getActor($conexion);

        $data = array_merge([
            'owner_id'     => $actor['owner_id'],
            'actor_id'     => $actor['actor_id'],
            'actor_type'   => $actor['actor_type'],
            'actor_roles'  => json_encode($actor['actor_roles']),
            'gestor'       => null,
            'action'       => null,
            'status'       => 'success',
            'target_table' => null,
            'target_id'    => null,
            'old'          => null,
            'new'          => null,
            'meta'         => json_encode([
                'ip'    => $_SERVER['REMOTE_ADDR'] ?? null,
                'agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ])
        ], $params);

        // Validación
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
            ":owner_id"     => $data["owner_id"],
            ":actor_id"     => $data["actor_id"],
            ":actor_type"   => $data["actor_type"],
            ":actor_roles"  => $data["actor_roles"],
            ":gestor"       => $data["gestor"],
            ":action"       => $data["action"],
            ":status"       => $data["status"],
            ":target_table" => $data["target_table"],
            ":target_id"    => $data["target_id"],
            ":old"          => json_encode($data["old"]),
            ":new"          => json_encode($data["new"]),
            ":meta"         => $data["meta"]
        ]);
    }

    public static function quick(string $gestor, string $action, array $data = []): bool
    {
        return self::log(array_merge([
            'gestor' => $gestor,
            'action' => $action
        ], $data));
    }
}
