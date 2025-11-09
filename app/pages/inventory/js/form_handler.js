// ===============================
// 🔹 VALIDAR FECHAS
// ===============================
function validarFechas() {
  const ingreso = document.getElementById("fecha_ingreso").value;
  const vencimiento = document.getElementById("fecha_vencimiento").value;

  if (ingreso && vencimiento && new Date(vencimiento) < new Date(ingreso)) {
    Alerts.warning("La fecha de vencimiento no puede ser anterior a la de ingreso.");
    return false;
  }
  return true;
}

// ===============================
// 🔹 CARGAR INVENTARIO GLOBAL
// ===============================
async function loadStorage() {
  const grid = document.getElementById("ingredientGrid");
  grid.innerHTML = "<p class='loading'>Cargando ingredientes...</p>";

  try {
    const response = await fetch("../php/utilidades/get_storage.php");
    const data = await response.json();

    if (!data.success) throw new Error(data.error);
    renderStorage(data.insumos);
  } catch (error) {
    grid.innerHTML = `<p class='error'>Error al cargar ingredientes: ${error.message}</p>`;
    Alerts.error("Error al cargar ingredientes: " + error.message);
  }
}

// ===============================
// 🔹 RENDERIZAR TARJETAS
// ===============================
function renderStorage(ingredientes) {
  const grid = document.getElementById("ingredientGrid");
  grid.innerHTML = "";

  const categorias = {};
  ingredientes.forEach(ing => {
    const cat = ing.category || "Sin categoría";
    if (!categorias[cat]) categorias[cat] = [];
    categorias[cat].push(ing);
  });

  for (const cat in categorias) {
    categorias[cat].forEach(ing => {
      const imgPath = ing.photo && ing.photo.trim() !== ""
        ? (ing.photo.startsWith("media/") ? "../" + ing.photo : "../media/" + ing.photo)
        : "../img/default.png";

      const card = document.createElement("div");
      card.className = "ingredient-card card";
      card.dataset.category = cat;

      card.innerHTML = `
        <div class="card-image">
          <img src="${imgPath}" alt="${ing.name}">
        </div>
        <div class="card-body">
          <h4 class="ingredient-name">${ing.name}</h4>
          <p class="ingredient-cost">$${Number(ing.unit_cost || 0).toLocaleString()}</p>
          <p class="ingredient-state ${ing.state?.toLowerCase() || "inactivo"}">${ing.state || "Inactivo"}</p>
          <p><strong>Cantidad:</strong> ${ing.amount} ${ing.unit}</p>
          <p><strong>Mínimo:</strong> ${ing.minimum_quantity}</p>
          <p class="ingredient-desc">${ing.description || "Sin descripción"}</p>
          <p><strong>Proveedor:</strong> ${ing.supplier || "No especificado"}</p>
          <p><strong>Ubicación:</strong> ${ing.location || "Sin ubicación"}</p>
        </div>
        <div class="card-footer">
          <button class="btn btn-edit" onclick='editIngredient(${JSON.stringify(ing)})'>✏️</button>
          <button class="btn btn-delete" onclick="deleteIngredient(${ing.id})">🗑️</button>
        </div>
      `;
      grid.appendChild(card);
    });
  }

  // 🌟 Tarjeta de creación
  const createCard = document.createElement("div");
  createCard.className = "ingredient-card card create-card";
  createCard.id = "globalCreateCard";
  createCard.onclick = () => newIngredient();
  createCard.innerHTML = `
    <div class="card-body text-center">
      <span class="plus-icon">+</span>
      <p>Crear Ingrediente</p>
    </div>
  `;
  grid.appendChild(createCard);
}

// ===============================
// 🔹 FILTRAR POR CATEGORÍA
// ===============================
window.mostrarCategoria = async function (categoria) {
  const grid = document.getElementById("ingredientGrid");
  grid.innerHTML = "<p class='loading'>Filtrando ingredientes...</p>";

  try {
    const response = await fetch("../php/utilidades/get_storage.php");
    const data = await response.json();

    if (!data.success) throw new Error(data.error);
    let ingredientes = data.insumos;

    // 🔹 Si no es "Todos", filtrar
    if (categoria !== "Todos") {
      ingredientes = ingredientes.filter(ing => (ing.category || "Sin categoría") === categoria);
    }

    // 🔹 Renderizar filtrados
    renderStorage(ingredientes);

    // 🔹 Marcar activo
    document.querySelectorAll(".sidebar-item").forEach(item => {
      item.classList.toggle("active", item.textContent.trim() === categoria);
    });

    // 🔹 Cerrar sidebar
    document.getElementById("sidebarMenu").classList.remove("active");
    document.getElementById("sidebarOverlay")?.classList.remove("active");

  } catch (error) {
    grid.innerHTML = `<p class='error'>Error al filtrar: ${error.message}</p>`;
    Alerts.error("Error al filtrar: " + error.message);
  }
};

// ===============================
// 🔹 RECARGAR CATEGORÍAS
// ===============================
window.reloadCategories = async function () {
  const categoryList = document.getElementById("categoryList");

  try {
    const res = await fetch("../php/utilidades/get_storage.php");
    const data = await res.json();

    if (!data.success) throw new Error(data.error);

    categoryList.innerHTML = `<li class="sidebar-item active" onclick="mostrarCategoria('Todos')">Todos</li>`;

    data.categorias.forEach(cat => {
      const li = document.createElement("li");
      li.classList.add("sidebar-item");
      li.textContent = cat;
      li.onclick = () => mostrarCategoria(cat);
      categoryList.appendChild(li);
    });
  } catch (error) {
    Alerts.error("Error al recargar categorías: " + error.message);
  }
};

