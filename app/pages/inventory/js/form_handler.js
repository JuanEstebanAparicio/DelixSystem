function validarFechas() {
  const ingresoEl = document.getElementById("fecha_ingreso");
  const vencimientoEl = document.getElementById("fecha_vencimiento");
  if (!ingresoEl || !vencimientoEl) return true;

  const ingreso = ingresoEl.value;
  const vencimiento = vencimientoEl.value;

  if (ingreso && vencimiento && new Date(vencimiento) < new Date(ingreso)) {
    if (typeof Alerts !== "undefined" && Alerts.warning) {
      Alerts.warning("La fecha de vencimiento no puede ser anterior a la de ingreso.");
    } else {
      alert("La fecha de vencimiento no puede ser anterior a la de ingreso.");
    }
    return false;
  }
  return true;
}

async function loadStorage() {
  const grid = document.getElementById("ingredientGrid");
  if (!grid) return;
  grid.innerHTML = "<p class='loading'>Cargando ingredientes...</p>";

  try {
    const response = await fetch("../php/utilidades/get_storage.php");
    const data = await response.json();
    if (!data.success) throw new Error(data.error || "Respuesta inválida del servidor.");
    renderStorage(data.insumos || []);
  } catch (error) {
    grid.innerHTML = `<p class='error'>Error al cargar ingredientes: ${error.message}</p>`;
    if (typeof Alerts !== "undefined" && Alerts.error) {
      Alerts.error("Error al cargar ingredientes: " + error.message);
    } else {
      console.error(error);
    }
  }
}

function renderStorage(ingredientes) {
  const grid = document.getElementById("ingredientGrid");
  if (!grid) return;
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
        ? ing.photo
        : "../img/default.png";

      const card = document.createElement("div");
      card.className = "ingredient-card card";
      card.dataset.category = cat;

      const ingEscaped = JSON.stringify(ing).replace(/'/g, "\\'");

      card.innerHTML = `
        <div class="card-image">
          <img src="${imgPath}" alt="${escapeHtml(ing.name || 'Ingrediente')}">
        </div>
        <div class="card-body">
          <h4 class="ingredient-name">${escapeHtml(ing.name || '')}</h4>
          <p class="ingredient-cost">$${Number(ing.unit_cost || 0).toLocaleString()}</p>
          <p class="ingredient-state ${((ing.state||"inactivo").toLowerCase())}">${escapeHtml(ing.state || "Inactivo")}</p>
          <p><strong>Cantidad:</strong> ${escapeHtml(String(ing.amount || 0))} ${escapeHtml(ing.unit || '')}</p>
          <p><strong>Mínimo:</strong> ${escapeHtml(String(ing.minimum_quantity || '0'))}</p>
          <p class="ingredient-desc">${escapeHtml(ing.description || "Sin descripción")}</p>
          <p><strong>Proveedor:</strong> ${escapeHtml(ing.supplier || "No especificado")}</p>
          <p><strong>Ubicación:</strong> ${escapeHtml(ing.location || "Sin ubicación")}</p>
        </div>
        <div class="card-footer">
          <button class="btn btn-edit" onclick='editIngredient(${ingEscaped})'>✏️</button>
          <button class="btn btn-delete" onclick="deleteIngredient(${Number(ing.id)})">🗑️</button>
        </div>
      `;
      grid.appendChild(card);
    });
  }

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

window.mostrarCategoria = async function (categoria) {
  const grid = document.getElementById("ingredientGrid");
  if (!grid) return;
  grid.innerHTML = "<p class='loading'>Filtrando ingredientes...</p>";

  try {
    const response = await fetch("../php/utilidades/get_storage.php");
    const data = await response.json();
    if (!data.success) throw new Error(data.error || "Respuesta inválida del servidor.");
    let ingredientes = data.insumos || [];

    if (categoria !== "Todos") {
      ingredientes = ingredientes.filter(ing => (ing.category || "Sin categoría") === categoria);
    }

    renderStorage(ingredientes);

    document.querySelectorAll(".sidebar-item").forEach(item => {
      item.classList.toggle("active", item.textContent.trim() === categoria);
    });

    document.getElementById("sidebarMenu")?.classList.remove("active");
    document.getElementById("sidebarOverlay")?.classList.remove("active");
    filtrarIngredientes();

  } catch (error) {
    grid.innerHTML = `<p class='error'>Error al filtrar: ${error.message}</p>`;
    if (typeof Alerts !== "undefined" && Alerts.error) 
      Alerts.error("Error al filtrar: " + error.message);
  }
};


