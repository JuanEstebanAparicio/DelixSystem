// ===============================================
// CANCELAR PEDIDO CON SWEETALERT
// ===============================================
//DelixSystem/app/views/mesas/js/mis_pedidos.js
document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".cancelar-btn").forEach(btn => {
        btn.addEventListener("click", () => {

            let id = btn.dataset.id;

            Swal.fire({
                title: "¿Cancelar pedido?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, cancelar",
                cancelButtonText: "No",
            }).then(result => {
                if (result.isConfirmed) {

                    // 🔥 Enviamos petición al servidor
                    fetch("/DelixSystem/app/views/mesas/php/cancelar_pedido.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `pedido_id=${id}`
                    })
                    .then(res => res.json())
                    .then(data => {

                        console.log("📦 Respuesta cancelar:", data);

                        if (data.error) {
                            Swal.fire("Error", data.error, "error");
                            return;
                        }

                        Swal.fire({
                            title: "Cancelado",
                            text: data.mensaje,
                            icon: "success",
                        }).then(() => {
                            location.reload(); // refrescar lista
                        });

                    })
                    .catch(err => {
                        console.error("❌ Error en fetch cancelar:", err);
                        Swal.fire("Error", "No se pudo cancelar el pedido.", "error");
                    });

                }
            });

        });
    });

});