// ===============================
// 🔹 SIDEBAR TOGGLE
// ===============================
function toggleSidebar() {
  const sidebar = document.getElementById("sidebarMenu");
  let overlay = document.getElementById("sidebarOverlay");

  if (!overlay) {
    overlay = document.createElement("div");
    overlay.id = "sidebarOverlay";
    overlay.classList.add("sidebar-overlay");
    document.body.appendChild(overlay);
    overlay.addEventListener("click", toggleSidebar);
  }

  sidebar.classList.toggle("active");
  overlay.classList.toggle("active");
}

// ===============================
// 🔹 INICIALIZAR TODO
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  loadStorage();
  reloadCategories();
});
// ===============================
// 🔹 MODAL CONTROL Y CRUD
// ===============================

// Mostrar modal
function showModal(id) {
  document.getElementById(id).classList.remove("hidden");
  document.body.style.overflow = "hidden"; // Evita scroll al abrir
}

// Ocultar modal
function hideModal(id) {
  document.getElementById(id).classList.add("hidden");
  document.body.style.overflow = "";
  document.getElementById("ingredientForm").reset();
  document.getElementById("currentPhotoContainer").classList.add("hidden-img");
}

// Crear nuevo ingrediente
function newIngredient() {
  const form = document.getElementById("ingredientForm");
  document.getElementById("modalTitle").textContent = "Registrar Ingrediente";
  document.getElementById("action").value = "create";
  form.reset();
  document.getElementById("currentPhotoContainer").classList.add("hidden-img");
  showModal("formModal");
}

// Editar ingrediente existente
function editIngredient(ing) {
  const form = document.getElementById("ingredientForm");
  document.getElementById("modalTitle").textContent = "Editar Ingrediente";
  document.getElementById("action").value = "update";

  // Llenar campos
  form.ingredient_id.value = ing.id;
  form.name.value = ing.name || "";
  form.amount.value = ing.amount || "";
  form.minimum_quantity.value = ing.minimum_quantity || "";
  form.unit.value = ing.unit || "Unidad";
  form.unit_cost.value = ing.unit_cost || "";
  form.category.value = ing.category || "";
  form.batch.value = ing.batch || "";
  form.description.value = ing.description || "";
  form.location.value = ing.location || "";
  form.state.value = ing.state || "Activo";
  form.supplier.value = ing.supplier || "";
  form.fecha_ingreso.value = ing.fecha_ingreso || "";
  form.fecha_vencimiento.value = ing.fecha_vencimiento || "";

  // Mostrar foto actual si existe
  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  const currentPhoto = document.getElementById("currentPhoto");
  if (ing.photo && ing.photo.trim() !== "") {
    currentPhoto.src = ing.photo.startsWith("media/")
      ? "../" + ing.photo
      : "../media/" + ing.photo;
    currentPhotoContainer.classList.remove("hidden-img");
  } else {
    currentPhotoContainer.classList.add("hidden-img");
  }

  showModal("formModal");
}

// Guardar (crear/editar)
document.getElementById("ingredientForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  if (!validarFechas()) return;

  const form = e.target;
  const formData = new FormData(form);

  try {
    const response = await fetch("../php/inventario/ingredienteController.php", {
      method: "POST",
      body: formData,
    });
    const data = await response.json();

    if (data.success) {
      Alerts.success(data.message || "Ingrediente guardado correctamente.");
      hideModal("formModal");
      loadStorage();
      reloadCategories();
    } else {
      Alerts.warning(data.error || "No se pudo guardar el ingrediente.");
    }
  } catch (err) {
    Alerts.error("Error al guardar ingrediente: " + err.message);
  }
});

// Eliminar ingrediente
async function deleteIngredient(id) {
  const confirm = await Swal.fire({
    title: "¿Eliminar ingrediente?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  });

  if (!confirm.isConfirmed) return;

  try {
    const response = await fetch("../php/inventario/ingredienteController.php", {
      method: "POST",
      body: new URLSearchParams({
        id,
        action: "delete",
      }),
    });

    const data = await response.json();

    if (data.success) {
      Alerts.success("Ingrediente eliminado correctamente.");
      loadStorage();
      reloadCategories();
    } else {
      Alerts.warning(data.error || "No se pudo eliminar el ingrediente.");
    }
  } catch (err) {
    Alerts.error("Error al eliminar ingrediente: " + err.message);
  }
}

// ===============================
// 🔹 NUEVA CATEGORÍA
// ===============================
const categorySelect = document.getElementById("category");
const newCategoryInput = document.getElementById("newCategoryInput");

categorySelect.addEventListener("change", () => {
  if (categorySelect.value === "__new__") {
    newCategoryInput.classList.remove("hidden-input");
    newCategoryInput.required = true;
  } else {
    newCategoryInput.classList.add("hidden-input");
    newCategoryInput.required = false;
    newCategoryInput.value = "";
  }
});

// ===============================
// 🔹 PREVISUALIZAR FOTO
// ===============================
document.getElementById("photo").addEventListener("change", function (e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (ev) {
      const preview = document.getElementById("currentPhoto");
      const container = document.getElementById("currentPhotoContainer");
      preview.src = ev.target.result;
      container.classList.remove("hidden-img");
    };
    reader.readAsDataURL(file);
  }
});
