<?php
session_start();
header('Content-Type: application/json');

// 📦 Cargar dependencias
$baseDir = dirname(__DIR__, 4);
require_once $baseDir . '/middleware/universal_guard.php';
require_once $baseDir . '/config/supabase.php';

try {
    // ✅ Obtener usuario activo (propietario o empleado)
    $usuario = universalGuard();

    // Determinar ID del propietario
    if ($usuario['tipo'] === 'propietario') {
        $id_user = $usuario['id'];

    } elseif ($usuario['tipo'] === 'empleado') {

        if (!empty($usuario['restaurant_id'])) {
            $id_user = $usuario['restaurant_id'];

        } else {
            // Último recurso: buscar al propietario del empleado
            $stmt = $conexion->prepare("SELECT user_id FROM employees WHERE id = :id_empleado LIMIT 1");
            $stmt->execute([':id_empleado' => $usuario['id']]);
            $owner = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$owner || empty($owner['user_id'])) {
                throw new Exception("No se pudo determinar el propietario del empleado.");
            }

            $id_user = $owner['user_id'];
        }
    } else {
        throw new Exception("Usuario no autenticado.");
    }

    // 🔹 Obtener platos
    $stmt = $conexion->prepare("
        SELECT * 
        FROM dish
        WHERE id_user = :id_user
        ORDER BY category, name_dish ASC
    ");
    $stmt->execute([':id_user' => $id_user]);
    $platos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔹 Obtener ingredientes
    $stmtIng = $conexion->prepare("
        SELECT id, name 
        FROM storage 
        WHERE id_user = :id_user
        ORDER BY name ASC
    ");
    $stmtIng->execute([':id_user' => $id_user]);
    $ingredientes = $stmtIng->fetchAll(PDO::FETCH_ASSOC);

    // 🔹 Agregar ingredientes por plato
    foreach ($platos as $i => $dish) {
        $stmt2 = $conexion->prepare("
            SELECT di.ingredient_id AS id, di.quantity_used, di.unit, s.name
            FROM dish_ingredient di
            INNER JOIN storage s ON di.ingredient_id = s.id
            WHERE di.dish_id = ?
        ");
        $stmt2->execute([$dish['id']]);
        $platos[$i]['ingredients'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Categorías únicas
    $categorias = array_unique(array_filter(array_column($platos, 'category')));

    echo json_encode([
        "success" => true,
        "platos" => $platos,
        "ingredientes" => $ingredientes,
        "categorias" => array_values($categorias)
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
