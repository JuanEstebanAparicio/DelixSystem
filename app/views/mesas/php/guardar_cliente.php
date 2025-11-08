<?php
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

$nombre = $_POST['nombre'] ?? '';

if($nombre != ''){

    // Guardar datos básicos en sesión para recordar el alias del pedido
    $_SESSION['cliente'] = [
        'nombre' => $nombre
    ];

    // Insertar en tabla clientes de Supabase
    $insert = supabase('clientes', 'POST', [
        'nombre' => $nombre
    ]);

    if($insert['status'] == 201){ // insert correcto supabase devuelve 201
        echo "ok";
    }else{
        echo "error supabase: " . json_encode($insert);
    }

} else {
    echo "error: nombre vacio";
}
