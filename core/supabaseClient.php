<?php
class SupabaseClient {
    private $url;
    private $key;
    private $authUrl;
    private $restUrl;

    public function __construct(array $config) {
        $this->url = $config['supabase_url'];
        $this->key = $config['supabase_key'];
        $this->authUrl = $config['supabase_auth_url'];
        $this->restUrl = $config['supabase_rest_url'];
    }

    private function request($method, $url, $data = null) {
        $ch = curl_init($url);
        $headers = [
            "apikey: {$this->key}",
            "Authorization: Bearer {$this->key}",
            "Content-Type: application/json"
        ];

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // Ejemplo: obtener datos de una tabla
    public function select($table) {
        return $this->request("GET", "{$this->restUrl}/{$table}");
    }

    // Ejemplo: insertar datos
    public function insert($table, $data) {
        return $this->request("POST", "{$this->restUrl}/{$table}", $data);
    }
}
