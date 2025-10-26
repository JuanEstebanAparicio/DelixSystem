<?php
require_once __DIR__ . '/../../../../config/supabase.php';

class RolesModel {
    private $db;

    public function __construct() {
        global $conexion; // <- viene de supabase.php
        if (!$conexion) {
            throw new Exception("No se pudo establecer conexión con Supabase");
        }
        $this->db = $conexion;
    }

    public function getAllRoles() {
        try {
            $stmt = $this->db->query("SELECT id, nombre, descripcion FROM roles ORDER BY id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener roles: " . $e->getMessage());
        }
    }

    public function getRolesByEmployee($empleadoId) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.id, r.nombre, r.descripcion
                FROM roles r
                INNER JOIN empleados_roles er ON er.rol_id = r.id
                WHERE er.empleado_id = :empleado_id
            ");
            $stmt->execute(['empleado_id' => $empleadoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener roles del empleado: " . $e->getMessage());
        }
    }
}
