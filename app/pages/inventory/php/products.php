<?php

class Product {
    private $name;
    private $quantity;
    private $minimum_quantity;
    private $unit;
    private $unit_cost;
    private $category;
    private $entry_date;
    private $expiration_date;
    private $batch;
    private $description;
    private $location;
    private $status;
    private $supplier;
    private $photo;

    public function __construct($name, $quantity, $minimum_quantity, $unit, $unit_cost, $category, $entry_date, $expiration_date, $batch, $description, $location, $status, $supplier, $photo) {
        $this->name = $name;
        $this->quantity = $quantity;
        $this->minimum_quantity = $minimum_quantity;
        $this->unit = $unit;
        $this->unit_cost = $unit_cost;
        $this->category = $category;
        $this->entry_date = $entry_date;
        $this->expiration_date = $expiration_date;
        $this->batch = $batch;
        $this->description = $description;
        $this->location = $location;
        $this->status = $status;
        $this->supplier = $supplier;
        $this->photo = $photo;
    }
    public function getName() { return $this->name; }
    public function getQuantity() { return $this->quantity; }
    public function getMinimumQuantity() { return $this->minimum_quantity; }
    public function getUnit() { return $this->unit; }
    public function getUnitCost() { return $this->unit_cost; }
    public function getCategory() { return $this->category; }
    public function getEntryDate() { return $this->entry_date; }
    public function getExpirationDate() { return $this->expiration_date; }
    public function getBatch() { return $this->batch; }
    public function getDescription() { return $this->description; }
    public function getLocation() { return $this->location; }
    public function getStatus() { return $this->status; }
    public function getSupplier() { return $this->supplier; }
    public function getPhoto() { return $this->photo; }

    public function setName($name) { $this->name = $name; }
    public function setQuantity($quantity) { $this->quantity = $quantity; }
    public function setMinimumQuantity($minimum_quantity) { $this->minimum_quantity = $minimum_quantity; }
    public function setUnit($unit) { $this->unit = $unit; }
    public function setUnitCost($unit_cost) { $this->unit_cost = $unit_cost; }
    public function setCategory($category) { $this->category = $category; }
    public function setEntryDate($entry_date) { $this->entry_date = $entry_date; }
    public function setExpirationDate($expiration_date) { $this->expiration_date = $expiration_date; }
    public function setBatch($batch) { $this->batch = $batch; }
    public function setDescription($description) { $this->description = $description; }
    public function setLocation($location) { $this->location = $location; }
    public function setStatus($status) { $this->status = $status; }
    public function setSupplier($supplier) { $this->supplier = $supplier; }
    public function setPhoto($photo) { $this->photo = $photo; }
}

?>
