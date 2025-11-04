<?php
require_once __DIR__ . '/products.php';

class storage_crud {

    private $conn;

    public function __construct() {
        require_once __DIR__ . '/../../../../config/supabase.php';
        $this->conn = $conexion;
    }

    public function getAll($id_user) {
        $sql = "SELECT * FROM storage WHERE id_user = :id_user ORDER BY category, name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id, $id_user) {
        $sql = "SELECT * FROM storage WHERE id = :id AND id_user = :id_user";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertProduct(Product $p) {
        $sql = "INSERT INTO storage (id_user, name, amount, minimum_quantity, unit, unit_cost, category, entrance_date, expiration_date, batch, description, location, state, supplier, photo)
                VALUES (:id_user, :name, :amount, :minimum_quantity, :unit, :unit_cost, :category, :entrance_date, :expiration_date, :batch, :description, :location, :state, :supplier, :photo)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':id_user', $p->getIdUser());
        $stmt->bindValue(':name', $p->getName());
        $stmt->bindValue(':amount', $p->getAmount());
        $stmt->bindValue(':minimum_quantity', $p->getMinimumQuantity());
        $stmt->bindValue(':unit', $p->getUnit());
        $stmt->bindValue(':unit_cost', $p->getUnitCost());
        $stmt->bindValue(':category', $p->getCategory());
        $stmt->bindValue(':entrance_date', $p->getEntranceDate());
        $stmt->bindValue(':expiration_date', $p->getExpirationDate());
        $stmt->bindValue(':batch', $p->getBatch());
        $stmt->bindValue(':description', $p->getDescription());
        $stmt->bindValue(':location', $p->getLocation());
        $stmt->bindValue(':state', $p->getState());
        $stmt->bindValue(':supplier', $p->getSupplier());
        $stmt->bindValue(':photo', $p->getPhoto());

        return $stmt->execute();
    }

    public function updateProduct(Product $p, $id) {
        $sql = "UPDATE storage SET name = :name, amount = :amount, minimum_quantity = :minimum_quantity, unit = :unit, unit_cost = :unit_cost, category = :category, entrance_date = :entrance_date, expiration_date = :expiration_date, batch = :batch, description = :description, location = :location, state = :state, supplier = :supplier, photo = :photo 
                WHERE id = :id AND id_user = :id_user";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':id_user', $p->getIdUser());
        $stmt->bindValue(':name', $p->getName());
        $stmt->bindValue(':amount', $p->getAmount());
        $stmt->bindValue(':minimum_quantity', $p->getMinimumQuantity());
        $stmt->bindValue(':unit', $p->getUnit());
        $stmt->bindValue(':unit_cost', $p->getUnitCost());
        $stmt->bindValue(':category', $p->getCategory());
        $stmt->bindValue(':entrance_date', $p->getEntranceDate());
        $stmt->bindValue(':expiration_date', $p->getExpirationDate());
        $stmt->bindValue(':batch', $p->getBatch());
        $stmt->bindValue(':description', $p->getDescription());
        $stmt->bindValue(':location', $p->getLocation());
        $stmt->bindValue(':state', $p->getState());
        $stmt->bindValue(':supplier', $p->getSupplier());
        $stmt->bindValue(':photo', $p->getPhoto());

        return $stmt->execute();
    }

    public function deleteProduct($id, $id_user) {
        $sql = "DELETE FROM storage WHERE id = :id AND id_user = :id_user";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_user', $id_user);
        return $stmt->execute();
    }
}
?>