window.reloadCategories = async function () {
  const categoryList = document.getElementById("categoryList");
  if (!categoryList) return;

  try {
    const res = await fetch("../php/utilidades/get_storage.php");
    const data = await res.json();
    if (!data.success) throw new Error(data.error || "Respuesta inválida");
    const categorias = data.categorias || [];
    categoryList.innerHTML = `<li class="sidebar-item active" onclick="mostrarCategoria('Todos')">Todos</li>`;
    categorias.forEach(cat => {
      const li = document.createElement("li");
      li.classList.add("sidebar-item");
      li.textContent = cat;
      li.onclick = () => mostrarCategoria(cat);
      categoryList.appendChild(li);
    });
  } catch (error) {
    if (typeof Alerts !== "undefined" && Alerts.error) Alerts.error("Error al recargar categorías: " + error.message);
    console.error(error);
  }
};

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

document.addEventListener("DOMContentLoaded", () => {
  const fechaIngreso = document.getElementById("fecha_ingreso");
  const hoy = new Date().toISOString().split("T")[0];
  if (fechaIngreso) {
    if (!fechaIngreso.value) fechaIngreso.value = hoy;
    fechaIngreso.readOnly = true;
  }

  loadStorage();
  reloadCategories();

  const form = document.getElementById("ingredientForm");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!validarFechas()) return;

      const formData = new FormData(form);
      try {
        const resp = await fetch("../php/inventario/ingredienteController.php", {
          method: "POST",
          body: formData
        });
        const data = await resp.json();
        if (data.success) {
          if (typeof Alerts !== "undefined" && Alerts.success) Alerts.success(data.message || "Ingrediente guardado correctamente.");
          hideModal("formModal");
          loadStorage();
          reloadCategories();
        } else {
          if (typeof Alerts !== "undefined" && Alerts.warning) Alerts.warning(data.error || "No se pudo guardar el ingrediente.");
        }
      } catch (err) {
        if (typeof Alerts !== "undefined" && Alerts.error) Alerts.error("Error al guardar ingrediente: " + err.message);
        console.error(err);
      }
    });
  }

  const photoInput = document.getElementById("photo");
  if (photoInput) {
    photoInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (ev) {
          const preview = document.getElementById("currentPhoto");
          const container = document.getElementById("currentPhotoContainer");
          if (preview) preview.src = ev.target.result;
          if (container) container.classList.remove("hidden-img");
        };
        reader.readAsDataURL(file);
      }
    });
  }

  const categorySelect = document.getElementById("category");
  const newCategoryInput = document.getElementById("newCategoryInput");
  if (categorySelect && newCategoryInput) {
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
  }
});

function escapeHtml(str) {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function showModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove("hidden");
  document.body.style.overflow = "hidden";
}

function hideModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add("hidden");
  document.body.style.overflow = "";
  const form = document.getElementById("ingredientForm");
  if (form) form.reset();
  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden-img");
  const fechaIngreso = document.getElementById("fecha_ingreso");
  if (fechaIngreso) {
    fechaIngreso.value = new Date().toISOString().split("T")[0];
    fechaIngreso.readOnly = true;
  }
}

function newIngredient() {
  const form = document.getElementById("ingredientForm");
  if (!form) return;
  document.getElementById("modalTitle").textContent = "Registrar Ingrediente";
  const actionEl = document.getElementById("action");
  if (actionEl) actionEl.value = "create";
  form.reset();
  const fechaIngreso = document.getElementById("fecha_ingreso");
  if (fechaIngreso) {
    fechaIngreso.value = new Date().toISOString().split("T")[0];
    fechaIngreso.readOnly = true;
  }
  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden-img");
  showModal("formModal");
}

