<?php
/**--
 *  Este módulo gestiona la comunicación entre la aplicación y la base de datos
 *  de Supabase mediante peticiones HTTP (REST API). 
 * 
 *  Proporciona funciones reutilizables para:
 *   - Consultar, insertar, actualizar o eliminar registros en tablas de Supabase.
 *   - Manejar autenticación de usuarios a través del endpoint de Supabase Auth.
 * 
 * Funciones principales:
 *  • supabase($table, $method, $data, $query, $useService)
 *      Permite interactuar con cualquier tabla del proyecto Supabase usando 
 *      métodos HTTP (GET, POST, PATCH, DELETE).
 * 
 *  • supabaseAuth($endpoint, $method, $data)
 *      Gestiona la autenticación (registro, login, recuperación de contraseña)
 *      con el servicio de autenticación de Supabase.
 * 
 * Dependencias:
 *  - constants.php (debe contener SUPABASE_URL, SUPABASE_ANON_KEY y SUPABASE_SERVICE_KEY)
 *  - Extensión cURL habilitada en PHP.
 */
require_once __DIR__ . '/constants.php';

function supabase(string $table, string $method = 'GET', array $data = null, string $query = '', bool $useService = true): array {
    $url = rtrim(SUPABASE_URL, '/') . '/rest/v1/' . ltrim($table, '/') . $query;
    $key = $useService ? SUPABASE_SERVICE_KEY : SUPABASE_ANON_KEY;

    $headers = [
        "apikey: $key",
        "Authorization: Bearer $key",
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 15,
    ]);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $res = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'status' => $status,
        'error'  => $error ?: null,
        'data'   => json_decode($res, true)
    ];
}

function supabaseAuth(string $endpoint, string $method = 'POST', array $data = null): array {
    $url = rtrim(SUPABASE_URL, '/') . '/auth/v1/' . ltrim($endpoint, '/');

    $headers = [
        'apikey: ' . SUPABASE_ANON_KEY,
        'Authorization: Bearer ' . SUPABASE_ANON_KEY,
        'Content-Type: application/json'
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 15,
    ]);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $res = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'status' => $status,
        'error'  => $error ?: null,
        'data'   => json_decode($res, true)
    ];
}