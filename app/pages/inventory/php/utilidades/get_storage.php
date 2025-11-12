<?php
// DelixSystem/app/pages/inventory/php/get_storage.php

session_start();
header('Content-Type: application/json');

// 📦 Cargar dependencias
$baseDir = dirname(__DIR__, 4);
require_once $baseDir . '/middleware/universal_guard.php';
require_once $baseDir . '/config/supabase.php';

try {
    // ✅ Obtener usuario activo (propietario o empleado)
    $usuario = universalGuard();

    // Determinar el ID base para la consulta de inventario
    if ($usuario['tipo'] === 'propietario') {
        $id_user = $usuario['id'];
    } elseif ($usuario['tipo'] === 'empleado') {
        // 🔹 Obtener el ID del propietario asociado
        if (!empty($usuario['restaurant_id'])) {
            $id_user = $usuario['restaurant_id'];
        } elseif (!empty($usuario['user_id'])) {
            $id_user = $usuario['user_id'];
        } else {
            // Último recurso: buscar propietario desde la tabla employees
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

    // 🔹 OBTENER CATEGORÍAS
    $sqlCategorias = "
        SELECT DISTINCT category 
        FROM storage 
        WHERE id_user = :id_user 
          AND category IS NOT NULL 
          AND category != '' 
        ORDER BY category ASC
    ";
    $stmtCat = $conexion->prepare($sqlCategorias);
    $stmtCat->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmtCat->execute();
    $categorias = $stmtCat->fetchAll(PDO::FETCH_COLUMN) ?: [];

    // 🔹 OBTENER INVENTARIO
    $sqlInsumos = "
        SELECT * 
        FROM storage 
        WHERE id_user = :id_user 
        ORDER BY category, name ASC
    ";
    $stmtInv = $conexion->prepare($sqlInsumos);
    $stmtInv->execute([':id_user' => $id_user]);
    $insumos = $stmtInv->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "categorias" => $categorias,
        "insumos" => $insumos
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
