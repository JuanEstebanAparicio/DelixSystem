<?php
require_once(dirname(__DIR__, 3) . '/config/supabase.php');
require_once(__DIR__ . '/products.php');

class storage_crud {
    private $pdo;

    public function __construct($pdo = null) {
        global $conexion;
        $this->pdo = $pdo ?? $conexion;
    }

    public function createProduct(Product $product) {
        $sql = "INSERT INTO storage 
            (id_user, name, amount, minimum_quantity, unit, unit_cost, category, entrance_date, expiration_date, batch, description, location, state, supplier, photo)
            VALUES (:id_user, :name, :amount, :minimum_quantity, :unit, :unit_cost, :category, :entrance_date, :expiration_date, :batch, :description, :location, :state, :supplier, :photo)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_user' => $product->getIdUser(),
            ':name' => $product->getName(),
            ':amount' => $product->getAmount(),
            ':minimum_quantity' => $product->getMinimumQuantity(),
            ':unit' => $product->getUnit(),
            ':unit_cost' => $product->getUnitCost(),
            ':category' => $product->getCategory(),
            ':entrance_date' => $product->getEntranceDate(),
            ':expiration_date' => $product->getExpirationDate(),
            ':batch' => $product->getBatch(),
            ':description' => $product->getDescription(),
            ':location' => $product->getLocation(),
            ':state' => $product->getState(),
            ':supplier' => $product->getSupplier(),
            ':photo' => $product->getPhoto()
        ]);
        return true;
    }

    public function updateProduct(Product $product, $id) {
        $sql = "UPDATE storage SET 
            name = :name,
            amount = :amount,
            minimum_quantity = :minimum_quantity,
            unit = :unit,
            unit_cost = :unit_cost,
            category = :category,
            entrance_date = :entrance_date,
            expiration_date = :expiration_date,
            batch = :batch,
            description = :description,
            location = :location,
            state = :state,
            supplier = :supplier,
            photo = :photo
            WHERE id = :id AND id_user = :id_user";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name' => $product->getName(),
            ':amount' => $product->getAmount(),
            ':minimum_quantity' => $product->getMinimumQuantity(),
            ':unit' => $product->getUnit(),
            ':unit_cost' => $product->getUnitCost(),
            ':category' => $product->getCategory(),
            ':entrance_date' => $product->getEntranceDate(),
            ':expiration_date' => $product->getExpirationDate(),
            ':batch' => $product->getBatch(),
            ':description' => $product->getDescription(),
            ':location' => $product->getLocation(),
            ':state' => $product->getState(),
            ':supplier' => $product->getSupplier(),
            ':photo' => $product->getPhoto(),
            ':id_user' => $product->getIdUser(),
            ':id' => $id
        ]);
        return true;
    }

    public function deleteProduct($id, $id_user) {
        $sql = "DELETE FROM storage WHERE id = :id AND id_user = :id_user";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':id_user' => $id_user]);
        return true;
    }

    public function getAllProductsByUser($id_user) {
        $sql = "SELECT * FROM storage WHERE id_user = :id_user ORDER BY id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_user' => $id_user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id, $id_user) {
        $sql = "SELECT * FROM storage WHERE id = :id AND id_user = :id_user";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':id_user' => $id_user]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
