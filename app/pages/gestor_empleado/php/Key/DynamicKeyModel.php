<?php
require_once __DIR__ . '/../../../../config/supabase.php';
require_once __DIR__ . '/DynamicKeyConstructor.php';

class DynamicKeyModel {
    private $conn;

    public function __construct() {
        global $conexion;
        $this->conn = $conexion;
    }

    // 🔹 Obtener código activo
    public function getActiveKey($userId) {
        $query = "SELECT * FROM dynamic_keys 
                  WHERE user_id = :user_id AND active = TRUE 
                  ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? new DynamicKey($result) : null;
    }

    // 🔹 Desactivar códigos viejos
    public function deactivateOldKeys($userId) {
        $query = "UPDATE dynamic_keys SET active = FALSE 
                  WHERE user_id = :user_id AND active = TRUE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId]);
    }

    // 🔹 Crear un nuevo código
    public function createKey($userId, $code, $expiresAt) {
        $query = "INSERT INTO dynamic_keys (user_id, code, expires_at, active, created_at)
                  VALUES (:user_id, :code, :expires_at, TRUE, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':code' => $code,
            ':expires_at' => $expiresAt
        ]);
    }

    // 🔹 Marcar expirado
    public function expireKey($userId) {
        $query = "UPDATE dynamic_keys SET active = FALSE 
                  WHERE user_id = :user_id AND expires_at <= NOW() AND active = TRUE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId]);
    }
}
?>
