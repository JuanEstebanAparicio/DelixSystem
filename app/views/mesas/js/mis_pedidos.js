// ===============================
// BOTONES VER DETALLES (NUEVO)
// ===============================
document.querySelectorAll('.detalles-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;

        fetch('/DelixSystem/app/views/mesas/php/obtener_items.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_pedido=${id}`
        })
        .then(r => r.json())
        .then(data => {

            if (!data.ok) {
                Swal.fire('Error', data.error, 'error');
                return;
            }

            let html = "";

            data.items.forEach(i => {
                let subtotal = i.precio * i.cantidad;

                html += `
                <div class="item-row">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z"/>
                    </svg>

                    <div class="item-info">
                        <strong>${i.nombre_platillo}</strong>
                        <span>${i.cantidad} × $${Intl.NumberFormat('es-CO').format(i.precio)}</span>
                    </div>

                    <div class="item-precio">
                        <strong>$${Intl.NumberFormat('es-CO').format(subtotal)}</strong>
                    </div>
                </div>`;
            });

            document.getElementById('detallesContenido').innerHTML = html;

            // activar animación
            document.getElementById('modalDetalles').classList.add('active');
        });
    });
});

// ===============================
// CERRAR MODAL NUEVO
// ===============================
document.querySelector('.cerrar').onclick = () =>
    document.getElementById('modalDetalles').classList.remove('active');

window.onclick = e => {
    if (e.target.id === 'modalDetalles')
        document.getElementById('modalDetalles').classList.remove('active');
};
