<?php
require_once(dirname(__DIR__, 3) . '/config/supabase.php');
require_once(__DIR__ . '/dishes.php');

class dishes_crud {
    private $pdo;

    public function __construct($pdo = null) {
        global $conexion;
        $this->pdo = $pdo ?? $conexion;
    }

    public function createDish(dishes $dish, $ingredients = []) {
        try {
            $this->pdo->beginTransaction();

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

            $dishId = $this->pdo->lastInsertId();

            if (!empty($ingredients)) {
                $sqlIng = "INSERT INTO dish_ingredient (dish_id, ingredient_id, quantity_used, unit)
                           VALUES (:dish_id, :ingredient_id, :quantity_used, :unit)";
                $stmtIng = $this->pdo->prepare($sqlIng);

                foreach ($ingredients as $ing) {
                    $stmtIng->execute([
                        ':dish_id' => $dishId,
                        ':ingredient_id' => $ing['id'],
                        ':quantity_used' => $ing['quantity'] ?? 1,
                        ':unit' => $ing['unit'] ?? 'unidad'
                    ]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al crear plato: " . $e->getMessage());
        }
    }

    public function updateDish(dishes $dish, $id, $ingredients = []) {
        try {
            $this->pdo->beginTransaction();

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
 
            $this->pdo->prepare("DELETE FROM dish_ingredient WHERE dish_id = :id")->execute([':id' => $id]);

            if (!empty($ingredients)) {
                $sqlIng = "INSERT INTO dish_ingredient (dish_id, ingredient_id, quantity_used, unit)
                           VALUES (:dish_id, :ingredient_id, :quantity_used, :unit)";
                $stmtIng = $this->pdo->prepare($sqlIng);

                foreach ($ingredients as $ing) {
                    $stmtIng->execute([
                        ':dish_id' => $id,
                        ':ingredient_id' => $ing['id'],
                        ':quantity_used' => $ing['quantity'] ?? 1,
                        ':unit' => $ing['unit'] ?? 'unidad'
                    ]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al actualizar plato: " . $e->getMessage());
        }
    }
    public function deleteDish($id) {
        try {
            $this->pdo->beginTransaction();
            $this->pdo->prepare("DELETE FROM dish_ingredient WHERE dish_id = :id")->execute([':id' => $id]);
            $this->pdo->prepare("DELETE FROM dish WHERE id = :id")->execute([':id' => $id]);
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al eliminar plato: " . $e->getMessage());
        }
    }

    public function getAllDishes() {
        try {
            $sql = "SELECT * FROM dish ORDER BY category, name_dish ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener platos: " . $e->getMessage());
        }
    }

    public function getDishById($id) {
        try {
            $sql = "SELECT * FROM dish WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener plato: " . $e->getMessage());
        }
    }
}
?>
