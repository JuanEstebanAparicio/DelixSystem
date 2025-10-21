<?php

class Product {
    private $name;
    private $amount;
    private $minimum_quantity;
    private $unit;
    private $unit_cost;
    private $category;
    private $entrance_date;
    private $expiration_date;
    private $batch;
    private $description;
    private $location;
    private $state;
    private $supplier;
    private $photo;

    public function __construct($name, $amount, $minimum_quantity, $unit, $unit_cost, $category, $entrance_date, $expiration_date, $batch, $description, $location, $state, $supplier, $photo) {
        $this->name = $name;
        $this->amount = $amount;
        $this->minimum_quantity = $minimum_quantity;
        $this->unit = $unit;
        $this->unit_cost = $unit_cost;
        $this->category = $category;
        $this->entrance_date = $entrance_date;
        $this->expiration_date = $expiration_date;
        $this->batch = $batch;
        $this->description = $description;
        $this->location = $location;
        $this->state = $state;
        $this->supplier = $supplier;
        $this->photo = $photo;
    }
    public function getName() { return $this->name; }
    public function getAmount() { return $this->amount; }
    public function getMinimumQuantity() { return $this->minimum_quantity; }
    public function getUnit() { return $this->unit; }
    public function getUnitCost() { return $this->unit_cost; }
    public function getCategory() { return $this->category; }
    public function getEntranceDate() { return $this->entrance_date; }
    public function getExpirationDate() { return $this->expiration_date; }
    public function getBatch() { return $this->batch; }
    public function getDescription() { return $this->description; }
    public function getLocation() { return $this->location; }
    public function getState() { return $this->state; }
    public function getSupplier() { return $this->supplier; }
    public function getPhoto() { return $this->photo; }

    public function setName($name) { $this->name = $name; }
    public function setAmount($amount) { $this->amount = $amount; }
    public function setMinimumQuantity($minimum_quantity) { $this->minimum_quantity = $minimum_quantity; }
    public function setUnit($unit) { $this->unit = $unit; }
    public function setUnitCost($unit_cost) { $this->unit_cost = $unit_cost; }
    public function setCategory($category) { $this->category = $category; }
    public function setEntranceDate($entrance_date) { $this->entrance_date = $entrance_date; }
    public function setExpirationDate($expiration_date) { $this->expiration_date = $expiration_date; }
    public function setBatch($batch) { $this->batch = $batch; }
    public function setDescription($description) { $this->description = $description; }
    public function setLocation($location) { $this->location = $location; }
    public function setState($state) { $this->state = $state; }
    public function setSupplier($supplier) { $this->supplier = $supplier; }
    public function setPhoto($photo) { $this->photo = $photo; }
}

?>
