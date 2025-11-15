<?php
class AreaModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    /** Obtener áreas del propietario o del empleado vinculado */
    public function obtenerAreasAdaptable($id_usuario, $conexion) {
        // 🔹 Determinar si es propietario
        $stmt = $conexion->prepare("SELECT rol FROM usuarios WHERE id = ?");
        $stmt->execute([$id_usuario]);
        $rol = $stmt->fetchColumn();

        if ($rol === 'propietario') {
            $stmt = $this->db->prepare("
                SELECT * FROM areas 
                WHERE id_usuario = ? 
                ORDER BY orden ASC, id_area ASC
            ");
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // 🔹 Si es empleado
        $stmt = $conexion->prepare("SELECT user_id FROM employees WHERE id = ?");
        $stmt->execute([$id_usuario]);
        $id_propietario = $stmt->fetchColumn();

        if (!$id_propietario) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM areas 
            WHERE id_usuario = ? 
            ORDER BY orden ASC, id_area ASC
        ");
        $stmt->execute([$id_propietario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Obtener un área específica del propietario */
public function getAreaById($id_area, $id_usuario) {
    $stmt = $this->db->prepare("
        SELECT 
            id_area,
            nombre,
            id_usuario,
            restaurant_name,
            orden
        FROM areas
        WHERE id_area = ? 
          AND id_usuario = ?
        LIMIT 1
    ");

    $stmt->execute([$id_area, $id_usuario]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}


    /** Crear nueva área */
  public function crearArea($nombre, $id_usuario) {

    // sacamos el nombre del restaurante del usuario dueño
    $stmt = $this->db->prepare("SELECT restaurant_name FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $restaurantName = $stmt->fetchColumn() ?: 'Restaurante Delix';

    $stmt = $this->db->prepare("
        INSERT INTO areas (nombre, id_usuario, restaurant_name)
        VALUES (?, ?, ?)
    ");
    $ok = $stmt->execute([$nombre, $id_usuario, $restaurantName]);

    if ($ok) {
        $id_area = $this->db->lastInsertId();

        $stmtOrden = $this->db->prepare("SELECT COALESCE(MAX(orden), 0) + 1 FROM areas WHERE id_usuario = ?");
        $stmtOrden->execute([$id_usuario]);
        $nuevoOrden = (int)$stmtOrden->fetchColumn();

        $update = $this->db->prepare("UPDATE areas SET orden = ? WHERE id_area = ?");
        $update->execute([$nuevoOrden, $id_area]);
    }

    return $ok;
}


    /** Editar área */
    public function editarArea($id_area, $nombre, $id_usuario) {
        $stmt = $this->db->prepare("UPDATE areas SET nombre = ? WHERE id_area = ? AND id_usuario = ?");
        return $stmt->execute([$nombre, $id_area, $id_usuario]);
    }

    /** Eliminar área */
    public function eliminarArea($id_area, $id_usuario) {
        $stmt = $this->db->prepare("DELETE FROM areas WHERE id_area = ? AND id_usuario = ?");
        return $stmt->execute([$id_area, $id_usuario]);
    }

    /** Verificar duplicados */
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

    /** Actualizar orden */
    public function actualizarOrden(array $ids) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE areas SET orden = :orden WHERE id_area = :id");
            foreach ($ids as $index => $id_area) {
                $stmt->execute([':orden' => $index + 1, ':id' => $id_area]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Error al actualizar orden: " . $e->getMessage());
            return false;
        }
    }
}
