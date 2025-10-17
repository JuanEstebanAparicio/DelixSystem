<?php

$baseDir = dirname(dirname(__DIR__));
require_once($baseDir . '../Model/Entity/products.php');

class StorageCRUD {
    private $pdo;

    public function __construct($pdo = null) {
        if ($pdo === null) {
            $this->pdo = Connection::getConnection();
        } else {
            $this->pdo = $pdo;
        }
    }

    public function createProduct(Product $product) {
        if (empty($product->getName()) || empty($product->getQuantity()) || empty($product->getUnit())) {
            throw new Exception("Los campos obligatorios no pueden estar vacíos.");
        }

        $sql = "INSERT INTO insumos 
            (nombre, cantidad, cantidad_minima, unidad, costo_unitario, categoria, fecha_ingreso, fecha_vencimiento, lote, descripcion, ubicacion, estado, proveedor, foto)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $product->getName(),
            $product->getQuantity(),
            $product->getMinimumQuantity(),
            $product->getUnit(),
            $product->getUnitCost(),
            $product->getCategory(),
            $product->getEntryDate(),
            $product->getExpirationDate(),
            $product->getBatch(),
            $product->getDescription(),
            $product->getLocation(),
            $product->getStatus(),
            $product->getSupplier(),
            $product->getPhoto()
        ]);

        return true;
    }

    public function updateProduct(Product $product, $id) {
        $sql = "UPDATE insumos SET 
            nombre = ?, 
            cantidad = ?, 
            cantidad_minima = ?, 
            unidad = ?, 
            costo_unitario = ?, 
            categoria = ?, 
            fecha_ingreso = ?, 
            fecha_vencimiento = ?, 
            lote = ?, 
            descripcion = ?, 
            ubicacion = ?, 
            estado = ?, 
            proveedor = ?, 
            foto = ?
            WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $product->getName(),
            $product->getQuantity(),
            $product->getMinimumQuantity(),
            $product->getUnit(),
            $product->getUnitCost(),
            $product->getCategory(),
            $product->getEntryDate(),
            $product->getExpirationDate(),
            $product->getBatch(),
            $product->getDescription(),
            $product->getLocation(),
            $product->getStatus(),
            $product->getSupplier(),
            $product->getPhoto(),
            $id
        ]);

        return true;
    }

    // Delete
    public function deleteProduct($id) {
        $sql = "DELETE FROM insumos WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return true;
    }
}
?>
