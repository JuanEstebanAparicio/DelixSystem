<?php
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
