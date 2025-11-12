<?php
require_once "../../../../config/supabase.php";
require_once "combo_crud.php";

$action = $_POST['action'] ?? '';
$response = ["success" => false, "error" => "Acción no válida"];

switch ($action) {
    case 'add':
        $response = addCombo($_POST);
        break;
    case 'edit':
        $response = editCombo($_POST);
        break;
    case 'delete':
        $response = deleteCombo($_POST['id']);
        break;
}

echo json_encode($response);
?>
