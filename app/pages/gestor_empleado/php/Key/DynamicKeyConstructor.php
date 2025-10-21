<?php
class DynamicKey {
  public $id;
  public $user_id;
  public $code;
  public $expires_at;
  public $active;
  public $created_at;

  public function __construct($data) {
    $this->id = $data['id'] ?? null;
    $this->user_id = $data['user_id'] ?? null;
    $this->code = $data['code'] ?? null;
    $this->expires_at = $data['expires_at'] ?? null;
    $this->active = $data['active'] ?? true;
    $this->created_at = $data['created_at'] ?? null;
  }
}
?>
