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

  reloadCategories();
  loadDishes();
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

function editDish(data) {
  const form = document.getElementById("dishForm");
  form.reset();
  const idInput = document.getElementById("dish_id");
  idInput.name = "id";
  idInput.value = data.id;
  document.getElementById("action").value = "edit";
  document.getElementById("name_dish").value = data.name_dish || "";
  document.getElementById("price").value = data.price || "";
  document.getElementById("category").value = data.category || "";
  document.getElementById("description").value = data.description || "";
  document.getElementById("state").value = data.state || "Activo";

  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  const currentPhoto = document.getElementById("currentPhoto");
  const currentPhotoInput = document.getElementById("current_photo_input");

  if (!data.photo || data.photo.trim() === "") {
    currentPhotoContainer.classList.add("hidden");
    currentPhotoInput.value = "";
  } else {
    currentPhoto.src = data.photo.startsWith("http") ? data.photo : "../" + data.photo;
    currentPhotoContainer.classList.remove("hidden");
    currentPhotoInput.value = data.photo;
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
            <label for="${inputId}">${ing.name || ""} - Cantidad:</label>
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
  }

  document.getElementById("modalTitle").textContent = "Editar Plato";
  document.getElementById("submitBtn").textContent = "Actualizar Plato";
  showModal("formModal");
}

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

function renderDishes(platos) {
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
      let imgPath;
      if (!dish.photo || dish.photo.trim() === "") {
        imgPath = "../img/default.png";
      } else if (dish.photo.startsWith("http")) {
        imgPath = dish.photo;
      } else {
        imgPath = "../" + dish.photo;
      }

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
          ${renderIngredients(dish.ingredients)}
        </div>
        <div class="card-footer">
          <button class="btn btn-edit" onclick='editDish(${JSON.stringify(dish)})'>✏️</button>
          <button class="btn btn-delete" onclick="deleteDish(${dish.id})">🗑️</button>
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

function renderIngredients(ingredients) {
  if (!ingredients || ingredients.length === 0) return "";
  const listItems = ingredients.map(ing => `<li>${ing.name} (${ing.quantity_used} ${ing.unit})</li>`).join("");
  return `<p><strong>Ingredientes:</strong></p><ul>${listItems}</ul>`;
}

window.mostrarCategoria = async function (categoria) {
  const grid = document.getElementById("dishGrid");
  grid.innerHTML = "<p class='loading'>Filtrando platillos...</p>";
  try {
    const response = await fetch("../php/utilidades/get_dish.php");
    const data = await response.json();
    if (!data.success) throw new Error(data.error);
    let platos = data.platos;
    if (categoria !== "Todos") {
      platos = platos.filter(p => (p.category || "Sin categoría") === categoria);
    }
    renderDishes(platos);
    document.querySelectorAll(".sidebar-item").forEach(item => {
      item.classList.toggle("active", item.textContent.trim() === categoria);
    });
    document.getElementById("sidebarMenu").classList.remove("active");
    document.getElementById("sidebarOverlay")?.classList.remove("active");
  } catch (error) {
    grid.innerHTML = `<p class='error'>Error al filtrar: ${error.message}</p>`;
  }
};

async function reloadCategories() {
  try {
    const response = await fetch("../php/utilidades/get_dish.php");
    const data = await response.json();
    const categorias = [...new Set(data.platos.map(c => c.category || "Sin categoría"))];
    const list = document.getElementById("categoryList");
    list.innerHTML = "";
    const todosLi = document.createElement("li");
    todosLi.className = "sidebar-item active";
    todosLi.textContent = "Todos";
    todosLi.onclick = (e) => mostrarCategoria("Todos", e.target);
    list.appendChild(todosLi);
    categorias.forEach(cat => {
      const li = document.createElement("li");
      li.className = "sidebar-item";
      li.textContent = cat;
      li.onclick = (e) => mostrarCategoria(cat, e.target);
      list.appendChild(li);
    });
  } catch (err) {}
}

function toggleSidebar() {
  const sidebar = document.getElementById('sidebarMenu');
  const overlay = document.getElementById('sidebarOverlay');
  sidebar.classList.toggle('active');
  overlay.classList.toggle('active');
}

document.addEventListener('click', (e) => {
  const sidebar = document.getElementById('sidebarMenu');
  const overlay = document.getElementById('sidebarOverlay');
  const hamburger = document.querySelector('.hamburger');
  if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
  }
});

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
