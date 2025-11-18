<?php
//  DelixSystem/app/api/get_dishes.php

header("Content-Type: application/json; charset=utf-8");

include __DIR__ . '/../config/supabase.php';

$id_user = $_GET['u'] ?? null;
if(!$id_user){
    echo json_encode(["error"=>"missing user"]);
    exit;
}

$stmt = $conexion->prepare("
    SELECT id, name_dish, price, photo, description, category, state
    FROM dish
    WHERE id_user = :id_user
    ORDER BY state DESC, name_dish ASC
");
$stmt->bindParam(":id_user", $id_user);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Siempre devolver array
echo json_encode($data ?: []);
exit;
