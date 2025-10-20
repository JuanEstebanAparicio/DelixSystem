<?php
require_once __DIR__ . '/../../../../config/supabase.php';

class DynamicKeyModel {
    private $conn;

    public function __construct() {
        global $conexion; // usa la variable global definida en config
        $this->conn = $conexion;
    }

    // 🔹 Obtener código activo no expirado y no usado
    public function getActiveKey($adminId) {
        $query = "SELECT * FROM dynamic_keys 
                  WHERE admin_id = :admin_id 
                  AND used = false 
                  AND expires_at > NOW()
                  ORDER BY created_at DESC 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':admin_id' => $adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Insertar un nuevo código
    public function createNewKey($adminId, $code, $expiresAt) {
        $query = "INSERT INTO dynamic_keys (admin_id, code, expires_at, used, created_at)
                  VALUES (:admin_id, :code, :expires_at, false, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':admin_id'   => $adminId,
            ':code'       => $code,
            ':expires_at' => $expiresAt
        ]);
    }

    // 🔹 Marcar códigos antiguos como usados/expirados
    public function expireOldKeys($adminId) {
        $query = "UPDATE dynamic_keys 
                  SET used = true 
                  WHERE admin_id = :admin_id 
                  AND (expires_at <= NOW() OR used = false)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':admin_id' => $adminId]);
    }
}
?>
