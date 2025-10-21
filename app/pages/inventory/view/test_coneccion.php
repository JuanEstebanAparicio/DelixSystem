<?php
$baseDir = dirname(__DIR__, 3);
require_once($baseDir . '/config/supabase.php');

$query = $conexion->query("SELECT NOW()");
$result = $query->fetch(PDO::FETCH_ASSOC);

echo "✅ Conectado correctamente. Fecha actual en Supabase: " . $result["now"];
?>
