// ===============================
// 🔹 MODALES CON ANIMACIÓN SUAVE
// ===============================
function showModal(id) {
  const modal = document.getElementById(id);
  modal.classList.remove("hidden");
  requestAnimationFrame(() => {
    modal.style.opacity = "1";
    modal.style.transform = "scale(1)";
  });
}

function hideModal(id) {
  const modal = document.getElementById(id);
  modal.style.opacity = "0";
  modal.style.transform = "scale(0.95)";
  setTimeout(() => modal.classList.add("hidden"), 300);
}

// ===============================
// 🔹 CONFIGURACIÓN DE FORMULARIO
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;

  // Inicializar select2 si existe
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

  // Evento del formulario principal
  const form = document.getElementById("dishForm");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(form);
      const action = formData.get("action") || "add";

      try {
        const res = await fetch("../php/platillos/dish_controller.php", {
          method: "POST",
          body: formData
        });

        const data = await res.json();
        if (data.success) {
          hideModal("formModal");
          alert(data.message || "✅ Operación realizada correctamente");
          loadDishes();
        } else {
          alert("❌ Error: " + (data.error || "No se pudo procesar la solicitud."));
        }
      } catch (err) {
        alert("⚠️ Error al enviar los datos: " + err.message);
      }
    });
  }

  // Cargar los platos al iniciar
  loadDishes();
});

// ===============================
// 🔹 NUEVO PLATO
// ===============================
function newDish() {
  const form = document.getElementById("dishForm");
  form.reset();

  document.getElementById("dish_id").value = "";
  document.getElementById("modalTitle").textContent = "Registrar Plato";
  document.getElementById("submitBtn").textContent = "Registrar Plato";
  document.getElementById("action").value = "add";

  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;

  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden");

  const currentPhotoInput = document.getElementById("current_photo_input");
  if (currentPhotoInput) currentPhotoInput.value = "";

  $("#ingredients").val(null).trigger("change");
  $("#ingredientQuantities").empty();

  showModal("formModal");
}

// ===============================
// 🔹 EDITAR PLATO
// ===============================
function editDish(data) {
  const form = document.getElementById("dishForm");
  form.reset();

  document.getElementById("dish_id").name = "id"; 
  document.getElementById("dish_id").value = data.id;
  document.getElementById("name_dish").value = data.name_dish || "";
  document.getElementById("price").value = data.price || "";
  document.getElementById("category").value = data.category || "";
  document.getElementById("description").value = data.description || "";
  document.getElementById("state").value = data.state || "Activo";
  document.getElementById("action").value = "edit";

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

  if (Array.isArray(data.ingredients)) {
    const ingredientIds = data.ingredients.map(i => i.id);
    $("#ingredients").val(ingredientIds).trigger("change");

    setTimeout(() => {
      const container = $("#ingredientQuantities");
      container.empty();

      data.ingredients.forEach(ing => {
        const inputId = "quantity_" + ing.id;
        const block = `
          <div class="ingredient-quantity-block">
            <label for="${inputId}">${ing.name} - Cantidad:</label>
            <input type="number" step="0.01" name="quantity_${ing.id}" id="${inputId}" value="${ing.quantity_used}">
            <select name="unit_${ing.id}">
              <option value="kg" ${ing.unit === "kg" ? "selected" : ""}>kg</option>
              <option value="litros" ${ing.unit === "litros" ? "selected" : ""}>litros</option>
              <option value="unidad" ${ing.unit === "unidad" ? "selected" : ""}>unidad</option>
            </select>
          </div>
        `;
        container.append(block);
      });
    }, 100);
  } else {
    $("#ingredients").val(null).trigger("change");
    $("#ingredientQuantities").empty();
  }

  document.getElementById("modalTitle").textContent = "Editar Plato";
  document.getElementById("submitBtn").textContent = "Actualizar Plato";
  showModal("formModal");
}


// ===============================
// 🔹 ELIMINAR PLATO
// ===============================
async function deleteDish(id) {
  if (!confirm("¿Seguro que deseas eliminar este plato?")) return;

  const formData = new FormData();
  formData.append("action", "delete");
  formData.append("id", id);

  try {
    const res = await fetch("../php/platillos/dish_controller.php", {
      method: "POST",
      body: formData
    });
    const data = await res.json();

    if (data.success) {
      alert(data.message || "🗑️ Plato eliminado correctamente");
      loadDishes();
    } else {
      alert("❌ Error: " + (data.error || "No se pudo eliminar."));
    }
  } catch (err) {
    alert("⚠️ Error al eliminar: " + err.message);
  }
}

// ===============================
// 🔹 CARGAR Y RENDERIZAR PLATOS
// ===============================
async function loadDishes() {
  const grid = document.getElementById("dishGrid");
  grid.innerHTML = "<p class='loading'>Cargando platos...</p>";

  try {
    const response = await fetch("../php/utilidades/get_dish.php");
    const data = await response.json();

    if (!data.success) throw new Error(data.error);
    renderDishes(data.platos, data.ingredientes);
  } catch (error) {
    grid.innerHTML = `<p class='error'>Error: ${error.message}</p>`;
  }
}

function renderDishes(platos, ingredientes) {
  const grid = document.getElementById("dishGrid");
  grid.innerHTML = "";

  const categorias = {};
  platos.forEach(p => {
    const cat = p.category || "Sin categoría";
    if (!categorias[cat]) categorias[cat] = [];
    categorias[cat].push(p);
  });

  for (const cat in categorias) {
    categorias[cat].forEach(dish => {
      const imgPath = dish.photo && dish.photo.trim() !== ""
        ? (dish.photo.startsWith("media/") ? "../" + dish.photo : "../media/" + dish.photo)
        : "../img/default.png";

      const card = document.createElement("div");
      card.className = "ingredient-card card";
      card.dataset.category = cat;
      card.innerHTML = `
        <div class="card-image">
          <img src="${imgPath}" alt="${dish.name_dish}">
        </div>
        <div class="card-body">
          <h4 class="ingredient-name">${dish.name_dish}</h4>
          <p class="ingredient-cost">$${Number(dish.price).toLocaleString()}</p>
          <p class="ingredient-state ${dish.state.toLowerCase()}">${dish.state}</p>
          <p class="ingredient-desc">${dish.description || "Sin descripción"}</p>
          ${renderIngredients(dish.ingredients, ingredientes)}
        </div>
        <div class="card-footer">
          <button class="btn btn-edit" onclick='editDish(${JSON.stringify(dish)})'>✏️</button>
          <button class="btn btn-delet" onclick="deleteDish(${dish.id})">🗑️</button>
        </div>
      `;
      grid.appendChild(card);
    });
  }

  const createCard = document.createElement("div");
  createCard.className = "ingredient-card card create-card";
  createCard.id = "globalCreateCard";
  createCard.onclick = () => newDish();
  createCard.innerHTML = `
    <div class="card-body text-center">
      <span class="plus-icon">+</span>
      <p>Crear Plato</p>
    </div>
  `;
  grid.appendChild(createCard);
}

function renderIngredients(ids, ingredientes) {
  if (!ids || ids.length === 0) return "";
  const names = ids
    .map(id => {
      const ing = ingredientes.find(i => i.id == id);
      return ing ? ing.name : "";
    })
    .filter(Boolean);
  return `
    <p><strong>Ingredientes:</strong></p>
    <ul>${names.map(n => `<li>${n}</li>`).join("")}</ul>
  `;
}
