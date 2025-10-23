<?php
class EmpleadoModel {
    private $db;

    public function __construct(PDO $conexion) {
        $this->db = $conexion;
    }

    /**
     * 🔍 Verifica si un código dinámico es válido y obtiene el user_id del propietario
     */
    public function verifyCode(string $code): array {
        $stmt = $this->db->prepare("
            SELECT user_id, expires_at, active 
            FROM dynamic_keys
            WHERE code = :code
            LIMIT 1
        ");
        $stmt->execute(['code' => $code]);
        $key = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$key) {
            return ["valid" => false, "msg" => "El código ingresado no existe."];
        }

        if (!$key['active']) {
            return ["valid" => false, "msg" => "El código ya no está activo."];
        }

        if (strtotime($key['expires_at']) < time()) {
            return ["valid" => false, "msg" => "El código ha expirado."];
        }

        return ["valid" => true, "user_id" => $key['user_id']];
    }

    /**
     * 👥 Registra un nuevo empleado asociado a un propietario (user_id)
     */
    public function registerEmployee(
        int $userId,
        string $name,
        string $email,
        string $document,
        string $role = "Empleado"
    ): bool {
        try {
            // 🧠 Evitar duplicados por documento o email dentro del mismo user_id
            $check = $this->db->prepare("
                SELECT id FROM employees 
                WHERE user_id = :user_id 
                  AND (email = :email OR document = :document)
                LIMIT 1
            ");
            $check->execute([
                'user_id' => $userId,
                'email' => $email,
                'document' => $document
            ]);

            if ($check->fetch()) {
                throw new Exception("Este empleado ya está registrado en tu cuenta.");
            }

            // 🆕 Registrar el nuevo empleado
            $stmt = $this->db->prepare("
                INSERT INTO employees (user_id, full_name, email, document, role, created_at)
                VALUES (:user_id, :full_name, :email, :document, :role, NOW())
            ");
            return $stmt->execute([
                'user_id' => $userId,
                'full_name' => $name,
                'email' => $email,
                'document' => $document,
                'role' => $role
            ]);
        } catch (Exception $e) {
            throw new Exception("Error al registrar empleado: " . $e->getMessage());
        }
    }

    /**
     * 📴 Desactiva el código dinámico una vez usado
     */
    public function deactivateCode(string $code): void {
        $stmt = $this->db->prepare("
            UPDATE dynamic_keys 
            SET active = FALSE 
            WHERE code = :code
        ");
        $stmt->execute(['code' => $code]);
    }

    public function deleteEmployee($id) {
    try {
        $stmt = $this->db->prepare("DELETE FROM employees WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        error_log("Error al eliminar empleado: " . $e->getMessage());
        return false;
    }
}
}
?>
