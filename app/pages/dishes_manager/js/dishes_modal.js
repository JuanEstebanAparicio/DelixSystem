function showModal(id) {
  document.getElementById(id).classList.remove("hidden");
}

function hideModal(id) {
  document.getElementById(id).classList.add("hidden");
}

const UNIT_GROUPS = {
  non_convertible: ['unidad', 'paquete', 'caja', 'bolsa'],
  weight: ['kg', 'g', 'mg'],
  volume: ['litro', 'l', 'ml', 'cc', 'galon', 'galón']
};

function normalizeUnit(u) {
  return (u || '').toString().trim().toLowerCase();
}

function getUnitGroup(unit) {
  const u = normalizeUnit(unit);
  if (UNIT_GROUPS.non_convertible.includes(u)) return 'non_convertible';
  if (UNIT_GROUPS.weight.includes(u)) return 'weight';
  if (UNIT_GROUPS.volume.includes(u)) return 'volume';
  return 'unknown';
}

function renderUnitControl(unitVal, ingredientId) {
  const group = getUnitGroup(unitVal);
  const name = `unit_${ingredientId}`;
  const id = `unit_${ingredientId}`;
  const opt = (val, label, selected) => `<option value="${val}" ${selected ? 'selected' : ''}>${label}</option>`;

  if (group === 'non_convertible') {
    const display = unitVal || 'unidad';
    return `
      <input type="hidden" name="${name}" value="${display}">
      <select disabled class="unit-display" data-ingredient="${ingredientId}">
        <option selected>${display}</option>
      </select>
    `;
  }

  if (group === 'weight') {
    return `
      <select name="${name}" id="${id}">
        ${opt('kg', 'Kg', normalizeUnit(unitVal) === 'kg')}
        ${opt('g', 'g', normalizeUnit(unitVal) === 'g')}
        ${opt('mg', 'mg', normalizeUnit(unitVal) === 'mg')}
      </select>
    `;
  }

  if (group === 'volume') {
    return `
      <select name="${name}" id="${id}">
        ${opt('litro', 'Litro (L)', normalizeUnit(unitVal) === 'litro' || normalizeUnit(unitVal) === 'l')}
        ${opt('ml', 'ml', normalizeUnit(unitVal) === 'ml')}
        ${opt('cc', 'cc', normalizeUnit(unitVal) === 'cc')}
        ${opt('galon', 'Galón', normalizeUnit(unitVal) === 'galon' || normalizeUnit(unitVal) === 'galón')}
      </select>
    `;
  }

  return `
    <select name="${name}" id="${id}">
      <option value="${unitVal}" selected>${unitVal || 'unidad'}</option>
      <option value="unidad">unidad</option>
    </select>
  `;
}

async function safeFetchTextJson(url, options = {}) {
  try {
    const res = await fetch(url, options);
    const text = await res.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch {
      data = { success: false, error: text || 'Respuesta no JSON' };
    }
    return { res, data };
  } catch (err) {
    return { res: null, data: { success: false, error: err.message } };
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;

  if (window.jQuery && $("#ingredients").length) {
    $("#ingredients").select2({
      placeholder: "Selecciona los ingredientes...",
      width: "100%"
    });

    $("#ingredients").on("change", function () {
      const selected = $(this).val() || [];
      const container = $("#ingredientQuantities");
      container.empty();
      selected.forEach(id => {
        const opt = $(`#ingredients option[value="${id}"]`);
        const name = opt.text() || `#${id}`;
        const unitFromOption = opt.data('unit') || '';
        const inputId = "quantity_" + id;
        const unitControlHTML = renderUnitControl(unitFromOption, id);
        const block = `
          <div class="ingredient-quantity-block" data-ing="${id}">
            <label for="${inputId}">${name} - Cantidad:</label>
            <input type="number" step="0.01" name="quantity_${id}" id="${inputId}" placeholder="Ej: 2">
            ${unitControlHTML}
          </div>
        `;
        container.append(block);
      });
    });
  }

  const form = document.getElementById("dishForm");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      Alerts.loading("Guardando plato...");
      const formData = new FormData(form);
      const { res, data } = await safeFetchTextJson("../php/platillos/dish_controller.php", {
        method: "POST",
        body: formData
      });
      Alerts.close();
      if (!res) {
        Alerts.error(data.error || "Error de conexión");
        return;
      }
      if (!res.ok) {
        Alerts.error((data && (data.error || data.message)) || "No tienes permisos suficientes");
        return;
      }
      if (data && data.success) {
        hideModal("formModal");
        Alerts.success(data.message || "Plato guardado correctamente");
        loadDishes();
      } else {
        Alerts.error((data && (data.error || data.message)) || "No se pudo procesar la solicitud.");
      }
    });
  }
});

