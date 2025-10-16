<?php
class MesaModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    /** Obtener todas las mesas de un área específica */
    public function obtenerMesasPorArea($id_area) {
        $stmt = $this->db->prepare("SELECT * FROM mesas WHERE id_area = ? ORDER BY id_mesa");
        $stmt->execute([$id_area]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Crear una nueva mesa */
    public function crearMesa($id_area, $nombre) {
        $stmt = $this->db->prepare("INSERT INTO mesas (id_area, nombre) VALUES (?, ?)");
        return $stmt->execute([$id_area, $nombre]);
    }

    /** Editar una mesa existente */
    public function editarMesa($id_mesa, $id_area, $nombre) {
        $stmt = $this->db->prepare("UPDATE mesas SET id_area = ?, nombre = ? WHERE id_mesa = ?");
        return $stmt->execute([$id_area, $nombre, $id_mesa]);
    }

    /** Eliminar una mesa */
    public function eliminarMesa($id_mesa) {
        $stmt = $this->db->prepare("DELETE FROM mesas WHERE id_mesa = ?");
        return $stmt->execute([$id_mesa]);
    }

    /** Verificar si ya existe una mesa con ese nombre dentro del área */
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
}
