<?php

class dishes {
    private $id;
    private $name_dish;
    private $price;
    private $category;
    private $description;
    private $state;
    private $created_at;
    private $photo;

    public function __construct($id, $name_dish, $price, $category, $description, $state, $created_at, $photo) {
        $this->id = $id;
        $this->name_dish = $name_dish;
        $this->price = $price;
        $this->category = $category;
        $this->description = $description;
        $this->state = $state;
        $this->created_at = $created_at;
        $this->photo = $photo;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNameDish() { return $this->name_dish; }
    public function getPrice() { return $this->price; }
    public function getCategory() { return $this->category; }
    public function getDescription() { return $this->description; }
    public function getState() { return $this->state; }
    public function getCreatedAt() { return $this->created_at; }
    public function getPhoto() { return $this->photo; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNameDish($name_dish) { $this->name_dish = $name_dish; }
    public function setPrice($price) { $this->price = $price; }
    public function setCategory($category) { $this->category = $category; }
    public function setDescription($description) { $this->description = $description; }
    public function setState($state) { $this->state = $state; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setPhoto($photo) { $this->photo = $photo; }
}

?>
