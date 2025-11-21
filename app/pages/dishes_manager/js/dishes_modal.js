// ---------- MODAL: CREAR, EDITAR, ELIMINAR, FORMULARIO ---------- //

function showModal(id) {
  document.getElementById(id).classList.remove("hidden");
}

function hideModal(id) {
  document.getElementById(id).classList.add("hidden");
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
        const name = $('#ingredients option[value="' + id + '"]').text();
        const inputId = "quantity_" + id;
        container.append(`
          <div class="ingredient-quantity-block">
            <label for="${inputId}">${name} - Cantidad:</label>
            <input type="number" step="0.01" name="quantity_${id}" id="${inputId}">
            <select name="unit_${id}">
              <option value="kg">kg</option>
              <option value="litros">litros</option>
              <option value="unidad">unidad</option>
            </select>
          </div>
        `);
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
function newDish() {
  const form = document.getElementById("dishForm");
  form.reset();
  document.getElementById("dish_id").value = "";
  document.getElementById("action").value = "add";
  document.getElementById("modalTitle").textContent = "Registrar Plato";
  document.getElementById("submitBtn").textContent = "Registrar Plato";
  const hoy = new Date().toISOString().split("T")[0];
  document.getElementById("created_at").value = hoy;
  $("#ingredients").val(null).trigger("change");
  $("#ingredientQuantities").empty();
  document.getElementById("currentPhotoContainer").classList.add("hidden");
  document.getElementById("current_photo_input").value = "";
  showModal("formModal");
}
// CREAR PLATO
function editDish(raw) {
  const data = JSON.parse(raw);

  const form = document.getElementById("dishForm");
  form.reset();

  document.getElementById("dish_id").name = "id";
  document.getElementById("dish_id").value = data.id;
  document.getElementById("action").value = "edit";
  document.getElementById("name_dish").value = data.name_dish;
  document.getElementById("price").value = data.price;
  document.getElementById("category").value = data.category;
  document.getElementById("description").value = data.description;
  document.getElementById("state").value = data.state;

  // Foto actual
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

  // Ingredientes del plato
  if (Array.isArray(data.ingredients)) {
    const ids = data.ingredients.map(i => i.id);

    // select2
    $("#ingredients").val(ids).trigger("change");

    // cargar cantidades después de que select2 renderice
    setTimeout(() => {
      const container = $("#ingredientQuantities");
      container.empty();

      data.ingredients.forEach(ing => {
        const inputId = "quantity_" + ing.id;

        container.append(`
          <div class="ingredient-quantity-block">
            <label for="${inputId}">${ing.name} - Cantidad:</label>
            <input type="number" step="0.01" name="quantity_${ing.id}" id="${inputId}" value="${ing.quantity_used}">
            <select name="unit_${ing.id}">
              <option value="kg" ${ing.unit === "kg" ? "selected" : ""}>kg</option>
              <option value="litros" ${ing.unit === "litros" ? "selected" : ""}>litros</option>
              <option value="unidad" ${ing.unit === "unidad" ? "selected" : ""}>unidad</option>
            </select>
          </div>
        `);
      });
    }, 100);
  }

  document.getElementById("modalTitle").textContent = "Editar Plato";
  document.getElementById("submitBtn").textContent = "Actualizar Plato";

  showModal("formModal");
}


// ELIMINAR PLATO
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

// Categoría dinámica
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
