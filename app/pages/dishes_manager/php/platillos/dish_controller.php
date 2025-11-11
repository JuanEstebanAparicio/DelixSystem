<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once(__DIR__ . '/dishes.php');
require_once(__DIR__ . '/dishes_crud.php');
require_once(__DIR__ . '/../../../../config/supabase_img.php'); // ✅ Importamos el helper

try {

    if (empty($_SESSION['usuario']['id'])) {
        throw new Exception("⚠️ No autorizado.");
    }

    $id_user = intval($_SESSION['usuario']['id']);
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

    // =========================================================
    // 🟢 SUBIR IMAGEN A SUPABASE
    // =========================================================
    $uploadPhoto = function ($file, $category, $dish, $photoOld = null) {

        $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($category));
        $safeDish     = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($dish));
        $path         = "platillos/$safeCategory/$safeDish";

        // Si existía una imagen previa → eliminar
        if ($photoOld) {
            $relative = str_replace(rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/dish_img/", '', $photoOld);
            supabaseDeleteImage('dish_img', $relative);
        }

        return supabaseUploadImage($file, 'dish_img', $path);
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

        // ✅ Subir imagen a Supabase
        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $photo = $uploadPhoto($_FILES['photo'], $category, $name);
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

        $current = $crud->getDishById($id, $id_user);
        if (!$current) throw new Exception("Plato no encontrado.");

        $name = trim($_POST['name_dish']);
        $price = $_POST['price'];
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $state = $_POST['state'];
        $photo = $current['photo'];

        if (!empty($_POST['new_category'])) $category = trim($_POST['new_category']);

        // ✅ Si sube nueva imagen → reemplazar
        if (!empty($_FILES['photo']['name'])) {
            $photo = $uploadPhoto($_FILES['photo'], $category, $name, $photo);
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

        $current = $crud->getDishById($id, $id_user);

        if ($current && !empty($current['photo'])) {
            $relative = str_replace(rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/dish_img/", '', $current['photo']);
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
