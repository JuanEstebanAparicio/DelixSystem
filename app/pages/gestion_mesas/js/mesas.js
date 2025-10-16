document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.container');

  // 🟢 CREAR MESA
  container.addEventListener('submit', async (e) => {
    const form = e.target.closest('form.mesa-form');
    if (!form || form.querySelector('input[name="accion"]').value !== 'crear') return;

    e.preventDefault();
    const formData = new FormData(form);
    formData.append('accion', 'crear');

    const res = await fetch('../php/mesas/MesaController.php', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });

    const data = await res.json().catch(() => null);
    if (!data) return Alerts.error('Error inesperado');

    if (data.status === 'success') {
      Alerts.success(data.message);
      agregarMesaDOM(form.closest('.area-card').querySelector('.mesas-grid'), data.data);
      form.reset();
    } else {
      Alerts.warning(data.message);
    }
  });

  // 🟠 EDITAR MESA
  container.addEventListener('submit', async (e) => {
    const form = e.target.closest('form.inline-form');
    if (!form || form.querySelector('input[name="accion"]').value !== 'editar') return;

    e.preventDefault();
    const formData = new FormData(form);
    formData.append('accion', 'editar');

    const res = await fetch('../php/mesas/MesaController.php', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });

    const data = await res.json().catch(() => null);
    if (!data) return Alerts.error('Error inesperado');

    if (data.status === 'success') {
      Alerts.success(data.message);
      actualizarMesaDOM(data.data.id_mesa, data.data.nombre);
      form.reset();
    } else {
      Alerts.warning(data.message);
    }
  });

  // 🔴 ELIMINAR MESA
  container.addEventListener('click', async (e) => {
    const btn = e.target.closest('a[data-confirm][href*="MesaController.php"]');
    if (!btn) return;

    e.preventDefault();
    const id_mesa = btn.href.split('id_mesa=')[1];
    const confirmed = await Alerts.confirm('¿Eliminar esta mesa?');
    if (!confirmed) return;

    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id_mesa', id_mesa);

    const res = await fetch('../php/mesas/MesaController.php', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });

    const data = await res.json().catch(() => null);
    if (!data) return Alerts.error('Error inesperado');

    if (data.status === 'success') {
      eliminarMesaDOM(id_mesa);
      Alerts.success(data.message);
    } else {
      Alerts.error(data.message);
    }
  });

  // 📦 FUNCIONES AUXILIARES DOM
  function agregarMesaDOM(contenedor, mesa) {
    const div = document.createElement('div');
    div.classList.add('mesa-card');
    div.dataset.id = mesa.id_mesa;
    div.innerHTML = `
      <h4><i class="fa-solid fa-chair"></i> ${mesa.nombre}</h4>
      <div class="mesa-actions">
        <form action="../php/mesas/MesaController.php" method="POST" class="inline-form" data-loader>
          <input type="hidden" name="accion" value="editar">
          <input type="hidden" name="id_mesa" value="${mesa.id_mesa}">
          <input type="hidden" name="id_area" value="${mesa.id_area}">
          <input type="text" name="nombre_mesa" placeholder="Nuevo nombre" required class="input-text-small">
          <button type="submit" class="btn-icon edit"><i class="fa-solid fa-pen"></i></button>
        </form>
        <a href="../php/mesas/MesaController.php?accion=eliminar&id_mesa=${mesa.id_mesa}" data-confirm="¿Eliminar esta mesa?" class="btn-icon delete">
          <i class="fa-solid fa-trash"></i>
        </a>
        <button type="button" class="btn-icon qr" data-id-mesa="${mesa.id_mesa}" title="Ver QR">
          <i class="fa-solid fa-qrcode"></i>
        </button>
      </div>
    `;
    contenedor.appendChild(div);
  }

  function actualizarMesaDOM(id, nuevoNombre) {
    const mesaCard = document.querySelector(`.mesa-card[data-id="${id}"]`);
    if (mesaCard) {
      const h4 = mesaCard.querySelector('h4');
      if (h4) h4.innerHTML = `<i class="fa-solid fa-chair"></i> ${nuevoNombre}`;
    }
  }

  function eliminarMesaDOM(id) {
    const mesaCard = document.querySelector(`.mesa-card[data-id="${id}"]`);
    if (mesaCard) mesaCard.remove();
  }
});
