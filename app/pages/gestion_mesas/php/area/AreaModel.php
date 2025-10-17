<?php
class AreaModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    /** Obtener todas las áreas */
        public function obtenerAreas() {
        $stmt = $this->conexion->query("SELECT * FROM areas ORDER BY orden ASC, id_area ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }


    /** Crear nueva área */
    public function crearArea($nombre) {
        $stmt = $this->db->prepare("INSERT INTO areas (nombre) VALUES (?)");
        return $stmt->execute([$nombre]);
    }

    /** Editar área existente */
    public function editarArea($id_area, $nombre) {
        $stmt = $this->db->prepare("UPDATE areas SET nombre = ? WHERE id_area = ?");
        return $stmt->execute([$nombre, $id_area]);
    }

    /** Eliminar área (y sus mesas si hay FK en cascada) */
    public function eliminarArea($id_area) {
        $stmt = $this->db->prepare("DELETE FROM areas WHERE id_area = ?");
        return $stmt->execute([$id_area]);
    }

    /** Verificar si un área ya existe */
    public function areaExiste($nombre, $id_area = null) {
        $sql = "SELECT COUNT(*) FROM areas WHERE LOWER(nombre) = LOWER(?)";
        $params = [$nombre];

        if ($id_area) {
            $sql .= " AND id_area != ?";
            $params[] = $id_area;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
