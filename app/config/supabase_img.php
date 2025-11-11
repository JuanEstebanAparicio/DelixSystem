<?php
/**
 * 🔹 Supabase Storage API Helper
 * Maneja subida, obtención y eliminación de imágenes en Supabase Storage.
 * Usa las claves definidas en constants.php.
 */

require_once __DIR__ . '/constants.php';

/**
 * 🔸 Subir una imagen al bucket de Supabase Storage
 * @param array  $file   Archivo del formulario ($_FILES['photo'])
 * @param string $bucket Nombre del bucket (ej: 'storage_img')
 * @param string $path   Ruta interna (ej: 'category/product')
 * @return string|null   URL pública de la imagen
 */
function supabaseUploadImage(array $file, string $bucket, string $path): ?string
{
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // Nombre único para evitar conflictos
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('img_') . '.' . strtolower($ext);
    $objectPath = "$path/$fileName";

    // Construir URL del endpoint
    $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/$bucket/$objectPath";

    // Leer archivo
    $fileData = file_get_contents($file['tmp_name']);

    // Configurar cURL
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $fileData,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer " . SUPABASE_SERVICE_KEY,
            "apikey: " . SUPABASE_SERVICE_KEY,
            "Content-Type: application/octet-stream",
        ],
    ]);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($statusCode >= 200 && $statusCode < 300) {
        // URL pública
        return rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/$bucket/$objectPath";
    } else {
        error_log("❌ Error al subir imagen ($statusCode): $response | $error");
        return null;
    }
}

/**
 * 🔸 Eliminar una imagen del bucket de Supabase Storage
 * @param string $bucket  Nombre del bucket
 * @param string $path    Ruta completa dentro del bucket (ej: 'inventario/1/categoria/img_xxx.png')
 * @return bool           True si se elimina correctamente
 */
function supabaseDeleteImage(string $bucket, string $path): bool
{
    $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/$bucket/$path";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer " . SUPABASE_SERVICE_KEY,
            "apikey: " . SUPABASE_SERVICE_KEY,
        ],
    ]);

    curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $statusCode >= 200 && $statusCode < 300;
}
?>
