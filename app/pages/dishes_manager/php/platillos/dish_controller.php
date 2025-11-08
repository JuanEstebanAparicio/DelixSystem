<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once(__DIR__ . '/dishes.php');
require_once(__DIR__ . '/dishes_crud.php');

try {

    if (empty($_SESSION['usuario']['id'])) {
        throw new Exception("⚠️ No autorizado.");
    }

    $id_user = intval($_SESSION['usuario']['id']);
    $crud = new dishes_crud();

    $baseDir = dirname(__DIR__, 2);
    $mediaRoot = $baseDir . '/media';

    $roundAction = $_POST['action'] ?? '';
    $actionMap = [
        'create' => 'add',
        'add' => 'add',
        'update' => 'edit',
        'edit' => 'edit',
        'delete' => 'delete'
    ];
    $method = $actionMap[$roundAction] ?? null;

    if (!$method) {
        throw new Exception("Acción no válida.");
    }

    $clearDirectory = function ($dir) {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) as $file) {
            if ($file == "." || $file == "..") continue;
            $path = "$dir/$file";
            if (is_file($path)) @unlink($path);
        }
    };

    $uploadPhoto = function ($file, $safeCategory, $safeDish, $mediaRoot, $clearDirectory) {

        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return null;

        $categoryDir = "$mediaRoot/$safeCategory";
        $dishDir = "$categoryDir/$safeDish";

        if (!is_dir($dishDir)) mkdir($dishDir, 0777, true);

        // Eliminar imágenes previas
        $clearDirectory($dishDir);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $name = uniqid("dish_") . "." . $ext;
        $target = "$dishDir/$name";

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new Exception("Error al guardar la imagen del plato.");
        }

        return "media/$safeCategory/$safeDish/$name";
    };

    // =========================================================
    // 🟢 AGREGAR PLATO
    // =========================================================
    if ($method === 'add') {

        $name = trim($_POST['name_dish']);
        $price = $_POST['price'];
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $state = $_POST['state'] ?? 'Activo';

        if (!empty($_POST['new_category'])) $category = trim($_POST['new_category']);

        $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($category));
        $safeDish = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($name));

        $photo = $uploadPhoto($_FILES['photo'] ?? [], $safeCategory, $safeDish, $mediaRoot, $clearDirectory);

        $ingredients = [];
        if (!empty($_POST['ingredients'])) {
            foreach ($_POST['ingredients'] as $idIng) {
                $ingredients[] = [
                    'id'       => $idIng,
                    'quantity' => $_POST['quantity_' . $idIng] ?? 1,
                    'unit'     => $_POST['unit_' . $idIng] ?? 'unidad'
                ];
            }
        }

        $dish = new dishes(null, $id_user, $name, $price, $category, $description, $state, date("Y-m-d H:i:s"), $photo);

        $crud->createDish($dish, $ingredients);
        echo json_encode(["success" => true, "message" => "✅ Plato creado correctamente."]);
        exit;
    }

    // =========================================================
    // ✏️ EDITAR PLATO
    // =========================================================
    if ($method === 'edit') {

        $id = intval($_POST['id']);
        if ($id <= 0) throw new Exception("ID inválido.");

        // Obtener plato actual
        $current = $crud->getDishById($id, $id_user);
        if (!$current) throw new Exception("Plato no encontrado.");

        $name = trim($_POST['name_dish']);
        $price = $_POST['price'];
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $state = $_POST['state'];
        $photo = $_POST['current_photo'] ?? $current['photo'];

        if (!empty($_POST['new_category'])) $category = trim($_POST['new_category']);

        $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($category));
        $safeDish = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($name));

        // Si sube nueva imagen → reemplazar
        if (!empty($_FILES['photo']['name'])) {
            $newPhoto = $uploadPhoto($_FILES['photo'], $safeCategory, $safeDish, $mediaRoot, $clearDirectory);
            if ($newPhoto) $photo = $newPhoto;
        }

        $ingredients = [];
        if (!empty($_POST['ingredients'])) {
            foreach ($_POST['ingredients'] as $idIng) {
                $ingredients[] = [
                    'id'       => $idIng,
                    'quantity' => $_POST['quantity_' . $idIng] ?? 1,
                    'unit'     => $_POST['unit_' . $idIng] ?? 'unidad'
                ];
            }
        }

        $dish = new dishes($id, $id_user, $name, $price, $category, $description, $state, null, $photo);

        $crud->updateDish($dish, $id, $ingredients);
        echo json_encode(["success" => true, "message" => "✅ Plato actualizado correctamente."]);
        exit;
    }

    // =========================================================
    // 🗑️ ELIMINAR PLATO
    // =========================================================
    if ($method === 'delete') {

        $id = intval($_POST['id']);
        if ($id <= 0) throw new Exception("ID inválido.");

        $crud->deleteDish($id, $id_user);
        echo json_encode(["success" => true, "message" => "🗑️ Plato eliminado correctamente."]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
