<?php
// DelixSystem/app/pages/pedidos/php/pedidosController.php
// Controlador para crear pedido + items (devuelta siempre JSON)

header("Content-Type: application/json; charset=utf-8");

// DEV: no mostrar errores en HTML (rompen JSON). Loggear los errores en el log de PHP.
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();

// ruta al config (desde app/pages/pedidos/php/ -> subir 3 niveles a app/ -> config/supabase.php)
$supabasePath = __DIR__ . '/../../../config/supabase.php';
if (!file_exists($supabasePath)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Config supabase no encontrada en: $supabasePath"
    ]);
    exit;
}

require_once $supabasePath;

// lee JSON entrante
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "JSON inválido: " . json_last_error_msg(),
        "raw" => substr($raw, 0, 200) // muestra un pedazo para depurar
    ]);
    exit;
}

// campos obligatorios mínimos
$required = ['id_user','restaurant_name','id_area','area','id_mesa','mesa','total','metodo_pago','items'];
foreach ($required as $r) {
    if (!array_key_exists($r, $data)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Falta campo requerido: $r"]);
        exit;
    }
}

if (!is_array($data['items']) || count($data['items']) === 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "items debe ser un array no vacío"]);
    exit;
}

try {
    // mapear variables
    $id_user = $data["id_user"];
    $restaurant_name = $data["restaurant_name"];
    $id_area = $data["id_area"];
    $area = $data["area"];
    $id_mesa = $data["id_mesa"];
    $mesa = $data["mesa"];
    $nombre_cliente = $data["nombre_cliente"] ?? null;
    $total = $data["total"];
    $metodo_pago = $data["metodo_pago"];
    $items = $data["items"];

    // empezar transacción (si tu $conexion lo soporta)
    if (isset($conexion) && $conexion instanceof PDO) {
        $conexion->beginTransaction();
    }

    // insertar orders
    $sql = "INSERT INTO orders(id_user, restaurant_name, id_area, area, id_mesa, mesa, nombre_cliente, total_pedido, metodo_pago)
            VALUES(:id_user, :restaurant_name, :id_area, :area, :id_mesa, :mesa, :nombre_cliente, :total_pedido, :metodo_pago)
            RETURNING id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_user"=>$id_user,
        ":restaurant_name"=>$restaurant_name,
        ":id_area"=>$id_area,
        ":area"=>$area,
        ":id_mesa"=>$id_mesa,
        ":mesa"=>$mesa,
        ":nombre_cliente"=>$nombre_cliente,
        ":total_pedido"=>$total,
        ":metodo_pago"=>$metodo_pago
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order || !isset($order['id'])) {
        if (isset($conexion) && $conexion instanceof PDO) $conexion->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "No se pudo crear order"]);
        exit;
    }
    $order_id = $order['id'];

    // preparar inserción items
    $sqlItems = "INSERT INTO order_items(order_id, id_platillo, nombre_platillo, precio, cantidad)
                 VALUES(:order_id, :id_platillo, :nombre_platillo, :precio, :cantidad)";
    $stmtItem = $conexion->prepare($sqlItems);

    foreach ($items as $i) {
        // validar estructura de cada item mínimo
        if (!isset($i['id']) || !isset($i['nombre']) || !isset($i['precio']) || !isset($i['cantidad'])) {
            if (isset($conexion) && $conexion instanceof PDO) $conexion->rollBack();
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Estructura de item inválida", "item" => $i]);
            exit;
        }

        $stmtItem->execute([
            ":order_id" => $order_id,
            // adaptamos nombres según lo que envía el frontend
            ":id_platillo" => $i['id'],
            ":nombre_platillo" => $i['nombre'],
            ":precio" => $i['precio'],
            ":cantidad" => $i['cantidad']
        ]);
    }

    if (isset($conexion) && $conexion instanceof PDO) {
        $conexion->commit();
    }

    echo json_encode(["success" => true, "order_id" => $order_id]);

} catch (Throwable $e) {
    if (isset($conexion) && $conexion instanceof PDO && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    http_response_code(500);
    // además de responder en JSON, registramos el error en el log
    error_log("pedidoController error: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Excepción en servidor: " . $e->getMessage()]);
    exit;
}
