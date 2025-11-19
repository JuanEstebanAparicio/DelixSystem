<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once(__DIR__ . '/dishes.php');
require_once(__DIR__ . '/dishes_crud.php');
require_once(dirname(__DIR__, 4) . '/middleware/universal_guard.php');
require_once(dirname(__DIR__, 4) . '/config/supabase_img.php');

try {

    // ============================================================
    // 🟢 Obtener usuario desde universal_guard (propietario/empleado)
    // ============================================================
    $user = universalGuard();

    if (!$user) {
        throw new Exception("⚠️ No autorizado.");
    }

    // Igual que en get_storage.php
    if ($user['tipo'] === 'propietario') {
        $id_user = $user['id'];
    } elseif ($user['tipo'] === 'empleado') {
        if (!empty($user['restaurant_id'])) {
            $id_user = $user['restaurant_id'];
        } elseif (!empty($user['user_id'])) {
            $id_user = $user['user_id'];
        } else {
            throw new Exception("No se pudo determinar el propietario asociado al empleado.");
        }
    } else {
        throw new Exception("Rol no permitido.");
    }

    // ============================================================
    // 📦 CRUD de platillos
    // ============================================================
    $crud = new dishes_crud();

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

    // ============================================================
    // 🖼 Upload a Supabase
    // ============================================================
    $uploadPhoto = function ($file, $category, $dish, $photoOld = null) {

        $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($category));
        $safeDish     = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($dish));
        $path         = "platillos/$safeCategory/$safeDish";

        if ($photoOld) {
            $relative = str_replace(
                rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/dish_img/",
                '',
                $photoOld
            );
            supabaseDeleteImage('dish_img', $relative);
        }

        return supabaseUploadImage($file, 'dish_img', $path);
    };

    // ============================================================
    // 🟢 ADD DISH
    // ============================================================
    if ($method === 'add') {

        $name = trim($_POST['name_dish']);
        $price = $_POST['price'];
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $state = $_POST['state'] ?? 'Activo';

        if (!empty($_POST['new_category'])) 
            $category = trim($_POST['new_category']);

        // Imagen
        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $photo = $uploadPhoto($_FILES['photo'], $category, $name);
        }

        // Ingredientes
        $ingredients = [];
        if (!empty($_POST['ingredients'])) {
            foreach ($_POST['ingredients'] as $ingId) {
                $ingredients[] = [
                    'id' => $ingId,
                    'quantity' => $_POST['quantity_' . $ingId] ?? 1,
                    'unit' => $_POST['unit_' . $ingId] ?? 'unidad'
                ];
            }
        }

        $dish = new dishes(
            null, $id_user, $name, $price, $category,
            $description, $state, date("Y-m-d H:i:s"), $photo
        );

        $crud->createDish($dish, $ingredients);

        echo json_encode(["success" => true, "message" => "✅ Plato creado correctamente."]);
        exit;
    }

    // ============================================================
    // ✏️ EDIT DISH
    // ============================================================
    if ($method === 'edit') {

        $id = intval($_POST['id']);
        if ($id <= 0) throw new Exception("ID inválido.");

        // Verificamos que el plato pertenezca al dueño del empleado
        $current = $crud->getDishById($id, $id_user);
        if (!$current) throw new Exception("Plato no encontrado.");

        $name = trim($_POST['name_dish']);
        $price = $_POST['price'];
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $state = $_POST['state'];
        $photo = $current['photo'];

        if (!empty($_POST['new_category'])) 
            $category = trim($_POST['new_category']);

        // Reemplazar imagen
        if (!empty($_FILES['photo']['name'])) {
            $photo = $uploadPhoto($_FILES['photo'], $category, $name, $photo);
        }

        // Ingredientes nuevos
        $ingredients = [];
        if (!empty($_POST['ingredients'])) {
            foreach ($_POST['ingredients'] as $ingId) {
                $ingredients[] = [
                    'id' => $ingId,
                    'quantity' => $_POST['quantity_' . $ingId] ?? 1,
                    'unit' => $_POST['unit_' . $ingId] ?? 'unidad'
                ];
            }
        }

        $dish = new dishes(
            $id, $id_user, $name, $price, $category,
            $description, $state, null, $photo
        );

        $crud->updateDish($dish, $id, $ingredients);

        echo json_encode(["success" => true, "message" => "✅ Plato actualizado correctamente."]);
        exit;
    }

    // ============================================================
    // 🗑️ DELETE DISH
    // ============================================================
    if ($method === 'delete') {

        $id = intval($_POST['id']);
        if ($id <= 0) throw new Exception("ID inválido.");

        $current = $crud->getDishById($id, $id_user);

        if ($current && !empty($current['photo'])) {
            $relative = str_replace(
                rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/dish_img/",
                '',
                $current['photo']
            );
            supabaseDeleteImage('dish_img', $relative);
        }

        $crud->deleteDish($id, $id_user);

        echo json_encode(["success" => true, "message" => "🗑️ Plato eliminado correctamente."]);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
