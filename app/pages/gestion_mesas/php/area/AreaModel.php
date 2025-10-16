<?php
class AreaModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    /** Obtener todas las áreas */
    public function obtenerAreas() {
        $stmt = $this->db->query("SELECT * FROM areas ORDER BY id_area");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Crear área */
    public function crearArea($nombre) {
        $stmt = $this->db->prepare("INSERT INTO areas (nombre) VALUES (?)");
        return $stmt->execute([$nombre]);
    }

    /** Editar área */
    public function editarArea($id_area, $nombre) {
        $stmt = $this->db->prepare("UPDATE areas SET nombre = ? WHERE id_area = ?");
        return $stmt->execute([$nombre, $id_area]);
    }

    /** Eliminar área (y sus mesas en cascada si aplica) */
    public function eliminarArea($id_area) {
        $stmt = $this->db->prepare("DELETE FROM areas WHERE id_area = ?");
        return $stmt->execute([$id_area]);
    }

    /** Verificar si el área ya existe */
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
