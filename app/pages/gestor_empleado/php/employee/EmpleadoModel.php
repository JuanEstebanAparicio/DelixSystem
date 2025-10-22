<?php
class EmpleadoModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function verifyCode($code) {
        $stmt = $this->db->prepare("
            SELECT user_id, expires_at, active 
            FROM dynamic_keys
            WHERE code = :code
            LIMIT 1
        ");
        $stmt->execute(['code' => $code]);
        $key = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$key) return ["valid" => false, "msg" => "Invalid code"];
        if (!$key['active']) return ["valid" => false, "msg" => "Code is inactive"];
        if (strtotime($key['expires_at']) < time()) return ["valid" => false, "msg" => "Code expired"];

        return ["valid" => true, "user_id" => $key['user_id']];
    }

    public function registerEmployee($userId, $name, $email, $document, $role = "Empleado") {
        $stmt = $this->db->prepare("
            INSERT INTO employees (user_id, full_name, email, document, role, created_at)
            VALUES (:user_id, :full_name, :email, :document, :role, NOW())
        ");
        $stmt->execute([
            'user_id' => $userId,
            'full_name' => $name,
            'email' => $email,
            'document' => $document,
            'role' => $role
        ]);
        return $this->db->lastInsertId();
    }

    public function deactivateCode($code) {
        $stmt = $this->db->prepare("UPDATE dynamic_keys SET active = FALSE WHERE code = :code");
        $stmt->execute(['code' => $code]);
    }
}
?>
