<?php
// DelixSystem/app/views/mesas/view/menu.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../../../config/supabase.php';

$id_mesa = $_GET['id'] ?? null;

if (!$id_mesa) {
    die("No se especificó una mesa válida.");
}

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
    <link rel="stylesheet" href="../css/menu.css"> <!-- Ruta al CSS -->
</head>
<body>
    <h2>🍽️ <?= htmlspecialchars($mesa['mesa']) ?></h2>
    <p>Área: <b><?= htmlspecialchars($mesa['area']) ?></b></p>
    <br>

    <div class="menu-container">
        <div class="menu-box">
            <h3>APPETIZERS</h3>
            <div class="menu-item"><span>Dish Name</span><span>$12</span></div>
            <div class="menu-item"><span>Dish Name</span><span>$12</span></div>
            <div class="menu-item"><span>Dish Name</span><span>$12</span></div>
            <div class="menu-item"><span>Dish Name</span><span>$12</span></div>
        </div>

        <div class="menu-box">
            <h3>SPECIALS</h3>
            <div class="menu-item"><span>Today's Special</span><span>$18</span></div>
            <div class="menu-item"><span>Daily Deal</span><span>$16</span></div>
        </div>

        <div class="menu-box">
            <h3>OFFERS</h3>
            <div class="menu-item"><span>Weekly Special</span><span>$15</span></div>
            <div class="menu-item"><span>Weekly Special</span><span>$15</span></div>
        </div>

        <div class="menu-box">
            <h3>PROMOTIONS</h3>
            <div class="menu-item"><span>Dish Name</span><span>$10</span></div>
            <div class="menu-item"><span>Dish Name</span><span>$10</span></div>
        </div>

        <div class="menu-box order-box">
            <h3>ORDER</h3>
            <button class="order-btn" onclick="makeOrder()">Realizar Pedido</button>
        </div>
    </div>

    <script src="../js/menu.js"></script> <!-- Ruta al JS -->
</body>
</html>
