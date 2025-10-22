<?php
// Detectar entorno (localhost o producción)
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
    . "://"
    . $_SERVER['HTTP_HOST']
    . "/ProjectDelix/";

// Ruta absoluta dentro del sistema de archivos
$base_path = __DIR__ . '/../../';

// Definir constantes globales
define('BASE_URL', $base_url);
define('BASE_PATH', $base_path);
?>
