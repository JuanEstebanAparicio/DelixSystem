<?php
$baseDir = dirname(dirname(__DIR__));
require_once($baseDir . '../Model/Entity/dishes.php');

class dishes_crud {
	private $pdo;

	public function __construct($pdo = null) {
        if ($pdo === null) {
            $this->pdo = Connection::getConnection();
        } else {
            $this->pdo = $pdo;
        }
    }

	// Create
	public function createDish(dishes $dish) {
		if (empty($dish->getNameDish()) || empty($dish->getPrice()) || empty($dish->getCategory())) {
			throw new Exception("Los campos obligatorios no pueden estar vacíos.");
		}

		$sql = "INSERT INTO dishes 
			(name_dish, price, category, description, state, created_at, photo)
			VALUES (?, ?, ?, ?, ?, ?, ?)";

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			$dish->getNameDish(),
			$dish->getPrice(),
			$dish->getCategory(),
			$dish->getDescription(),
			$dish->getState(),
			$dish->getCreatedAt(),
			$dish->getPhoto()
		]);

		return true;
	}

	// Update
	public function updateDish(dishes $dish, $id) {
		$sql = "UPDATE dishes SET 
			name_dish = ?, 
			price = ?, 
			category = ?, 
			description = ?, 
			state = ?, 
			created_at = ?, 
			photo = ?
			WHERE id = ?";

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			$dish->getNameDish(),
			$dish->getPrice(),
			$dish->getCategory(),
			$dish->getDescription(),
			$dish->getState(),
			$dish->getCreatedAt(),
			$dish->getPhoto(),
			$id
		]);

		return true;
	}

	// Delete
	public function deleteDish($id) {
		$sql = "DELETE FROM dishes WHERE id = ?";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([$id]);

		return true;
	}

	
}
?>
