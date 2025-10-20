<?php
class DynamicKey {
  public $id;
  public $admin_id;
  public $code;
  public $expires_at;
  public $used;
  public $created_at;

  public function __construct($id, $admin_id, $code, $expires_at, $used, $created_at) {
    $this->id = $id;
    $this->admin_id = $admin_id;
    $this->code = $code;
    $this->expires_at = $expires_at;
    $this->used = $used;
    $this->created_at = $created_at;
  }
}
?>
