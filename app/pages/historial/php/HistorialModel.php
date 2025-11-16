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

            -- ⬇ NOMBRE DEL USUARIO QUE REALIZÓ LA ACCIÓN
            CASE 
                WHEN a.actor_type = 'owner' THEN 
                    (SELECT first_name || ' ' || last_name FROM usuarios WHERE id = a.actor_id LIMIT 1)
                WHEN a.actor_type = 'employee' THEN 
                    (SELECT full_name FROM employees WHERE id = a.actor_id LIMIT 1)
                ELSE 'Usuario desconocido'
            END AS usuario_nombre

        FROM audit_logs a
        WHERE a.owner_id = :owner
        ORDER BY a.created_at DESC
        LIMIT :limit
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':owner', $ownerId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalizar columnas JSON
    foreach ($rows as &$r) {
        $r['actor_roles'] = $r['actor_roles'] !== null ? $r['actor_roles'] : '[]';
        $r['old']         = $r['old'] !== null ? $r['old'] : null;
        $r['new']         = $r['new'] !== null ? $r['new'] : null;
        $r['meta']        = $r['meta'] !== null ? $r['meta'] : null;

        // alias compatible con el JS
        if (!isset($r['usuario_nombre']) || $r['usuario_nombre'] === null) {
            $r['usuario_nombre'] = "Usuario desconocido";
        }
    }

    return $rows;
}

}