function editIngredient(ing) {
  const form = document.getElementById("ingredientForm");
  if (!form) return;

  document.getElementById("modalTitle").textContent = "Editar Ingrediente";
  const actionEl = document.getElementById("action");
  if (actionEl) actionEl.value = "update";

  form.ingredient_id.value = ing.id ?? "";
  form.name.value = ing.name ?? "";
  form.amount.value = ing.amount ?? "";
  form.minimum_quantity.value = ing.minimum_quantity ?? "";
  form.unit.value = ing.unit ?? "Unidad";
  form.unit_cost.value = ing.unit_cost ?? "";
  form.category.value = ing.category ?? "";
  form.batch.value = ing.batch ?? "";
  form.description.value = ing.description ?? "";
  form.location.value = ing.location ?? "";
  form.state.value = ing.state ?? "Activo";
  form.supplier.value = ing.supplier ?? "";

  const fechaIngreso = document.getElementById("fecha_ingreso");
  if (fechaIngreso) {
    if (ing.entrance_date) {
      fechaIngreso.value = String(ing.entrance_date).split("T")[0];
    } else if (ing.fecha_ingreso) {
      fechaIngreso.value = String(ing.fecha_ingreso).split("T")[0];
    } else {
      fechaIngreso.value = new Date().toISOString().split("T")[0];
    }
    fechaIngreso.readOnly = true;
  }

  const fechaVenc = document.getElementById("fecha_vencimiento");
  if (fechaVenc) {
    if (ing.expiration_date) {
      fechaVenc.value = String(ing.expiration_date).split("T")[0];
    } else if (ing.fecha_vencimiento) {
      fechaVenc.value = String(ing.fecha_vencimiento).split("T")[0];
    } else {
      fechaVenc.value = "";
    }
  }

  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  const currentPhoto = document.getElementById("currentPhoto");
  if (ing.photo && ing.photo.trim() !== "") {
    if (currentPhoto) currentPhoto.src = ing.photo;
    if (currentPhotoContainer) currentPhotoContainer.classList.remove("hidden-img");
  } else {
    if (currentPhoto) currentPhoto.src = "../img/default.png";
    if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden-img");
  }

  showModal("formModal");
}

async function deleteIngredient(id) {
  let confirmed = false;
  if (typeof Swal !== "undefined") {
    const result = await Swal.fire({
      title: "¿Eliminar ingrediente?",
      text: "Esta acción no se puede deshacer.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
    });
    confirmed = result.isConfirmed;
  } else {
    confirmed = confirm("¿Eliminar ingrediente? Esta acción no se puede deshacer.");
  }
  if (!confirmed) return;

  try {
    const resp = await fetch("../php/inventario/ingredienteController.php", {
      method: "POST",
      body: new URLSearchParams({ id: id, action: "delete" })
    });
    const data = await resp.json();
    if (data.success) {
      if (typeof Alerts !== "undefined" && Alerts.success) Alerts.success(data.message || "Ingrediente eliminado correctamente.");
      loadStorage();
      reloadCategories();
    } else {
      if (typeof Alerts !== "undefined" && Alerts.warning) Alerts.warning(data.error || "No se pudo eliminar el ingrediente.");
    }
  } catch (err) {
    if (typeof Alerts !== "undefined" && Alerts.error) Alerts.error("Error al eliminar ingrediente: " + err.message);
    console.error(err);
  }
}
const filterState = document.getElementById("filterState");
const searchInput = document.getElementById("searchInput");
const toggleFilters = document.getElementById("toggleFilters");
const filtersContainer = document.querySelector(".filters");

filterState.addEventListener("change", filtrarIngredientes);
searchInput.addEventListener("input", filtrarIngredientes);

function filtrarIngredientes() {
  const stateValue = filterState.value;
  const searchValue = searchInput.value.toLowerCase();
  
  const cards = document.querySelectorAll(".ingredient-card.card:not(.create-card)");

  cards.forEach(card => {
    const state = card.querySelector(".ingredient-state")?.textContent.trim() || "";
    const name = card.querySelector(".ingredient-name")?.textContent.toLowerCase() || "";

    const matchesState = stateValue === "Todos" || state.toLowerCase() === stateValue.toLowerCase();
    const matchesSearch = name.includes(searchValue);

    card.style.display = matchesState && matchesSearch ? "block" : "none";
  });
}

toggleFilters.addEventListener("click", () => {
  filtersContainer.classList.toggle("hidden-filters");
  toggleFilters.textContent = filtersContainer.classList.contains("hidden-filters")
    ? "Mostrar filtros"
    : "Ocultar filtros";
});
