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

  if (window.jQuery && $('#ingredients').length) {
    $('#ingredients').select2({
      placeholder: 'Selecciona los ingredientes...',
      width: '100%'
    });

    $('#ingredients').on('change', function () {
      const selected = $(this).val() || [];
      const container = $('#ingredientQuantities');
      container.empty();

      selected.forEach(id => {
        const name = $('#ingredients option[value="' + id + '"]').text();
        const inputId = 'quantity_' + id;
        const block = `
          <div class="ingredient-quantity-block">
            <label for="${inputId}">${name} - Cantidad:</label>
            <input type="number" step="0.01" name="quantity_${id}" id="${inputId}" placeholder="Ej: 2">
            <select name="unit_${id}">
              <option value="kg">kg</option>
              <option value="litros">litros</option>
              <option value="unidad">unidad</option>
            </select>
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

      const formData = new FormData(form);
      const action = form.action;

      try {
        const res = await fetch(action, {
          method: "POST",
          body: formData
        });

        const data = await res.json();
        if (data.success) {
          hideModal("formModal");
          alert("✅ Operación realizada correctamente");
          loadDishes();
        } else {
          alert("❌ Error: " + (data.error || "No se pudo procesar la solicitud."));
        }
      } catch (err) {
        alert("⚠️ Error al enviar los datos: " + err.message);
      }
    });
  }
});

function newDish() {
  const form = document.getElementById("dishForm");
  form.reset();

  document.getElementById("dish_id").value = "";
  document.getElementById("modalTitle").textContent = "Registrar Plato";
  document.getElementById("submitBtn").textContent = "Registrar Plato";
  form.action = "../php/dish_add.php";

  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;

  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden");

  const currentPhotoInput = document.getElementById("current_photo_input");
  if (currentPhotoInput) currentPhotoInput.value = "";

  $('#ingredients').val(null).trigger('change');
  $('#ingredientQuantities').empty();

  showModal("formModal");
}

function editDish(data) {
  document.getElementById("dish_id").value = data.id || "";
  document.getElementById("name_dish").value = data.name_dish || "";
  document.getElementById("price").value = data.price || "";
  document.getElementById("category").value = data.category || "";
  document.getElementById("description").value = data.description || "";
  document.getElementById("state").value = data.state || "Activo";

  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;

  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  const currentPhoto = document.getElementById("currentPhoto");
  const currentPhotoInput = document.getElementById("current_photo_input");

  if (data.photo && data.photo.trim() !== "") {
    currentPhoto.src = "../" + data.photo;
    currentPhotoContainer.classList.remove("hidden");
    currentPhotoInput.value = data.photo;
  } else {
    currentPhotoContainer.classList.add("hidden");
    currentPhotoInput.value = "";
  }

  if (data.ingredients && Array.isArray(data.ingredients)) {
    let ingredientIds = [];
    if (data.ingredients.length > 0 && typeof data.ingredients[0] === 'object') {
      ingredientIds = data.ingredients.map(i => i.id);
    } else {
      ingredientIds = data.ingredients.map(i => i);
    }

    $('#ingredients').val(ingredientIds).trigger('change');

    setTimeout(() => {
      const container = $('#ingredientQuantities');
      container.empty();

      ingredientIds.forEach(id => {
        const name = $('#ingredients option[value="' + id + '"]').text();
        const inputId = 'quantity_' + id;
        const block = `
          <div class="ingredient-quantity-block">
            <label for="${inputId}">${name} - Cantidad:</label>
            <input type="number" step="0.01" name="quantity_${id}" id="${inputId}" placeholder="Ej: 2">
            <select name="unit_${id}">
              <option value="kg">kg</option>
              <option value="litros">litros</option>
              <option value="unidad" selected>unidad</option>
            </select>
          </div>
        `;
        container.append(block);
      });
    }, 100);
  } else {
    $('#ingredients').val(null).trigger('change');
    $('#ingredientQuantities').empty();
  }

  document.getElementById("modalTitle").textContent = "Editar Plato";
  document.getElementById("submitBtn").textContent = "Actualizar Plato";
  document.getElementById("dishForm").action = "../php/dish_edit.php";

  showModal("formModal");
}

async function deleteDish(id) {
  if (!confirm("¿Seguro que deseas eliminar este plato?")) return;

  try {
    const res = await fetch(`../php/dish_delet.php?id=${id}`);
    const data = await res.json();

    if (data.success) {
      alert("🗑️ Plato eliminado correctamente");
      loadDishes();
    } else {
      alert("❌ Error al eliminar: " + (data.error || "Error desconocido"));
    }
  } catch (err) {
    alert("⚠️ Error al eliminar: " + err.message);
  }
}
