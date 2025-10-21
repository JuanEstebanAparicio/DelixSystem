<?php
require_once __DIR__ . '/../../../../config/supabase.php';

class EmpleadoModel {
    private $conn;

    public function __construct() {
        global $conexion;
        $this->conn = $conexion;
    }

    // 🔹 Register a new employee
    public function registerEmployee($adminId, $name, $email, $document) {
        $query = "INSERT INTO employees (admin_id, full_name, email, document, created_at)
                  VALUES (:admin_id, :full_name, :email, :document, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':admin_id' => $adminId,
            ':full_name' => $name,
            ':email' => $email,
            ':document' => $document
        ]);
        return $this->conn->lastInsertId();
    }

    // 🔹 Check if employee already exists for that admin
    public function employeeExists($email, $adminId) {
        $query = "SELECT id FROM employees WHERE email = :email AND admin_id = :admin_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':email' => $email, ':admin_id' => $adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
