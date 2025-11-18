<?php
session_start();
require_once __DIR__ . '/../../../config/supabase.php';

if (empty($_SESSION['cliente'])) {
    header("Location: /DelixSystem/app/views/mesas/view/menu.php");
    exit;
}

$nombre_cliente = $_SESSION['cliente']['nombre'];
$query = "SELECT * FROM orders WHERE nombre_cliente = :nombre_cliente";
$stmt = $conexion->prepare($query);
$stmt->execute(['nombre_cliente' => $nombre_cliente]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos</title>
    <!-- Incluir CSS para el modal -->
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
   <h2>Mis Pedidos</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pedidos as $pedido): ?>
            <tr>
                <td><?= htmlspecialchars($pedido['id']) ?></td>
                <td><?= htmlspecialchars($pedido['created_at']) ?></td> <!-- O 'fecha' si es el nombre correcto -->
                <td><?= htmlspecialchars($pedido['estado']) ?></td>
                <td>
                    <button class="btnVerDetalles" data-id="<?= $pedido['id'] ?>">Ver Detalles</button>
                    <button class="btnCancelar" data-id="<?= $pedido['id'] ?>">Cancelar</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


    <!-- Modal para ver los detalles del pedido -->
    <div id="detalleModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Detalles del Pedido</h3>
            <div id="modalBody">
                <!-- Aquí se cargarán los detalles del pedido -->
            </div>
        </div>
    </div>

    <script>
        // Abrir el modal
        const modal = document.getElementById("detalleModal");
        const modalBody = document.getElementById("modalBody");
        const span = document.getElementsByClassName("close")[0];

        const btnVerDetalles = document.querySelectorAll('.btnVerDetalles');
        btnVerDetalles.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const pedidoId = e.target.dataset.id;

                // Hacer una petición para obtener los detalles del pedido
                fetch(`/DelixSystem/app/controllers/detalles_pedido.php?id=${pedidoId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.ok) {
                            // Llenar el modal con los detalles
                            modalBody.innerHTML = `
                                <p><strong>Fecha:</strong> ${data.pedido.fecha}</p>
                                <p><strong>Estado:</strong> ${data.pedido.estado}</p>
                                <p><strong>Total:</strong> ${data.pedido.total}</p>
                                <p><strong>Artículos:</strong></p>
                                <ul>
                                    ${data.pedido.articulos.map(item => `<li>${item.nombre} x ${item.cantidad}</li>`).join('')}
                                </ul>
                            `;
                            // Mostrar el modal
                            modal.style.display = "block";
                        } else {
                            Swal.fire("Error", "No se pudieron obtener los detalles del pedido", "error");
                        }
                    });
            });
        });

        // Cerrar el modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // Cerrar el modal si se hace clic fuera del modal
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };

        // Botón para cancelar el pedido
        const btnCancelar = document.querySelectorAll('.btnCancelar');
        btnCancelar.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const pedidoId = e.target.dataset.id;

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡Este pedido será cancelado!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'No, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Hacer la solicitud para cancelar el pedido
                        fetch(`/DelixSystem/app/controllers/cancelar_pedido.php?id=${pedidoId}`, {
                            method: 'GET',
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.ok) {
                                Swal.fire('Pedido Cancelado', '', 'success');
                                window.location.reload();
                            } else {
                                Swal.fire('Error', 'No se pudo cancelar el pedido', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>

    <!-- Modal -->
<div id="modalDetalles" class="modal">
    <div class="modal-content">
        <span id="closeModal" class="close">&times;</span>
        <h2>Detalles del Pedido</h2>
        <div id="modalBody">
            <!-- Aquí se mostrarán los detalles del pedido -->
        </div>
    </div>
</div>
</body>
</html>
