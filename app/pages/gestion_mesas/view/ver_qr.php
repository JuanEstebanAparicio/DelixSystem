<?php
include __DIR__ . '/../../../config/supabase.php';

$id_mesa = $_GET['id'] ?? null;

if (!$id_mesa) {
    die("No se especificó la mesa.");
}

// Consulta la mesa y el área
$stmt = $conexion->prepare("
    SELECT m.nombre AS mesa, a.nombre AS area 
    FROM mesas m 
    JOIN areas a ON a.id_area = m.id_area 
    WHERE m.id_mesa = ?
");
$stmt->execute([$id_mesa]);
$mesa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mesa) {
    die("Mesa no encontrada.");
}

// Texto que contendrá el QR
$ngrok_url = "https://uncatered-thomasina-arousingly.ngrok-free.dev/Proyecto_aula";
$contenido = "$ngrok_url/app/views/mesas/mesa.php?id={$id_mesa}";

// Genera la URL del QR
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($contenido) . "&size=250x250";


/* 🔽 INSERTA AQUÍ ESTE BLOQUE 🔽 */
$isAjax = isset($_GET['ajax']);
if ($isAjax) {
    echo "<h3>QR de " . htmlspecialchars($mesa['mesa']) . " (" . htmlspecialchars($mesa['area']) . ")</h3>";
    echo "<img src='$qr_url' alt='QR de la mesa' style='width:250px;height:250px;'>";
    echo "<p><a href='$qr_url' download='QR_". htmlspecialchars($mesa['mesa']) .".png'>Descargar QR</a></p>";
    exit;
}
/* 🔼 HASTA AQUÍ 🔼 */

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>QR de <?= htmlspecialchars($mesa['mesa']) ?></title>
</head>
<body>
    <h2>QR de <?= htmlspecialchars($mesa['mesa']) ?> (<?= htmlspecialchars($mesa['area']) ?>)</h2>
    <p>Escanea este código para identificar la mesa:</p>

    <!-- Mostrar la imagen del QR -->
    <img src="<?= $qr_url ?>" alt="QR de la mesa" style="width:250px;height:250px;">

    <br><br>
    <a href="<?= $qr_url ?>" download="QR_<?= htmlspecialchars($mesa['mesa']) ?>.png">Descargar QR</a>
</body>
</html>