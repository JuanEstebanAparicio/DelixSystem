<?php
require_once __DIR__ . '/../../../../config/supabase.php';

class DynamicKeyModel {
    private $conn;
    public function __construct() {
        global $conexion;
        $this->conn = $conexion;
    }

    // 🔹 Get current valid code
    public function getActiveKey($userId) {
        $sql = "SELECT * FROM dynamic_keys
                WHERE user_id = :user_id
                  AND used = false
                  AND expires_at > NOW()
                ORDER BY created_at DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Expire old codes
    public function expireOldKeys($userId) {
        $sql = "UPDATE dynamic_keys
                SET used = true
                WHERE user_id = :user_id
                  AND (expires_at <= NOW() OR used = false)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
    }

    // 🔹 Create a new key
    public function createNewKey($userId, $code, $expiresAt) {
        $sql = "INSERT INTO dynamic_keys (user_id, code, expires_at, used)
                VALUES (:user_id, :code, :expires_at, false)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':code' => $code,
            ':expires_at' => $expiresAt
        ]);
    }
}
?>
