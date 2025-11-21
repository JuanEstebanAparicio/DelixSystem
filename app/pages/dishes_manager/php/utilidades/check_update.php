<?php
session_start();
header('Content-Type: application/json');

$baseDir = dirname(__DIR__, 4);
require_once $baseDir . '/middleware/universal_guard.php';
require_once $baseDir . '/config/supabase.php';

try {
    $usuario = universalGuard();
    if ($usuario['tipo'] === 'propietario') {
        $id_user = $usuario['id'];
    } elseif ($usuario['tipo'] === 'empleado') {

        if (!empty($usuario['restaurant_id'])) {
            $id_user = $usuario['restaurant_id'];
        } else {
            $stmt = $conexion->prepare("SELECT user_id FROM employees WHERE id = :id_empleado LIMIT 1");
            $stmt->execute([':id_empleado' => $usuario['id']]);
            $owner = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$owner || empty($owner['user_id'])) {
                throw new Exception("No se pudo determinar el propietario.");
            }

            $id_user = $owner['user_id'];
        }
    } else {
        throw new Exception("Usuario no autenticado.");
    }
    $stmt = $conexion->prepare("
        SELECT MAX(updated_at) AS last_update
        FROM dish
        WHERE id_user = :id_user
    ");

    $stmt->execute([':id_user' => $id_user]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "last_update" => $result['last_update'] ?? null
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
