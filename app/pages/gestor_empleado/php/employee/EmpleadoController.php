<?php
require_once __DIR__ . '/../Key/DynamicKeyModel.php';
require_once __DIR__ . '/EmpleadoModel.php';
header("Content-Type: application/json; charset=utf-8");

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $code = $_POST['codigo_dinamico'] ?? null;
    $name = $_POST['nombre_completo'] ?? null;
    $email = $_POST['correo'] ?? null;
    $document = $_POST['documento'] ?? null;

    if (!$code || !$name || !$email || !$document) {
        throw new Exception("Missing required fields");
    }

    $keyModel = new DynamicKeyModel();
    $empModel = new EmpleadoModel();

    // 1️⃣ Validate the dynamic access code
    $query = "SELECT * FROM dynamic_keys 
              WHERE code = :code AND used = false AND expires_at > NOW() 
              LIMIT 1";
    $stmt = $keyModel->conn->prepare($query);
    $stmt->execute([':code' => $code]);
    $keyData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$keyData) {
        throw new Exception("Invalid or expired code");
    }

    $adminId = $keyData['admin_id'];

    // 2️⃣ Prevent duplicate registration
    if ($empModel->employeeExists($email, $adminId)) {
        throw new Exception("You are already registered with this restaurant.");
    }

    // 3️⃣ Register the employee
    $empModel->registerEmployee($adminId, $name, $email, $document);

    // 4️⃣ Mark code as used
    $update = "UPDATE dynamic_keys SET used = true WHERE id = :id";
    $stmt = $keyModel->conn->prepare($update);
    $stmt->execute([':id' => $keyData['id']]);

    // 5️⃣ (Optional) Start a session for this employee
    session_start();
    $_SESSION['role'] = 'employee';
    $_SESSION['admin_id'] = $adminId;
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;

    echo json_encode([
        "status" => "ok",
        "message" => "Employee successfully registered",
        "admin_id" => $adminId
    ]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
