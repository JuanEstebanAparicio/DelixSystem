<?php
require_once(dirname(__DIR__, 3) . '/config/supabase.php');
require_once(__DIR__ . '/dishes.php');

class dishes_crud {
    private $pdo;

    public function __construct($pdo = null) {
        global $conexion;
        $this->pdo = $pdo ?? $conexion;
    }

    public function createDish(dishes $dish) {
        try {
            $sql = "INSERT INTO dish 
                (name_dish, price, category, description, state, created_at, photo)
                VALUES (:name_dish, :price, :category, :description, :state, :created_at, :photo)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name_dish'   => $dish->getNameDish(),
                ':price'       => $dish->getPrice(),
                ':category'    => $dish->getCategory(),
                ':description' => $dish->getDescription(),
                ':state'       => $dish->getState(),
                ':created_at'  => $dish->getCreatedAt(),
                ':photo'       => $dish->getPhoto()
            ]);

            return true;
        } catch (PDOException $e) {
            die("Error al crear plato: " . $e->getMessage());
        }
    }

    public function updateDish(dishes $dish, $id) {
        try {
            $sql = "UPDATE dish SET 
                name_dish = :name_dish,
                price = :price,
                category = :category,
                description = :description,
                state = :state,
                created_at = :created_at,
                photo = :photo
                WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name_dish'   => $dish->getNameDish(),
                ':price'       => $dish->getPrice(),
                ':category'    => $dish->getCategory(),
                ':description' => $dish->getDescription(),
                ':state'       => $dish->getState(),
                ':created_at'  => $dish->getCreatedAt(),
                ':photo'       => $dish->getPhoto(),
                ':id'          => $id
            ]);

            return true;
        } catch (PDOException $e) {
            die("Error al actualizar plato: " . $e->getMessage());
        }
    }

    public function deleteDish($id) {
        try {
            $sql = "DELETE FROM dish WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            die("Error al eliminar plato: " . $e->getMessage());
        }
    }

    public function getAllDishes() {
        try {
            $sql = "SELECT * FROM dish ORDER BY category, name_dish ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener platos: " . $e->getMessage());
        }
    }

    public function getDishById($id) {
        try {
            $sql = "SELECT * FROM dish WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener plato: " . $e->getMessage());
        }
    }
}
?>
