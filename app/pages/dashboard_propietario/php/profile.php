<?php
// ✅ Devuelve siempre JSON
header('Content-Type: application/json; charset=utf-8');

// 📂 Rutas de dependencias
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../middleware/session_guard.php';

// 🧩 Asegura sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🧩 Protege página
protectPage();

// 🧩 ID del usuario actual
$userId = $_SESSION['usuario']['id'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// 📘 Función para registrar logs
function registrarLog($mensaje) {
    $logFile = __DIR__ . '/profile_log.txt';
    $fecha = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$fecha] $mensaje" . PHP_EOL, FILE_APPEND);
}

// ----------------------------
// 🔹 PETICIÓN GET → Obtener perfil
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = '?id=eq.' . urlencode($userId);
    $response = supabase('usuarios', 'GET', null, $query);

    if ($response['status'] === 200 && !empty($response['data'])) {
        $user = $response['data'][0];
        echo json_encode([
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'email' => $user['email'] ?? '',
            'restaurant_name' => $user['restaurant_name'] ?? ''
        ]);
        registrarLog("GET perfil usuario ID $userId OK");
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        registrarLog("GET perfil usuario ID $userId FALLÓ");
    }
    exit;
}

// ----------------------------
// 🔹 PETICIÓN POST → Actualizar perfil
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $data = [
        'first_name'      => trim($input['first_name'] ?? ''),
        'last_name'       => trim($input['last_name'] ?? ''),
        'email'           => trim($input['email'] ?? ''),
        'restaurant_name' => trim($input['restaurant_name'] ?? '')
    ];

    $query = '?id=eq.' . urlencode($userId);
    $response = supabase('usuarios', 'PATCH', $data, $query);

    if ($response['status'] >= 200 && $response['status'] < 300) {
        // 🔹 Actualiza datos en sesión
        $_SESSION['usuario']['nombre'] = $data['first_name'];
        $_SESSION['usuario']['restaurante'] = $data['restaurant_name'];

        registrarLog("PATCH perfil usuario ID $userId actualizado correctamente");

        echo json_encode([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'usuario_actualizado' => $_SESSION['usuario'] ?? null
        ]);
    } else {
        registrarLog("PATCH perfil usuario ID $userId falló: " . json_encode($response));
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $response['error'] ?? 'Error al actualizar perfil'
        ]);
    }
    exit;
}

// ----------------------------
// 🔹 Método HTTP no permitido
// ----------------------------
http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
registrarLog("Método HTTP no permitido: " . $_SERVER['REQUEST_METHOD']);
exit;
