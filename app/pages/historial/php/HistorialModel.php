<?php

class HistorialModel
{
    private PDO $db;

    public function __construct(PDO $conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Lista auditorías del dueño y sus empleados
     */
    public function getAuditsByOwner($ownerId)
    {
        $sql = "
            SELECT 
                a.id,
                a.owner_id,
                a.actor_id,
                a.actor_type,
                a.actor_roles,
                a.gestor,
                a.action,
                a.status,
                a.target_table,
                a.target_id,
                a.old,
                a.new,
                a.meta,
                a.created_at,
                COALESCE(u.nombre, e.nombre) AS actor_nombre
            FROM audit_logs a
            LEFT JOIN usuarios u ON u.id = a.actor_id AND a.actor_type = 'owner'
            LEFT JOIN employees e ON e.id = a.actor_id AND a.actor_type = 'employee'
            WHERE a.owner_id = :owner
            ORDER BY a.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':owner' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
