<?php
session_start();
header('Content-Type: application/json');

$baseDir = dirname(__DIR__, 4);
require_once $baseDir . '/middleware/universal_guard.php';
require_once $baseDir . '/config/supabase.php';

try {
    $usuario = universalGuard();
    $id_user = null;
    if ($usuario['tipo'] === 'propietario') {
        $id_user = $usuario['id'];
    } elseif ($usuario['tipo'] === 'empleado') {
        if (!empty($usuario['restaurant_id'])) {
            $id_user = $usuario['restaurant_id'];
        } elseif (!empty($usuario['user_id'])) {
            $id_user = $usuario['user_id'];
        } else {
            $stmt = $conexion->prepare("
                SELECT user_id 
                FROM employees 
                WHERE id = :id_empleado 
                LIMIT 1
            ");
            $stmt->execute([":id_empleado" => $usuario['id']]);
            $owner = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$owner || empty($owner['user_id'])) {
                throw new Exception("No se pudo determinar el propietario del empleado.");
            }
            $id_user = $owner['user_id'];
        }
    }
    if (!$id_user) {
        throw new Exception("No fue posible obtener el owner_id del usuario.");
    }
    $debug = [
        "tipo" => $usuario['tipo'],
        "empleado_id" => $usuario['id'],
        "restaurant_id" => $usuario['restaurant_id'] ?? 'null',
        "id_user_calculado" => $id_user
    ];
    $sqlPlatos = "
        SELECT *
        FROM dish
        WHERE id_user = :owner
        ORDER BY category, name_dish ASC
    ";
    $stmtDish = $conexion->prepare($sqlPlatos);
    $stmtDish->execute([":owner" => $id_user]);
    $platos = $stmtDish->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($platos as $i => $dish) {
        $stmtIng = $conexion->prepare("
            SELECT 
                di.ingredient_id AS id,
                di.quantity_used,
                di.unit,
                s.name,
                s.state,
                s.category AS storage_category
            FROM dish_ingredient di
            INNER JOIN storage s ON di.ingredient_id = s.id
            WHERE di.dish_id = :dish
              AND s.id_user = :owner
        ");
        $stmtIng->execute([
            ":dish" => $dish["id"],
            ":owner" => $id_user
        ]);
        $platos[$i]["ingredients"] = $stmtIng->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    $stmtCat = $conexion->prepare("
        SELECT DISTINCT category
        FROM dish
        WHERE id_user = :owner
          AND category IS NOT NULL
          AND category != ''
        ORDER BY category ASC
    ");
    $stmtCat->execute([":owner" => $id_user]);
    $categorias = $stmtCat->fetchAll(PDO::FETCH_COLUMN) ?: [];

    echo json_encode([
        "success" => true,
        "debug" => $debug,
        "platos" => $platos,
        "categorias" => $categorias
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
