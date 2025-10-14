<?php
// PROJECTDELIX/src/auth/register.php
// Endpoint completo: recibe POST desde el modal, crea usuario en Supabase Auth, inserta en tabla admins,
// registra logs y envía correo de confirmación local si Supabase no lo envía.
// Requisitos (ajusta PROJECTDELIX/app/config/constants.php o PROJECTDELIX/config/constants.php):
//   SUPABASE_URL, SUPABASE_ANON_KEY, SUPABASE_SERVICE_KEY
// Opcional (para envío local de emails):
//   SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_FROM_EMAIL, SMTP_FROM_NAME, SITE_URL
declare(strict_types=1);

/* -------------------- CARGA DE CONFIGURACIÓN -------------------- */
$constantsPath = __DIR__ . '/../../config/constants.php';
$altConstants = __DIR__ . '/../../app/config/constants.php';
if (!file_exists($constantsPath) && file_exists($altConstants)) $constantsPath = $altConstants;
if (!file_exists($constantsPath)) {
    http_response_code(500);
    echo "Server error: constants.php not found.";
    error_log("ProjectDelix: constants.php not found in expected paths ({$constantsPath}, {$altConstants})");
    exit;
}
require_once $constantsPath;

/* verificar constantes mínimas */
if (!defined('SUPABASE_URL') || !defined('SUPABASE_ANON_KEY') || !defined('SUPABASE_SERVICE_KEY')) {
    http_response_code(500);
    echo "Server error: missing SUPABASE constants.";
    error_log("ProjectDelix: SUPABASE_URL/ANON/SERVICE constants are missing in constants.php");
    exit;
}

/* -------------------- UTILIDADES -------------------- */
function dbg(string $msg): void {
    $p = __DIR__ . '/../../logs/register_debug.log';
    @mkdir(dirname($p), 0755, true);
    @file_put_contents($p, date('c') . ' ' . $msg . PHP_EOL, FILE_APPEND);
}
function respondAndExit(string $title, string $html, string $icon = 'info', ?string $redirect = '/'): void {
    echo "<!doctype html><html lang='es'><head><meta charset='utf-8'><title>$title</title><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>";
    $js = "document.addEventListener('DOMContentLoaded',()=>{Swal.fire({title:".json_encode($title).",html:".json_encode($html).",icon:".json_encode($icon).",confirmButtonText:'Aceptar'}).then(()=>{window.location=".json_encode($redirect).";});});";
    echo "<script>{$js}</script></body></html>";
    exit;
}

