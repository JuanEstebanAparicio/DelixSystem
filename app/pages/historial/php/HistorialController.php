<?php
require_once __DIR__ . "/../../../../middleware/universal_guard.php";
require_once __DIR__ . "/../../../../config/supabase.php";
require_once "HistorialModel.php";

header("Content-Type: application/json");

$u = universalGuard();  // Detecta owner o empleado

if (!$u || !isset($u['id'])) {
    echo json_encode(["status" => false, "error" => "No autorizado"]);
    exit;
}

$ownerId = ($u['tipo'] === "propietario")
    ? $u['id']
    : $u['restaurant_id'];

if (!$ownerId) {
    echo json_encode(["status" => false, "error" => "No se pudo determinar el owner"]);
    exit;
}

$model = new HistorialModel($conexion);
$action = $_GET['action'] ?? 'list';

switch ($action) {

    case "list":
        try {
            $data = $model->getAuditsByOwner($ownerId);
            echo json_encode(["status" => true, "data" => $data]);
        } catch (Exception $e) {
            echo json_encode(["status" => false, "error" => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(["status" => false, "error" => "Acción no válida"]);
}
