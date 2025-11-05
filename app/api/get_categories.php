<?php
header("Content-Type: application/json; charset=utf-8");

include __DIR__ . '/../config/supabase.php';

$id_user = $_GET['u'] ?? null;
if(!$id_user){
    echo json_encode(["error"=>"missing user"]);
    exit;
}

// sacamos categorías desde dish (DISTINCT)
$stmt = $conexion->prepare("
    SELECT DISTINCT category AS name, category AS slug
    FROM dish
    WHERE id_user = :id_user AND state='Activo'
");
$stmt->bindParam(":id_user", $id_user);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data ?: []);
exit;
