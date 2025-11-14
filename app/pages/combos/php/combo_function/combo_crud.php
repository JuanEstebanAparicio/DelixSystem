<?php
require_once(dirname(__DIR__, 4) . '/config/supabase.php');
require_once(__DIR__ . '/combo_constructor.php');

class combo_crud {
    private $pdo;

    public function __construct($pdo = null) {
        global $conexion;
        $this->pdo = $pdo ?? $conexion;
    }

    public function createCombo(combo_constructor $combo) {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO combo_day 
                    (id_user, id_dish, state, description, created_at, type, start_time, end_time, start_date, end_date, photo)
                    VALUES 
                    (:id_user, :id_dish, :state, :description, :created_at, :type, :start_time, :end_time, :start_date, :end_date, :photo)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_user'     => $combo->getIdUser(),
                ':id_dish'     => $combo->getIdDish(),
                ':state'       => $combo->getState(),
                ':description' => $combo->getDescription(),
                ':created_at'  => $combo->getCreatedAt(),
                ':type'        => $combo->getType(),
                ':start_time'  => $combo->getStartTime(),
                ':end_time'    => $combo->getEndTime(),
                ':start_date'  => $combo->getStartDate(),
                ':end_date'    => $combo->getEndDate(),
                ':photo'       => $combo->getPhoto()
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al crear combo: " . $e->getMessage());
        }
    }

    public function updateCombo(combo_constructor $combo, $id) {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE combo_day SET
                        id_dish = :id_dish,
                        state = :state,
                        description = :description,
                        type = :type,
                        start_time = :start_time,
                        end_time = :end_time,
                        start_date = :start_date,
                        end_date = :end_date,
                        photo = :photo
                    WHERE id = :id AND id_user = :id_user";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id'          => $id,
                ':id_user'     => $combo->getIdUser(),
                ':id_dish'     => $combo->getIdDish(),
                ':state'       => $combo->getState(),
                ':description' => $combo->getDescription(),
                ':type'        => $combo->getType(),
                ':start_time'  => $combo->getStartTime(),
                ':end_time'    => $combo->getEndTime(),
                ':start_date'  => $combo->getStartDate(),
                ':end_date'    => $combo->getEndDate(),
                ':photo'       => $combo->getPhoto()
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al actualizar combo: " . $e->getMessage());
        }
    }

    public function getComboById($id, $id_user) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM combo_day WHERE id = :id AND id_user = :id_user");
            $stmt->execute([':id' => $id, ':id_user' => $id_user]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener combo: " . $e->getMessage());
        }
    }

    public function deleteCombo($id, $id_user) {
        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare("DELETE FROM combo_day WHERE id = :id AND id_user = :id_user")
                      ->execute([':id' => $id, ':id_user' => $id_user]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al eliminar combo: " . $e->getMessage());
        }
    }
}
?>
