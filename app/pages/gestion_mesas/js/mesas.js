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
      limpiarInputsResiduos();
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
      <!-- Botón editar (abre modal) -->
      <button type="button"
        class="btn-icon edit"
        data-modal-target="#editMesaModal"
        data-id-mesa="${mesa.id_mesa}"
        data-id-area="${mesa.id_area}"
        data-nombre-mesa="${mesa.nombre}"
        title="Editar mesa">
        <i class="fa-solid fa-pen"></i>
      </button>

      <!-- Eliminar -->
      <a href="../php/mesas/MesaController.php?accion=eliminar&id_mesa=${mesa.id_mesa}"
         data-confirm="¿Eliminar esta mesa?"
         class="btn-icon delete">
         <i class="fa-solid fa-trash"></i>
      </a>

      <!-- Ver QR -->
      <button type="button" class="btn-icon qr"
        data-modal-target="#qrModal"
        data-id-mesa="${mesa.id_mesa}"
        title="Ver QR">
        <i class="fa-solid fa-qrcode"></i>
      </button>
    </div>
  `;

  contenedor.appendChild(div);

  // 🟢 Reenlazar evento para abrir el modal de edición
  const editButton = div.querySelector('.btn-icon.edit');
  editButton.addEventListener('click', () => {
    const modal = document.querySelector('#editMesaModal');
    if (!modal) return;
    document.getElementById('editMesaId').value = mesa.id_mesa;
    document.getElementById('editMesaAreaId').value = mesa.id_area;
    document.getElementById('editMesaName').value = mesa.nombre;
    modal.style.display = 'block';
  });
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

  // 🧹 Evita errores de inputs ocultos requeridos
  function limpiarInputsResiduos() {
    document.querySelectorAll('.mesa-card input[name="nombre_mesa"]').forEach(input => {
      if (input.offsetParent === null) input.disabled = true;
    });
  }
});
