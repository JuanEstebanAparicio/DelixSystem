<?php
class DynamicKeyModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function getActiveKey($userId) {
        $stmt = $this->db->prepare("
            SELECT code, expires_at
            FROM dynamic_keys
            WHERE user_id = :user_id AND active = TRUE
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(["user_id" => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createKey($userId, $code, $expiresAt) {
        $stmt = $this->db->prepare("
            INSERT INTO dynamic_keys (user_id, code, expires_at, active, created_at)
            VALUES (:user_id, :code, :expires_at, TRUE, NOW())
        ");
        $stmt->execute([
            "user_id" => $userId,
            "code" => $code,
            "expires_at" => $expiresAt
        ]);
    }

    public function deactivateOldKeys($userId) {
        $stmt = $this->db->prepare("
            UPDATE dynamic_keys
            SET active = FALSE
            WHERE user_id = :user_id
        ");
        $stmt->execute(["user_id" => $userId]);
    }

    public function expireKey($userId) {
        $stmt = $this->db->prepare("
            UPDATE dynamic_keys
            SET active = FALSE
            WHERE user_id = :user_id
            AND expires_at < NOW()
        ");
        $stmt->execute(["user_id" => $userId]);
    }

    public function cleanOldKeys($userId) {
        $stmt = $this->db->prepare("
        DELETE FROM dynamic_keys
        WHERE user_id = :user_id
        AND (active = FALSE OR expires_at < NOW())
        ");
        $stmt->execute(["user_id" => $userId]);
}

}
?>
