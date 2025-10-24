<?php
require_once __DIR__ . '/../../../../config/supabase.php';

class RolesModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllRoles() {
        try {
            $sql = "SELECT id, nombre, descripcion FROM roles ORDER BY id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
