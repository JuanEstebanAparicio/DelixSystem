<?php
class DynamicKeyModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
        // 🔧 Pequeña optimización: usar modo real de prepares para rendimiento
        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * 🔍 Obtiene la clave activa de un usuario
     */
    public function getActiveKey($userId) {
        $stmt = $this->db->prepare("
            SELECT code, expires_at
            FROM dynamic_keys
            WHERE user_id = :user_id
              AND active = TRUE
              AND expires_at > NOW()
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(["user_id" => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 🧩 Crea un nuevo código dinámico
     */
    public function createKey($userId, $code, $expiresAt) {
        // ✅ Asegura unicidad del código dentro del mismo usuario
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

    /**
     * 📴 Desactiva todas las claves previas del usuario
     */
    public function deactivateOldKeys($userId) {
        $stmt = $this->db->prepare("
            UPDATE dynamic_keys
            SET active = FALSE
            WHERE user_id = :user_id
              AND active = TRUE
        ");
        $stmt->execute(["user_id" => $userId]);
    }

    /**
     * ⏰ Expira las claves que ya vencieron
     */
    public function expireKey($userId) {
        $stmt = $this->db->prepare("
            UPDATE dynamic_keys
            SET active = FALSE
            WHERE user_id = :user_id
              AND expires_at < NOW()
              AND active = TRUE
        ");
        $stmt->execute(["user_id" => $userId]);
    }

    /**
     * 🧹 Limpia claves viejas e inactivas (más de 24h)
     */
    public function cleanOldKeys($userId) {
        $stmt = $this->db->prepare("
            DELETE FROM dynamic_keys
            WHERE user_id = :user_id
              AND (active = FALSE OR expires_at < NOW() - INTERVAL '1 day')
        ");
        $stmt->execute(["user_id" => $userId]);
    }
}
?>
