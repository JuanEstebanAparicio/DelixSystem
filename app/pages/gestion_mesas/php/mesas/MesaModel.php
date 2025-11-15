<?php
class MesaModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    /** 🔹 Retorna la conexión PDO (usada en controlador) */
    public function getDB() {
        return $this->db;
    }

    /** Obtener todas las mesas de un área */
    public function obtenerMesasPorArea($id_area) {
        $stmt = $this->db->prepare("SELECT * FROM mesas WHERE id_area = ?");
        $stmt->execute([$id_area]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Crear una nueva mesa */
    public function crearMesa($id_area, $nombre) {
        $stmt = $this->db->prepare("INSERT INTO mesas (id_area, nombre) VALUES (?, ?) RETURNING id_mesa, id_area, nombre");
        $stmt->execute([$id_area, $nombre]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Editar mesa existente */
    public function editarMesa($id_mesa, $id_area, $nombre) {
        $stmt = $this->db->prepare("UPDATE mesas SET nombre = ? WHERE id_mesa = ? AND id_area = ?");
        return $stmt->execute([$nombre, $id_mesa, $id_area]);
    }

    /** Eliminar mesa */
    public function eliminarMesa($id_mesa) {
        $stmt = $this->db->prepare("DELETE FROM mesas WHERE id_mesa = ?");
        return $stmt->execute([$id_mesa]);
    }

    /** Verificar duplicado */
    public function mesaExiste($nombre, $id_area, $id_mesa = null) {
        $sql = "SELECT COUNT(*) FROM mesas WHERE LOWER(nombre) = LOWER(?) AND id_area = ?";
        $params = [$nombre, $id_area];

        if ($id_mesa) {
            $sql .= " AND id_mesa != ?";
            $params[] = $id_mesa;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    public function getMesaById($id_mesa)
{
    $stmt = $this->db->prepare("
        SELECT *
        FROM mesas
        WHERE id_mesa = ?
        LIMIT 1
    ");
    $stmt->execute([$id_mesa]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
    
}
