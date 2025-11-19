<?php
session_start();
header('Content-Type: application/json');

// 📦 Dependencias
$baseDir = dirname(__DIR__, 4);
require_once $baseDir . '/middleware/universal_guard.php';
require_once $baseDir . '/config/supabase.php';

try {

    // 🧑‍🍳 1. Obtener usuario activo
    $usuario = universalGuard();

    // 📌 2. Determinar el ID del propietario real
    if ($usuario['tipo'] === 'propietario') {

        // El propietario SIEMPRE usa su propio ID
        $id_user = $usuario['id'];

    } elseif ($usuario['tipo'] === 'empleado') {

        // Los empleados NO tienen restaurant_id, sino user_id del propietario
        $stmt = $conexion->prepare("
            SELECT user_id
            FROM employees
            WHERE id = :emp_id
            LIMIT 1
        ");
        $stmt->execute([
            ':emp_id' => $usuario['id']
        ]);

        $owner = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$owner || empty($owner['user_id'])) {
            throw new Exception("No se pudo obtener el propietario del empleado.");
        }

        $id_user = $owner['user_id'];

    } else {
        throw new Exception("Usuario no autenticado.");
    }


    // 🍽️ 3. Obtener PLATOS del propietario o empleado autorizado
    $stmt = $conexion->prepare("
        SELECT *
        FROM dish
        WHERE id_user = :id_user
        ORDER BY category, name_dish ASC
    ");
    $stmt->execute([':id_user' => $id_user]);
    $platos = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // 🧂 4. Obtener INGREDIENTES (insumos)
    $stmtIng = $conexion->prepare("
        SELECT id, name
        FROM storage
        WHERE id_user = :id_user
        ORDER BY name ASC
    ");
    $stmtIng->execute([':id_user' => $id_user]);
    $ingredientes = $stmtIng->fetchAll(PDO::FETCH_ASSOC);


    // 🧩 5. Unir los ingredientes por plato
    foreach ($platos as $i => $dish) {

        $stmt2 = $conexion->prepare("
            SELECT 
                di.ingredient_id AS id,
                di.quantity_used,
                di.unit,
                s.name
            FROM dish_ingredient di
            INNER JOIN storage s ON di.ingredient_id = s.id
            WHERE di.dish_id = ?
        ");
        $stmt2->execute([$dish['id']]);

        $platos[$i]['ingredients'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }


    // 🏷️ 6. Categorías únicas
    $categorias = array_unique(
        array_filter(
            array_column($platos, 'category')
        )
    );

    // 📤 7. Respuesta final
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
