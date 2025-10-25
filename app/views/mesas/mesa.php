<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../../config/supabase.php';

$id_mesa = $_GET['id'] ?? null;

if (!$id_mesa) {
    die("No se especificó una mesa válida.");
}

// Consultar mesa y su área
$stmt = $conexion->prepare("
    SELECT m.id_mesa, m.nombre AS mesa, a.nombre AS area 
    FROM mesas m 
    JOIN areas a ON a.id_area = m.id_area 
    WHERE m.id_mesa = ?
");
$stmt->execute([$id_mesa]);
$mesa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mesa) {
    die("Mesa no encontrada.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($mesa['mesa']) ?> - <?= htmlspecialchars($mesa['area']) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f7f7f7;
            color: #333;
            text-align: center;
            padding: 30px;
        }
        .contenedor {
            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            display: inline-block;
            padding: 30px 40px;
        }
        h2 {
            color: #2c3e50;
        }
        .btn {
            display: inline-block;
            margin: 10px;
            padding: 10px 25px;
            border-radius: 8px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <h2>🍽️ <?= htmlspecialchars($mesa['mesa']) ?></h2>
        <p>Área: <b><?= htmlspecialchars($mesa['area']) ?></b></p>
        <p>Bienvenido. Desde aquí pronto podrás:</p>
        <a href="#" class="btn">Ver Menú</a>
        <a href="#" class="btn">Ver Mis Órdenes</a>
    </div>
</body>
</html>