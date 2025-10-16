<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../../../../config/supabase.php';
include __DIR__ . '/MesaModel.php';

$mesaModel = new MesaModel($conexion);
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

try {
    // 🔹 ELIMINAR MESA
    if (($_POST['accion'] ?? '') === 'eliminar') {
        $id_mesa = $_POST['id_mesa'] ?? null;

        if ($id_mesa) {
            $mesaModel->eliminarMesa($id_mesa);
            $response = ['status' => 'success', 'message' => 'Mesa eliminada correctamente'];
        } else {
            $response = ['status' => 'error', 'message' => 'ID de mesa no proporcionado'];
        }

        if ($isAjax) {
            echo json_encode($response);
            exit;
        }
    }

    // 🔹 CREAR o EDITAR MESA
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';
        $id_area = $_POST['id_area'] ?? null;
        $nombre = trim($_POST['nombre_mesa'] ?? '');
        $id_mesa = $_POST['id_mesa'] ?? null;

        // Validación básica
        if (empty($nombre) || empty($id_area)) {
            $response = ['status' => 'error', 'message' => 'Datos incompletos'];
            echo json_encode($response);
            exit;
        }

        // Evitar duplicados
        if ($mesaModel->mesaExiste($nombre, $id_area, $accion === 'editar' ? $id_mesa : null)) {
            $response = ['status' => 'warning', 'message' => 'Ya existe una mesa con ese nombre en esta área'];
            echo json_encode($response);
            exit;
        }

        // 🔹 Crear
        if ($accion === 'crear') {
            $nuevaMesa = $mesaModel->crearMesa($id_area, $nombre);

            if ($nuevaMesa && isset($nuevaMesa['id_mesa'])) {
                $response = [
                    'status' => 'success',
                    'message' => 'Mesa creada correctamente',
                    'data' => [
                        'id_mesa' => $nuevaMesa['id_mesa'],
                        'id_area' => $id_area,
                        'nombre' => $nombre
                    ]
                ];
            } else {
                $response = ['status' => 'error', 'message' => 'No se pudo crear la mesa'];
            }

            echo json_encode($response);
            exit;
        }

        // 🔹 Editar
        if ($accion === 'editar' && $id_mesa) {
            $mesaModel->editarMesa($id_mesa, $id_area, $nombre);
            $response = [
                'status' => 'success',
                'message' => 'Mesa actualizada correctamente',
                'data' => [
                    'id_mesa' => $id_mesa,
                    'id_area' => $id_area,
                    'nombre' => $nombre
                ]
            ];

            if ($isAjax) {
                echo json_encode($response);
            } else {
                header("Location: ../../view/gestion_mesas.php?success=mesa_editada");
            }
            exit;
        }

        // Acción inválida
        $response = ['status' => 'error', 'message' => 'Acción no válida'];

        if ($isAjax) {
            echo json_encode($response);
        } else {
            header("Location: ../../view/gestion_mesas.php?error=accion_invalida");
        }
        exit;
    }

    // 🔹 Si llega aquí, no hay acción válida
    if ($isAjax) {
        echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
        exit;
    } else {
        header("Location: ../../view/gestion_mesas.php");
        exit;
    }
} catch (Exception $e) {
    if ($isAjax) {
        echo json_encode(['status' => 'error', 'message' => 'Error interno: ' . $e->getMessage()]);
        exit;
    } else {
        header("Location: ../../view/gestion_mesas.php?error=excepcion");
        exit;
    }
}
?>
