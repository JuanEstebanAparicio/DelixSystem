<?php
require_once __DIR__ . '/../../../config/supabase.php';

header('Content-Type: application/json');

try {
    $query = $conexion->query("SELECT * FROM dish ORDER BY category, name_dish ASC");
    $platos = $query->fetchAll(PDO::FETCH_ASSOC);

    $queryIng = $conexion->query("SELECT id, name FROM storage ORDER BY name ASC");
    $ingredientes = $queryIng->fetchAll(PDO::FETCH_ASSOC);

    foreach ($platos as $i => $dish) {
        $stmt = $conexion->prepare("SELECT ingredient_id FROM dish_ingredient WHERE dish_id = ?");
        $stmt->execute([$dish['id']]);
        $platos[$i]['ingredients'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    echo json_encode([
        "success" => true,
        "platos" => $platos,
        "ingredientes" => $ingredientes
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
