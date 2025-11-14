<?php
class combo_constructor {
    private $id;
    private $id_user;
    private $id_dish;
    private $state;
    private $description;
    private $created_at;
    private $type;
    private $start_time;
    private $end_time;
    private $start_date;
    private $end_date;
    private $photo;
    public function __construct($id,$id_user,$id_dish,$state,$description,$created_at,$type,$start_time = null,$end_time = null,$start_date = null,$end_date = null,$photo = null) {
        $this->id = $id;
        $this->id_user = $id_user;
        $this->id_dish = $id_dish;
        $this->state = $state;
        $this->description = $description;
        $this->created_at = $created_at;
        $this->type = $type;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->photo = $photo;
    }
    public function getId() { return $this->id; }
    public function getIdUser() { return $this->id_user; }
    public function getIdDish() { return $this->id_dish; }
    public function getState() { return $this->state; }
    public function getDescription() { return $this->description; }
    public function getCreatedAt() { return $this->created_at; }
    public function getType() { return $this->type; }
    public function getStartTime() { return $this->start_time; }
    public function getEndTime() { return $this->end_time; }
    public function getStartDate() { return $this->start_date; }
    public function getEndDate() { return $this->end_date; }
    public function getPhoto() { return $this->photo; }
    public function setId($id) { $this->id = $id; }
    public function setIdUser($id_user) { $this->id_user = $id_user; }
    public function setIdDish($id_dish) { $this->id_dish = $id_dish; }
    public function setState($state) { $this->state = $state; }
    public function setDescription($description) { $this->description = $description; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setType($type) { $this->type = $type; }
    public function setStartTime($start_time) { $this->start_time = $start_time; }
    public function setEndTime($end_time) { $this->end_time = $end_time; }
    public function setStartDate($start_date) { $this->start_date = $start_date; }
    public function setEndDate($end_date) { $this->end_date = $end_date; }
    public function setPhoto($photo) { $this->photo = $photo; }
}

?>
