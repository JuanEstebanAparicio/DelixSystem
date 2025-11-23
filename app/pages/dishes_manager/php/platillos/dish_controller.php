<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once(__DIR__ . '/dishes.php');
require_once(__DIR__ . '/dishes_crud.php');
require_once(dirname(__DIR__, 4) . '/middleware/universal_guard.php');
require_once(dirname(__DIR__, 4) . '/middleware/controller_bootstrap.php');
require_once(dirname(__DIR__, 4) . '/config/supabase_img.php');

try {
    $user = universalGuard();
    if (!$user) {
        throw new Exception("⚠️ No autorizado.");
    }

    if ($user['tipo'] === 'propietario') {

        $id_user = $user['id'];

    } elseif ($user['tipo'] === 'empleado') {

        $stmt = $conexion->prepare("
            SELECT user_id 
            FROM employees 
            WHERE id = :emp_id 
            LIMIT 1
        ");
        $stmt->execute([':emp_id' => $user['id']]);
        $owner = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$owner || empty($owner['user_id'])) {
            throw new Exception("No se pudo determinar el propietario asociado al empleado.");
        }

        $id_user = $owner['user_id'];

    } else {
        throw new Exception("Rol no permitido.");
    }

    $roundAction = $_POST['action'] ?? '';
    $actionMap = [
        'create' => 'crear',
        'add'    => 'crear',
        'update' => 'editar',
        'edit'   => 'editar',
        'delete' => 'eliminar'
    ];

    $permissionAction = $actionMap[$roundAction] ?? 'ver';

    verifyRoleAccess('menu', $permissionAction);


    $crud = new dishes_crud();

    $methodMap = [
        'create' => 'add',
        'add' => 'add',
        'update' => 'edit',
        'edit' => 'edit',
        'delete' => 'delete'
    ];

    $method = $methodMap[$roundAction] ?? null;

    if (!$method) {
        throw new Exception("Acción no válida.");
    }

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

    if ($method === 'add') {

        $name = trim($_POST['name_dish']);
        $price = $_POST['price'];
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $state = $_POST['state'] ?? 'Activo';

        if (!empty($_POST['new_category']))
            $category = trim($_POST['new_category']);

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

    if ($method === 'edit') {

        $id = intval($_POST['id']);
        if ($id <= 0) throw new Exception("ID inválido.");

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

        if (!empty($_FILES['photo']['name'])) {
            $photo = $uploadPhoto($_FILES['photo'], $category, $name, $photo);
        }

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
