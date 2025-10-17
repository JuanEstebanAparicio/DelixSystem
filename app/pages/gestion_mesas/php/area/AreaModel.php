<?php
class AreaModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    /** Obtener todas las áreas */
    public function obtenerAreas() {
        // ✅ Corregimos uso de la conexión y añadimos orden
        $stmt = $this->db->query("SELECT * FROM areas ORDER BY orden ASC, id_area ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Crear nueva área */
    public function crearArea($nombre) {
        // ✅ Primero insertamos el área
        $stmt = $this->db->prepare("INSERT INTO areas (nombre) VALUES (?)");
        $ok = $stmt->execute([$nombre]);

        if ($ok) {
            // ✅ Luego asignamos el orden automáticamente al final
            $id_area = $this->db->lastInsertId();
            $stmtOrden = $this->db->query("SELECT COALESCE(MAX(orden), 0) + 1 AS nuevo_orden FROM areas");
            $nuevoOrden = (int)$stmtOrden->fetchColumn();

            $update = $this->db->prepare("UPDATE areas SET orden = ? WHERE id_area = ?");
            $update->execute([$nuevoOrden, $id_area]);
        }

        return $ok;
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

    /** ✅ Nuevo: actualizar orden de las áreas (para drag & drop) */
    public function actualizarOrden(array $ids) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE areas SET orden = :orden WHERE id_area = :id");
            foreach ($ids as $index => $id_area) {
                $orden = $index + 1;
                $stmt->execute([':orden' => $orden, ':id' => $id_area]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}
