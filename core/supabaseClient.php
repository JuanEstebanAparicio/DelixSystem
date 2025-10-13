<?php
// core/supabaseClient.php
require_once __DIR__ . '/../config/constants.php';

function supabaseRequest(string $endpoint, array $data, string $method = 'POST'): array {
    $url = SUPABASE_URL . $endpoint;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'apikey: ' . SUPABASE_SERVICE_KEY,
            'Authorization: Bearer ' . SUPABASE_SERVICE_KEY
        ],
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_SSL_VERIFYPEER => false // solo para local dev
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error) {
        return ['error' => $error, 'status' => 0];
    }

    return [
        'status' => $status,
        'data' => json_decode($response, true)
    ];
}
