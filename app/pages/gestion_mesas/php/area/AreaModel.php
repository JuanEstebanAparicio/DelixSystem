<?php
class AreaModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    /** Obtener áreas del usuario actual */
    public function obtenerAreas($id_usuario) {
        $stmt = $this->db->prepare("SELECT * FROM areas WHERE id_usuario = ? ORDER BY orden ASC, id_area ASC");
        $stmt->execute([$id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Crear nueva área */
   public function crearArea($nombre, $id_usuario, $nombre_restaurante) {
    $stmt = $this->db->prepare("INSERT INTO areas (nombre, id_usuario, nombre_restaurante) VALUES (?, ?, ?)");
    $ok = $stmt->execute([$nombre, $id_usuario, $nombre_restaurante]);

    if ($ok) {
        $id_area = $this->db->lastInsertId();
        $stmtOrden = $this->db->prepare("SELECT COALESCE(MAX(orden), 0) + 1 AS nuevo_orden FROM areas WHERE id_usuario = ?");
        $stmtOrden->execute([$id_usuario]);
        $nuevoOrden = (int)$stmtOrden->fetchColumn();

        $update = $this->db->prepare("UPDATE areas SET orden = ? WHERE id_area = ?");
        $update->execute([$nuevoOrden, $id_area]);
    }

    return $ok;
}


    /** Editar área existente */
    public function editarArea($id_area, $nombre, $id_usuario) {
        $stmt = $this->db->prepare("UPDATE areas SET nombre = ? WHERE id_area = ? AND id_usuario = ?");
        return $stmt->execute([$nombre, $id_area, $id_usuario]);
    }

    /** Eliminar área (solo del usuario actual) */
    public function eliminarArea($id_area, $id_usuario) {
        $stmt = $this->db->prepare("DELETE FROM areas WHERE id_area = ? AND id_usuario = ?");
        return $stmt->execute([$id_area, $id_usuario]);
    }

    /** Verificar si un área ya existe (por usuario) */
    public function areaExiste($nombre, $id_usuario, $id_area = null) {
        $sql = "SELECT COUNT(*) FROM areas WHERE LOWER(nombre) = LOWER(?) AND id_usuario = ?";
        $params = [$nombre, $id_usuario];

        if ($id_area) {
            $sql .= " AND id_area != ?";
            $params[] = $id_area;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /** Actualizar orden de las áreas (Drag & Drop) */
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
