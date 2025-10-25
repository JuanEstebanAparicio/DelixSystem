<?php
require_once(__DIR__ . '/dishes_crud.php');
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "error" => "ID no proporcionado."]);
    exit;
}

$id = $_GET['id'];
$crud = new dishes_crud();

try {
    $crud->deleteDish($id);
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    error_log("Error al eliminar plato: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