function newDish() {
  const form = document.getElementById("dishForm");
  if (form) form.reset();
  const idEl = document.getElementById("dish_id");
  if (idEl) idEl.value = "";
  const actionEl = document.getElementById("action");
  if (actionEl) actionEl.value = "add";
  const modalTitle = document.getElementById("modalTitle");
  if (modalTitle) modalTitle.textContent = "Registrar Plato";
  const submitBtn = document.getElementById("submitBtn");
  if (submitBtn) submitBtn.textContent = "Registrar Plato";
  const hoy = new Date().toISOString().split("T")[0];
  const createdAt = document.getElementById("created_at");
  if (createdAt) createdAt.value = hoy;
  if (window.jQuery && $("#ingredients").length) $("#ingredients").val(null).trigger("change");
  const iq = document.getElementById("ingredientQuantities");
  if (iq) iq.innerHTML = "";
  const photoContainer = document.getElementById("currentPhotoContainer");
  if (photoContainer) photoContainer.classList.add("hidden");
  const hiddenPhoto = document.getElementById("current_photo_input");
  if (hiddenPhoto) hiddenPhoto.value = "";
  showModal("formModal");
}

function editDish(raw) {
  const data = (typeof raw === 'string') ? JSON.parse(raw) : raw;
  const form = document.getElementById("dishForm");
  if (form) form.reset();
  const dishId = document.getElementById("dish_id");
  if (dishId) {
    dishId.name = "id";
    dishId.value = data.id || "";
  }
  const actionEl = document.getElementById("action");
  if (actionEl) actionEl.value = "edit";
  const nameEl = document.getElementById("name_dish");
  if (nameEl) nameEl.value = data.name_dish || "";
  const priceEl = document.getElementById("price");
  if (priceEl) priceEl.value = data.price || "";
  const categoryEl = document.getElementById("category");
  if (categoryEl) categoryEl.value = data.category || "";
  const descriptionEl = document.getElementById("description");
  if (descriptionEl) descriptionEl.value = data.description || "";
  const stateEl = document.getElementById("state");
  if (stateEl) stateEl.value = data.state || "";
  const cont = document.getElementById("currentPhotoContainer");
  const img = document.getElementById("currentPhoto");
  const hiddenInput = document.getElementById("current_photo_input");
  if (!data.photo) {
    if (cont) cont.classList.add("hidden");
    if (hiddenInput) hiddenInput.value = "";
  } else {
    if (img) img.src = data.photo.startsWith("http") ? data.photo : "../" + data.photo;
    if (cont) cont.classList.remove("hidden");
    if (hiddenInput) hiddenInput.value = data.photo;
  }

  if (Array.isArray(data.ingredients)) {
    const ids = data.ingredients.map(i => i.id ? String(i.id) : String(i));
    if (window.jQuery && $("#ingredients").length) {
      $("#ingredients").val(ids).trigger("change");
    } else {
      ids.forEach(id => {
        const opt = document.querySelector(`#ingredients option[value="${id}"]`);
        if (opt) opt.selected = true;
      });
    }

    setTimeout(() => {
      const container = $("#ingredientQuantities");
      container.empty();
      data.ingredients.forEach(ing => {
        const inputId = "quantity_" + ing.id;
        const unitFromData = ing.unit || '';
        const unitControlHTML = renderUnitControl(unitFromData, ing.id);
        const quantityVal = (ing.quantity_used !== undefined && ing.quantity_used !== null) ? ing.quantity_used : '';
        container.append(`
          <div class="ingredient-quantity-block" data-ing="${ing.id}">
            <label for="${inputId}">${ing.name || ''} - Cantidad:</label>
            <input type="number" step="0.01" name="quantity_${ing.id}" id="${inputId}" value="${quantityVal}">
            ${unitControlHTML}
          </div>
        `);
      });
    }, 120);
  }

  const modalTitle = document.getElementById("modalTitle");
  if (modalTitle) modalTitle.textContent = "Editar Plato";
  const submitBtn = document.getElementById("submitBtn");
  if (submitBtn) submitBtn.textContent = "Actualizar Plato";
  showModal("formModal");
}

async function deleteDish(id) {
  const confirmed = await Alerts.confirm("¿Seguro que deseas eliminar este plato?", "Eliminar Plato");
  if (!confirmed) return;
  Alerts.loading("Eliminando plato...");
  const formData = new FormData();
  formData.append("action", "delete");
  formData.append("id", id);
  const { res, data } = await safeFetchTextJson("../php/platillos/dish_controller.php", {
    method: "POST",
    body: formData
  });
  Alerts.close();
  if (!res) {
    Alerts.error(data.error || "Error de conexión");
    return;
  }
  if (!res.ok) {
    Alerts.error((data && (data.error || data.message)) || "No tienes permisos suficientes");
    return;
  }
  if (data && data.success) {
    Alerts.success("Plato eliminado exitosamente");
    loadDishes();
  } else {
    Alerts.error((data && (data.error || data.message)) || "No se pudo eliminar.");
  }
}

const categorySelect = document.getElementById("category");
const newCategoryInput = document.getElementById("newCategoryInput");

if (categorySelect && newCategoryInput) {
  categorySelect.addEventListener("change", () => {
    if (categorySelect.value === "__new__") {
      newCategoryInput.classList.remove("hidden-input");
      newCategoryInput.focus();
    } else {
      newCategoryInput.classList.add("hidden-input");
      newCategoryInput.value = "";
    }
  });
}
