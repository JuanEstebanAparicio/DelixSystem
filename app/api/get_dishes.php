<?php
//  DelixSystem/app/api/get_dishes.php

header("Content-Type: application/json; charset=utf-8"); // <-- obligatorio

include __DIR__ . '/../config/supabase.php';

$id_user = $_GET['u'] ?? null;
if(!$id_user){
    echo json_encode(["error"=>"missing user"]);
    exit;
}

$stmt = $conexion->prepare("SELECT id, name_dish, price, photo, description, category
                            FROM dish
                            WHERE id_user = :id_user AND state='Activo'");
$stmt->bindParam(":id_user", $id_user);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// siempre devolver array, aunque esté vacío
echo json_encode($data ?: []);
exit;
