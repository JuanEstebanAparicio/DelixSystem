<?php
$baseDir = dirname(__DIR__, 2);
require_once($baseDir . '/config/supabase.php');
require_once($baseDir . '/products.php');

class Storage_crud {
    private $pdo;

    public function __construct($pdo = null) {
        global $conexion;
        $this->pdo = $pdo ?? $conexion;
    }

    /** Crear un producto (INSERT en Supabase) */
    public function createProduct(Product $product) {
        try {
            $sql = "INSERT INTO storage 
                (name, amount, minimum_quantity, unit, unit_cost, category, entrance_date, expiration_date, batch, description, location, status, supplier, photo)
                VALUES (:name, :amount, :minimum_quantity, :unit, :unit_cost, :category, :entrance_date, :expiration_date, :batch, :description, :location, :status, :supplier, :photo)";
            
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
                ':status' => $product->getStatus(),
                ':supplier' => $product->getSupplier(),
                ':photo' => $product->getPhoto()
            ]);
            return true;
        } catch (PDOException $e) {
            die("Error al crear producto: " . $e->getMessage());
        }
    }

    /** Actualizar producto existente */
    public function updateProduct(Product $product, $id) {
        try {
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
                status = :status,
                supplier = :supplier,
                photo = :photo
                WHERE id = :id";

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
                ':status' => $product->getStatus(),
                ':supplier' => $product->getSupplier(),
                ':photo' => $product->getPhoto(),
                ':id' => $id
            ]);
            return true;
        } catch (PDOException $e) {
            die("Error al actualizar producto: " . $e->getMessage());
        }
    }

    /** Eliminar producto */
    public function deleteProduct($id) {
        try {
            $sql = "DELETE FROM storage WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            die("Error al eliminar producto: " . $e->getMessage());
        }
    }

    /** Obtener todos los productos */
    public function getAllProducts() {
        try {
            $sql = "SELECT * FROM storage ORDER BY id ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener productos: " . $e->getMessage());
        }
    }

    /** Obtener producto por ID */
    public function getProductById($id) {
        try {
            $sql = "SELECT * FROM storage WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener producto: " . $e->getMessage());
        }
    }
}
?>
