<?php
// DelixSystem/app/pages/historial/php/HistorialController.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=utf-8");

// incluye PDO supabase.php (esta variable $conexion la usan tus modelos)
require_once __DIR__ . "/../../../config/supabase.php";

// incluye guard que usa supabaseRest / session (no redirigimos desde aquí)
require_once __DIR__ . "/../../../middleware/universal_guard.php";

// incluye el modelo
require_once __DIR__ . "/HistorialModel.php";

// obtener usuario (propietario o empleado)
$u = universalGuard();
if (!$u || !isset($u['id'])) {
    http_response_code(401);
    echo json_encode(["status" => false, "error" => "No autorizado"]);
    exit;
}

// Si es propietario: owner = id.
// Si es empleado: buscar owner consultando tabla employees POR ID (no confiar en session fields).
try {
    if ($u['tipo'] === 'propietario') {
        $ownerId = (int)$u['id'];
    } else {
        // empleado -> obtener user_id (owner) desde tabla employees
        $empleadoId = (int)$u['id'];

        $stmt = $conexion->prepare("SELECT user_id FROM employees WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $empleadoId]);
        $ownerId = $stmt->fetchColumn();

        if (!$ownerId) {
            http_response_code(400);
            echo json_encode(["status" => false, "error" => "No se pudo determinar el owner desde employees"]);
            exit;
        }
        $ownerId = (int)$ownerId;
    }

    // Instanciar modelo y devolver datos
    $model = new HistorialModel($conexion);

    $action = $_GET['action'] ?? 'list';
    switch ($action) {
        case 'list':
            // opcional: recibir limit desde querystring
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 300;
            $data = $model->getAuditsByOwner($ownerId, $limit);
            echo json_encode(["status" => true, "data" => $data]);
            break;

        default:
            http_response_code(400);
            echo json_encode(["status" => false, "error" => "Acción no válida"]);
            break;
    }

} catch (Throwable $e) {
    error_log("Error HistorialController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => false, "error" => "Error interno", "debug" => $e->getMessage()]);
    exit;
}
