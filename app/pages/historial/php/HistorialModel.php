<?php
// DelixSystem/app/pages/historial/php/HistorialModel.php

class HistorialModel
{
    private PDO $db;

    public function __construct(PDO $conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Retorna auditorías del owner (audit_logs)
     * Devuelve array asociativo apto para serializar en JSON.
     */
    public function getAuditsByOwner(int $ownerId, int $limit = 300)
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
                -- intentar resolver nombre legible del actor
                COALESCE(u.first_name || ' ' || u.last_name, e.full_name, NULL) AS actor_nombre
            FROM audit_logs a
            LEFT JOIN usuarios u ON (a.actor_type = 'owner'   AND u.id = a.actor_id)
            LEFT JOIN employees e ON (a.actor_type = 'employee' AND e.id = a.actor_id)
            WHERE a.owner_id = :owner
            ORDER BY a.created_at DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        // bindValue para limitar y tipar bien
        $stmt->bindValue(':owner', $ownerId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalizar columnas json para asegurar strings (evita problemas al JSON encode)
        foreach ($rows as &$r) {
            $r['actor_roles'] = $r['actor_roles'] !== null ? $r['actor_roles'] : '[]';
            $r['old'] = $r['old'] !== null ? $r['old'] : null;
            $r['new'] = $r['new'] !== null ? $r['new'] : null;
            $r['meta'] = $r['meta'] !== null ? $r['meta'] : null;
        }

        return $rows;
    }
}
