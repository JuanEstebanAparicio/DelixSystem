<?php
// DelixSystem/app/views/mesas/php/sincronizar_cliente.php
session_start();
header('Content-Type: application/json');

// Obtener y decodificar el cuerpo de la petición
$input = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!$input || !isset($input['cliente']) || empty($input['cliente']['nombre'])) {
    echo json_encode([
        'ok' => false,
        'msg' => 'Datos de cliente incompletos o inválidos'
    ]);
    exit;
}

$cliente = $input['cliente'];

// Si ya hay cliente en sesión y es diferente, reiniciamos la sesión
if (isset($_SESSION['cliente']) && $_SESSION['cliente']['nombre'] !== $cliente['nombre']) {
    session_unset();
    session_destroy();
    session_start();
}

// Guardar cliente actual en sesión
$_SESSION['cliente'] = [
    'nombre'   => $cliente['nombre'],
    'mesa'     => $cliente['mesa']     ?? null,
    'id_mesa'  => $cliente['id_mesa']  ?? null,
    'id_area'  => $cliente['id_area']  ?? null,
    'id_user'  => $cliente['id_user']  ?? null,
];

// Confirmar sincronización
echo json_encode([
    'ok' => true,
    'msg' => 'Sesión cliente sincronizada correctamente',
    'cliente' => $_SESSION['cliente']
]);
exit;
