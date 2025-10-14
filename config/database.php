<?php
// PROJECTDELIX/app/config/database.php
require_once __DIR__ . '/constants.php';

function supabaseRequest(string $endpoint, string $method = 'GET', $data = null, bool $useServiceKey = true): array {
    $base = rtrim(SUPABASE_URL, '/');
    $url  = $base . $endpoint;

    $apiKey = $useServiceKey ? SUPABASE_SERVICE_KEY : SUPABASE_ANON_KEY;

    $headers = [
        "apikey: {$apiKey}",
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER    => $headers,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT       => 15,
    ]);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $raw      = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    $decoded = null;
    $jsonErr = null;
    if ($raw !== false && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonErr = json_last_error_msg();
        }
    }

    return [
        'status' => $status,
        'error'  => $curlErr ?: $jsonErr,
        'data'   => $decoded,
        'raw'    => $raw
    ];
}
