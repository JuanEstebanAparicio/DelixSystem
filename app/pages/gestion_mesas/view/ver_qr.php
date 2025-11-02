<?php
// DelixSystem/app/pages/gestion_mesas/view/ver_qr.php

include __DIR__ . '/../../../config/supabase.php';

$id_mesa = isset($_GET['id']) ? (int) $_GET['id'] : null;
if (!$id_mesa) {
    die("No se especificó la mesa válida.");
}

// Consulta la mesa y el área
$stmt = $conexion->prepare("
    SELECT m.nombre AS mesa, a.nombre AS area, a.id_usuario AS owner_id
    FROM mesas m
    JOIN areas a ON a.id_area = m.id_area
    WHERE m.id_mesa = :id_mesa
    LIMIT 1
");
$stmt->bindValue(':id_mesa', $id_mesa, PDO::PARAM_INT);
$stmt->execute();
$mesa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mesa) {
    die("Mesa no encontrada.");
}

// ID del propietario real (owner) desde la tabla areas
$id_user_owner = $mesa['owner_id'] ?? null;
if (!$id_user_owner) {
    die("No se pudo determinar el propietario de esta mesa.");
}

// URL base del sistema (ejemplo con ngrok)
$ngrok_url = "https://uncatered-thomasina-arousingly.ngrok-free.dev/DelixSystem";

// Generar contenido que va dentro del QR
$contenido = "$ngrok_url/app/views/mesas/view/menu.php?id={$id_mesa}&u={$id_user_owner}";

// URL de la API para generar el QR
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($contenido) . "&size=250x250";

/* 🔽 BLOQUE AJAX 🔽 */
$isAjax = isset($_GET['ajax']);
if ($isAjax) {
    echo "<h3>QR de " . htmlspecialchars($mesa['mesa']) . " (" . htmlspecialchars($mesa['area']) . ")</h3>";
    echo "<img src='$qr_url' alt='QR de la mesa' style='width:250px;height:250px;'>";
    echo "<p><a href='$qr_url' download='QR_" . htmlspecialchars($mesa['mesa']) . ".png'>Descargar QR</a></p>";
    exit;
}
/* 🔼 FIN BLOQUE AJAX 🔼 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
