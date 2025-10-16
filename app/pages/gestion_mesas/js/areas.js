document.addEventListener('DOMContentLoaded', () => {
  const formCrearArea = document.querySelector('form[action="../php/area/AreaController.php"][data-loader]');
  const container = document.querySelector('.container');

  // 🟢 CREAR ÁREA (NO TOCAR — FUNCIONA BIEN)
  if (formCrearArea) {
    formCrearArea.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(formCrearArea);
      formData.append('accion', 'crear');

      const res = await fetch(formCrearArea.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });

      const data = await res.json();
      if (data.status === 'success') {
        Alerts.success(data.message);
        agregarAreaDOM(data.data);
        formCrearArea.reset();
      } else {
        Alerts.warning(data.message);
      }
    });
  }

  // 🟠 EDITAR ÁREA (AHORA ACTUALIZA DOM INMEDIATAMENTE)
  container.addEventListener('submit', async (e) => {
    const form = e.target.closest('form[action="../php/area/AreaController.php"]');
    if (!form || form.querySelector('input[name="accion"]').value !== 'editar') return;

    e.preventDefault();
    const formData = new FormData(form);
    formData.append('accion', 'editar');

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });

      const data = await res.json();
      if (data.status === 'success') {
        Alerts.success(data.message);
        actualizarNombreAreaDOM(data.data.id_area, data.data.nombre);
        const inputNombre = form.querySelector('input[name="nombre_area"]');
        if (inputNombre) inputNombre.value = '';
      } else {
        Alerts.warning(data.message);
      }
    } catch (error) {
      Alerts.error('Error al editar el área');
      console.error(error);
    }
  });

  // 🔴 ELIMINAR ÁREA (AHORA REMUEVE INSTANTÁNEAMENTE)
  container.addEventListener('click', async (e) => {
    const btn = e.target.closest('a[data-confirm][href*="AreaController.php"]');
    if (!btn) return;

    e.preventDefault();
    const id_area = btn.href.split('id_area=')[1];
    const confirmed = await Alerts.confirm('¿Eliminar esta área y sus mesas?');

    if (!confirmed) return;

    Alerts.loading();

    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id_area', id_area);

    try {
      const res = await fetch('../php/area/AreaController.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });

      const data = await res.json();
      Alerts.close();

      if (data.status === 'success') {
        eliminarAreaDOM(id_area);
        Alerts.success('Área eliminada correctamente');
      } else {
        Alerts.error(data.message);
      }
    } catch (error) {
      Alerts.error('Error al eliminar el área');
      console.error(error);
    }
  });

  // 📦 FUNCIONES AUXILIARES DOM
  function agregarAreaDOM(area) {
    const hr = document.querySelector('.divider');
    const div = document.createElement('div');
    div.classList.add('area-card');
    div.dataset.id = area.id_area;
    div.innerHTML = `
      <div class="area-header">
        <h3><i class="fa-solid fa-layer-group"></i> ${area.nombre}</h3>
        <div class="area-actions">
          <form action="../php/area/AreaController.php" method="POST" data-loader>
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_area" value="${area.id_area}">
            <div>
              <input type="text" name="nombre_area" placeholder="Nuevo nombre" required class="input-text-small">
              <button type="submit" class="btn-icon edit"><i class="fa-solid fa-pen"></i></button>
              <a href="../php/area/AreaController.php?accion=eliminar&id_area=${area.id_area}"
                 data-confirm="¿Eliminar esta área y sus mesas?" class="btn-icon delete">
                <i class="fa-solid fa-trash"></i>
              </a>
            </div>
          </form>
        </div>
      </div>
      <form action="../php/mesa/MesaController.php" method="POST" class="add-form mesa-form">
        <input type="hidden" name="accion" value="crear">
        <input type="hidden" name="id_area" value="${area.id_area}">
        <input type="text" name="nombre_mesa" placeholder="Nombre de la mesa" required class="input-text-small">
        <button type="submit" class="btn-secondary">
          <i class="fa-solid fa-plus"></i> Añadir mesa
        </button>
      </form>
      <div class="mesas-grid"></div>
    `;
    container.insertBefore(div, hr.nextSibling);
  }

  function actualizarNombreAreaDOM(id, nuevoNombre) {
    const areaCard = document.querySelector(`.area-card[data-id="${id}"]`);
    if (areaCard) {
      const title = areaCard.querySelector('h3');
      if (title) title.innerHTML = `<i class="fa-solid fa-layer-group"></i> ${nuevoNombre}`;
    } else {
      console.warn('Área no encontrada para actualizar:', id);
    }
  }

  function eliminarAreaDOM(id) {
    const areaCard = document.querySelector(`.area-card[data-id="${id}"]`);
    if (areaCard) {
      areaCard.style.transition = 'opacity 0.3s ease';
      areaCard.style.opacity = '0';
      setTimeout(() => areaCard.remove(), 300);
    } else {
      console.warn('Área no encontrada para eliminar:', id);
    }
  }
});

