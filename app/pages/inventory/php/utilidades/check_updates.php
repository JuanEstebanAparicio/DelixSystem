<?php
// DelixSystem/app/pages/inventory/php/utilidades/check_updates.php

session_start();
header('Content-Type: application/json');

// 📦 Cargar dependencias
$baseDir = dirname(__DIR__, 5);
require_once $baseDir . '/middleware/universal_guard.php';
require_once $baseDir . '/config/supabase.php';

try {
    // ✅ Obtener usuario logueado (propietario o empleado)
    $usuario = universalGuard();

    // 🔍 Determinar el propietario real según el usuario
    if ($usuario['tipo'] === 'propietario') {
        $id_user = $usuario['id'];
    } elseif ($usuario['tipo'] === 'empleado') {
        if (!empty($usuario['restaurant_id'])) {
            $id_user = $usuario['restaurant_id'];
        } elseif (!empty($usuario['user_id'])) {
            $id_user = $usuario['user_id'];
        } else {
            // Recuperar propietario desde tabla employees
            $stmt = $conexion->prepare("
                SELECT user_id 
                FROM employees 
                WHERE id = :id_empleado 
                LIMIT 1
            ");
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

    // ================================================
    // 🔄 OBTENER ÚLTIMA FECHA DE ACTUALIZACIÓN
    // ================================================
    $stmt = $conexion->prepare("
        SELECT MAX(updated_at) AS last_update
        FROM storage
        WHERE id_user = :id_user
    ");

    $stmt->execute([':id_user' => $id_user]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $last_update = $result['last_update'] ?? null;

    echo json_encode([
        "success" => true,
        "last_update" => $last_update
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
