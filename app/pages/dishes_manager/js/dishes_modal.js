// ---------- MODAL: CREAR, EDITAR, ELIMINAR, FORMULARIO (mejorado con unidades) ---------- //

function showModal(id) {
  document.getElementById(id).classList.remove("hidden");
}
function hideModal(id) {
  document.getElementById(id).classList.add("hidden");
}

const UNIT_GROUPS = {
  non_convertible: ['unidad','paquete','caja','bolsa'], // mostradas pero NO editables
  weight: ['kg','g','mg'], // conversibles entre sí
  volume: ['litro','l','ml','cc','galon','galón'] // conversibles entre sí (añadí varios alias)
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

// Genera HTML del select/hidden para la unidad según el grupo
function renderUnitControl(unitVal, ingredientId) {
  const group = getUnitGroup(unitVal);
  const name = `unit_${ingredientId}`;
  const id = `unit_${ingredientId}`;

  // Helper para option markup
  const opt = (val, label, selected) => `<option value="${val}" ${selected ? 'selected' : ''}>${label}</option>`;

  if (group === 'non_convertible') {
    // si no convertible: mostrar texto y un input hidden (porque disabled no se envía)
    const display = unitVal || 'unidad';
    return `
      <input type="hidden" name="${name}" value="${display}">
      <select disabled class="unit-display" data-ingredient="${ingredientId}">
        <option selected>${display}</option>
      </select>
    `;
  }

  if (group === 'weight') {
    // solo kg, g, mg
    return `
      <select name="${name}" id="${id}">
        ${opt('kg','Kg', normalizeUnit(unitVal) === 'kg')}
        ${opt('g','g', normalizeUnit(unitVal) === 'g')}
        ${opt('mg','mg', normalizeUnit(unitVal) === 'mg')}
      </select>
    `;
  }

  if (group === 'volume') {
    // Litro, ml, cc, Galón
    return `
      <select name="${name}" id="${id}">
        ${opt('litro','Litro (L)', normalizeUnit(unitVal) === 'litro' || normalizeUnit(unitVal) === 'l')}
        ${opt('ml','ml', normalizeUnit(unitVal) === 'ml')}
        ${opt('cc','cc', normalizeUnit(unitVal) === 'cc')}
        ${opt('galon','Galón', normalizeUnit(unitVal) === 'galon' || normalizeUnit(unitVal) === 'galón')}
      </select>
    `;
  }

  // unknown: dejar un select abierto con la unidad original como opción
  return `
    <select name="${name}" id="${id}">
      <option value="${unitVal}" selected>${unitVal || 'unidad'}</option>
      <option value="unidad">unidad</option>
    </select>
  `;
}

document.addEventListener("DOMContentLoaded", () => {
  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;

  // Inicializar select2
  if (window.jQuery && $("#ingredients").length) {
    $("#ingredients").select2({
      placeholder: "Selecciona los ingredientes...",
      width: "100%"
    });

    // Cuando cambian los ingredientes seleccionados
    $("#ingredients").on("change", function () {
      const selected = $(this).val() || [];
      const container = $("#ingredientQuantities");
      container.empty();

      // Por cada id seleccionado, buscamos la option y su data-unit
      selected.forEach(id => {
        const opt = $(`#ingredients option[value="${id}"]`);
        const name = opt.text() || `#${id}`;
        const unitFromOption = opt.data('unit') || ''; // <- viene del data-unit en PHP
        const inputId = "quantity_" + id;

        // Construir el bloque usando renderUnitControl
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
      try {
        const res = await fetch("../php/platillos/dish_controller.php", {
          method: "POST",
          body: formData
        });
        const data = await res.json();
        Alerts.close();
        if (data.success) {
          hideModal("formModal");
          Alerts.success(data.message || "Plato guardado correctamente");
          loadDishes();
        } else {
          Alerts.error(data.error || "No se pudo procesar la solicitud.");
        }
      } catch (err) {
        Alerts.error("Error al enviar los datos: " + err.message);
      }
    });
  }

});

// CREAR
function newDish() {
  const form = document.getElementById("dishForm");
  form.reset();
  document.getElementById("dish_id").value = "";
  document.getElementById("action").value = "add";
  document.getElementById("modalTitle").textContent = "Registrar Plato";
  document.getElementById("submitBtn").textContent = "Registrar Plato";
  const hoy = new Date().toISOString().split("T")[0];
  document.getElementById("created_at").value = hoy;
  // limpiar select2 y contenedores
  if (window.jQuery && $("#ingredients").length) $("#ingredients").val(null).trigger("change");
  const iq = document.getElementById("ingredientQuantities");
  if (iq) iq.innerHTML = "";
  document.getElementById("currentPhotoContainer").classList.add("hidden");
  document.getElementById("current_photo_input").value = "";
  showModal("formModal");
}

// EDITAR
function editDish(raw) {
  // raw puede llegar como JSON string o como objeto, manejamos ambos
  const data = (typeof raw === 'string') ? JSON.parse(raw) : raw;

  const form = document.getElementById("dishForm");
  form.reset();

  document.getElementById("dish_id").name = "id";
  document.getElementById("dish_id").value = data.id || "";
  document.getElementById("action").value = "edit";
  document.getElementById("name_dish").value = data.name_dish || "";
  document.getElementById("price").value = data.price || "";
  document.getElementById("category").value = data.category || "";
  document.getElementById("description").value = data.description || "";
  document.getElementById("state").value = data.state || "Activo";

  // foto
  const cont = document.getElementById("currentPhotoContainer");
  const img = document.getElementById("currentPhoto");
  const hiddenInput = document.getElementById("current_photo_input");

  if (!data.photo) {
    cont.classList.add("hidden");
    hiddenInput.value = "";
  } else {
    img.src = data.photo.startsWith("http") ? data.photo : "../" + data.photo;
    cont.classList.remove("hidden");
    hiddenInput.value = data.photo;
  }

  // Ingredientes: seleccionar en select2 y renderizar cantidades con su unidad real
  if (Array.isArray(data.ingredients)) {
    const ids = data.ingredients.map(i => i.id ? String(i.id) : String(i)); // asegurar strings
    // setear select2
    if (window.jQuery && $("#ingredients").length) {
      $("#ingredients").val(ids).trigger("change");
    } else {
      // fallback: marcar opciones
      ids.forEach(id => {
        const opt = document.querySelector(`#ingredients option[value="${id}"]`);
        if (opt) opt.selected = true;
      });
    }

    // esperar a que select2 renderice y luego poblar cantidades
    setTimeout(() => {
      const container = $("#ingredientQuantities");
      container.empty();

      data.ingredients.forEach(ing => {
        const inputId = "quantity_" + ing.id;
        const unitFromData = ing.unit || ''; // unidad guardada en DB para ese relation
        // render control basado en la unidad real
        const unitControlHTML = renderUnitControl(unitFromData, ing.id);

        // si existe cantidad en el payload la colocamos
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

  document.getElementById("modalTitle").textContent = "Editar Plato";
  document.getElementById("submitBtn").textContent = "Actualizar Plato";

  showModal("formModal");
}

// ELIMINAR
async function deleteDish(id) {
  const confirmed = await Alerts.confirm("¿Seguro que deseas eliminar este plato?", "Eliminar Plato");
  if (!confirmed) return;
  Alerts.loading("Eliminando plato...");

  const formData = new FormData();
  formData.append("action", "delete");
  formData.append("id", id);

  try {
    const res = await fetch("../php/platillos/dish_controller.php", {
      method: "POST",
      body: formData
    });
    const data = await res.json();
    Alerts.close();
    if (data.success) {
      Alerts.success("Plato eliminado exitosamente");
      loadDishes();
    } else {
      Alerts.error(data.error || "No se pudo eliminar.");
    }
  } catch (err) {
    Alerts.error("Error al eliminar: " + err.message);
  }
}

// categoría dinámica (mismo que antes)
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