/* -------------------- SOLO POST -------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondAndExit('Acceso no permitido','Debes usar el formulario de registro.','warning');
}

/* -------------------- LEER Y SANITIZAR INPUT -------------------- */
$first_name = trim((string)($_POST['first_name'] ?? ''));
$last_name = trim((string)($_POST['last_name'] ?? ''));
$email = strtolower(trim((string)($_POST['email'] ?? '')));
$restaurant_name = trim((string)($_POST['restaurant_name'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$confirm_password = (string)($_POST['confirm_password'] ?? '');
$accept_terms = isset($_POST['accept_terms']) && ($_POST['accept_terms'] === 'on' || $_POST['accept_terms'] === '1' || $_POST['accept_terms'] === 'true');

/* -------------------- VALIDACIONES -------------------- */
if ($first_name === '' || $last_name === '' || $restaurant_name === '') {
    dbg("VALIDATION_EMPTY_FIELDS first_name='$first_name' last_name='$last_name' restaurant_name='$restaurant_name'");
    respondAndExit('Error','Todos los campos obligatorios deben completarse.','error');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    dbg("VALIDATION_EMAIL_INVALID: $email");
    respondAndExit('Error','Correo electrónico no válido.','error');
}
if (!$accept_terms) respondAndExit('Error','Debes aceptar términos y política.','error');
if ($password === '' || $confirm_password === '') {
    dbg("VALIDATION_PASSWORD_EMPTY");
    respondAndExit('Error','Debes ingresar y confirmar la contraseña.','error');
}
if ($password !== $confirm_password) {
    dbg("VALIDATION_PASSWORD_MISMATCH");
    respondAndExit('Error','Las contraseñas no coinciden.','error');
}
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/', $password)) {
    dbg("VALIDATION_PASSWORD_POLICY_FAIL");
    respondAndExit('Error','La contraseña debe tener mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.','error');
}

/* -------------------- PREPARAR PROFILE (NO NULLS) -------------------- */
$profile = [
    'first_name' => (string)$first_name,
    'last_name' => (string)$last_name,
    'email' => (string)$email,
    'restaurant_name' => (string)$restaurant_name,
    'verified' => false,
    'created_at' => date('c')
];

/* -------------------- 1) CREAR USUARIO EN SUPABASE AUTH (SIGNUP) -------------------- */
$signupUrl = rtrim(SUPABASE_URL, '/') . '/auth/v1/signup';
$signupBody = json_encode(['email' => $profile['email'], 'password' => $password], JSON_UNESCAPED_UNICODE);
$ch = curl_init($signupUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . SUPABASE_ANON_KEY,
    'Authorization: Bearer ' . SUPABASE_ANON_KEY
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $signupBody);
$authRaw = curl_exec($ch);
$authStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$authCurlErr = curl_error($ch);
curl_close($ch);
dbg("AUTH_RESPONSE STATUS:$authStatus RAW:$authRaw ERR:$authCurlErr");
$authResp = $authRaw ? json_decode($authRaw, true) : null;

/* Manejo de errores de signup que indiquen duplicado */
if (!in_array($authStatus, [200,201])) {
    $msg = 'No se pudo crear la cuenta en el servicio de autenticación.';
    if (is_array($authResp)) {
        $m = $authResp['msg'] ?? $authResp['message'] ?? $authResp['error'] ?? '';
        if (stripos($m, 'duplicate') !== false || stripos($m, 'already exists') !== false) {
            dbg("AUTH_DUPLICATE: $authRaw");
            respondAndExit('Correo duplicado','El correo ya está registrado.','warning');
        }
        $msg = $m ?: $msg;
    } elseif ($authCurlErr) {
        $msg = 'Error de conexión al servicio de autenticación: ' . $authCurlErr;
    }
    dbg("AUTH_ERROR: $msg RAW:$authRaw");
    respondAndExit('Error en el registro', $msg, 'error');
}

/* -------------------- Verificar confirmation_sent_at o fallback -------------------- */
$confirmationSent = false;
if (is_array($authResp)) {
    if (!empty($authResp['confirmation_sent_at']) || !empty($authResp['user']['confirmation_sent_at'])) {
        $confirmationSent = true;
    }
}

/* -------------------- 2) Preparar payload y upsert en admins (SERVICE KEY) -------------------- */
/* añadir id devuelto por auth si existe */
$userId = $authResp['user']['id'] ?? null;
if ($userId) $profile['id'] = (string)$userId;

/* Definir columnas esperadas (ajusta si tu tabla tiene más columnas) */
$expectedColumns = ['id','first_name','last_name','email','restaurant_name','verified','created_at'];

/* Construir safe payload rellenando valores por defecto según tipo */
$safe = [];
foreach ($expectedColumns as $col) {
    $val = $profile[$col] ?? null;
    switch ($col) {
        case 'id':
            if ($val !== null) $safe[$col] = (string)$val;
            break;
        case 'verified':
            $safe[$col] = isset($val) ? (bool)$val : false;
            break;
        case 'created_at':
            $safe[$col] = $val ? (string)$val : date('c');
            break;
        default:
            $safe[$col] = $val !== null ? (string)$val : '';
            break;
    }
}

/* eliminar id vacío para que DB genere default uuid si aplica */
if (empty($safe['id'])) unset($safe['id']);
$payload = [$safe];
$jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);
dbg("SENT_JSON: $jsonBody");

/* Enviar insert/upsert a Supabase REST usando SERVICE KEY */
$directUrl = rtrim(SUPABASE_URL, '/') . '/rest/v1/admins';
$ch2 = curl_init($directUrl);
$headers = [
    'apikey: ' . SUPABASE_SERVICE_KEY,
    'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
    'Content-Type: application/json',
    'Prefer: return=representation',
    'Content-Length: ' . strlen($jsonBody)
];
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, $jsonBody);
$raw2 = curl_exec($ch2);
$status2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$err2 = curl_error($ch2);
curl_close($ch2);
dbg("DIRECT_INSERT RAW: $raw2 STATUS: $status2 ERR: $err2");

