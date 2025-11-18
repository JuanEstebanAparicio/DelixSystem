// Código JS para cargar el detalle del pedido en el modal
const btnVerDetalles = document.querySelectorAll('.btnVerDetalles');
const modal = document.getElementById("modalDetalles");
const modalBody = document.getElementById("modalBody");
const closeModal = document.getElementById("closeModal");

btnVerDetalles.forEach(btn => {
    btn.addEventListener('click', (e) => {
        const pedidoId = e.target.dataset.id; // Obtén el ID del pedido
        if (!pedidoId) return;

        // Realizamos la petición para obtener los detalles del pedido
        fetch(`/DelixSystem/app/controllers/detalles_pedido.php?id=${pedidoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    // Rellenamos el modal con los detalles
                    modalBody.innerHTML = `
                        <p><strong>Fecha:</strong> ${data.pedido.created_at}</p>
                        <p><strong>Estado:</strong> ${data.pedido.estado}</p>
                        <p><strong>Total:</strong> ${data.pedido.total_pedido}</p>
                        <p><strong>Artículos:</strong></p>
                        <ul>
                            ${data.pedido.articulos.map(item => `<li>${item.nombre_platillo} x ${item.cantidad}</li>`).join('')}
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
closeModal.addEventListener('click', () => {
    modal.style.display = "none";
});

// Cerrar el modal si el usuario hace clic fuera de él
window.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});