/* -------------------- 3) Manejo del resultado de inserción -------------------- */
if (in_array($status2, [200,201])) {
    /* registrar log en la tabla logs (intentar) */
    $logPayload = [[
        'user_id' => $userId ?? null,
        'user_role' => 'admin',
        'action' => 'register',
        'module' => 'auth',
        'meta' => json_encode(['email' => $profile['email'], 'first_name' => $profile['first_name']]),
        'created_at' => date('c')
    ]];
    $logBody = json_encode($logPayload, JSON_UNESCAPED_UNICODE);
    $ch3 = curl_init(rtrim(SUPABASE_URL, '/') . '/rest/v1/logs');
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch3, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_SERVICE_KEY,
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch3, CURLOPT_POST, true);
    curl_setopt($ch3, CURLOPT_POSTFIELDS, $logBody);
    curl_exec($ch3);
    curl_close($ch3);

    /* Si Supabase NO envió el email de confirmación, intentar enviar localmente (si SMTP configurado) */
    if (!$confirmationSent) {
        dbg("WARNING: confirmation email NOT sent for {$email}. Attempting local send if SMTP configured.");
        $sentLocal = false;
        if (defined('SMTP_HOST') && defined('SMTP_USER') && defined('SMTP_PASS') && defined('SMTP_FROM_EMAIL')) {
            // intentar enviar con PHPMailer si está disponible
            try {
                if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
                    require_once __DIR__ . '/../../vendor/autoload.php';
                    // PHPMailer namespaced autoload
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = SMTP_USER;
                    $mail->Password = SMTP_PASS;
                    $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
                    $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;
                    $mail->setFrom(SMTP_FROM_EMAIL, defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'DELIX');
                    $mail->addAddress($profile['email'], $profile['first_name'] . ' ' . $profile['last_name']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Confirma tu cuenta en DELIX';
                    $token = bin2hex(random_bytes(16));
                    $link = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/verify.php?email=' . urlencode($profile['email']) . '&token=' . $token;
                    $mail->Body = "Hola " . htmlspecialchars($profile['first_name']) . ",<br><br>Gracias por registrarte en DELIX. Haz clic en el siguiente enlace para confirmar tu cuenta: <a href=\"" . htmlspecialchars($link) . "\">Confirmar cuenta</a>.<br><br>Si no solicitaste esto, ignora este correo.";
                    $mail->send();
                    $sentLocal = true;
                    dbg("Local email sent to {$profile['email']} via PHPMailer.");
                } else {
                    dbg("PHPMailer not available (vendor/autoload.php missing). Local email not sent.");
                }
            } catch (\Throwable $e) {
                dbg("PHPMailer error: " . $e->getMessage());
                $sentLocal = false;
            }
        } else {
            dbg("SMTP config missing in constants.php; cannot send local confirmation email.");
        }

        if ($sentLocal) {
            respondAndExit('Registro exitoso','Cuenta creada. Hemos enviado el correo de confirmación desde el servidor. Revisa bandeja y SPAM.','success');
        } else {
            respondAndExit('Registro creado','Cuenta creada correctamente. No pudimos enviar el correo de confirmación automáticamente. Revisa tu bandeja o contacta soporte.','warning');
        }
    }

    /* Si confirmation_sent fue true */
    respondAndExit('Registro exitoso','Cuenta creada. Se ha enviado un correo de verificación. Revisa bandeja y SPAM.','success');
}

/* -------------------- 4) Manejo de errores de inserción -------------------- */
$rawDecoded = $raw2 ? json_decode($raw2, true) : null;
$errMsg = 'No se pudo completar el registro.';
if ($rawDecoded && (isset($rawDecoded['message']) || isset($rawDecoded['error']))) {
    $errMsg = $rawDecoded['message'] ?? $rawDecoded['error'];
}
if ($err2) $errMsg .= ' Curl error: ' . $err2;
dbg("INSERT_FAILED MSG: $errMsg RAW: $raw2");

/* Detección común de columnas nulas -> sugerencia temporal ejecutable en SQL editor (service role) */
if (stripos($raw2 ?? '', 'null value in column') !== false) {
    dbg("ERROR_NULL_COLUMN detected. RAW2: $raw2");
    respondAndExit('Error en la base de datos','Fallo al insertar datos en la base de datos por valores nulos. Revisa register_debug.log para más detalles.','error');
}

/* Default: respuesta genérica de fallo */
respondAndExit('Error en el registro',$errMsg,'error');
