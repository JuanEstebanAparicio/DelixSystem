/* ================================
   acciones.js – CRUD y filtros
================================== */
// === Filtros globales ===
const filterState = document.getElementById("filterState");
const searchInput = document.getElementById("searchInput");
const toggleFilters = document.getElementById("toggleFilters");
const filtersContainer = document.querySelector(".filters");

if (filterState) filterState.addEventListener("change", filtrarIngredientes);
if (searchInput) searchInput.addEventListener("input", filtrarIngredientes);

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
      const imgPath = ing.photo?.trim() ? ing.photo : "../img/default.png";
      const ingEscaped = JSON.stringify(ing).replace(/'/g, "\\'");

      const card = document.createElement("div");
      card.className = "ingredient-card card";
      card.dataset.category = cat;

      card.innerHTML = `
        <div class="card-image">
          <img src="${imgPath}" alt="${escapeHtml(ing.name || 'Ingrediente')}">
        </div>
        <div class="card-body">
          <h4 class="ingredient-name">${escapeHtml(ing.name || '')}</h4>
          <p class="ingredient-cost">$${Number(ing.unit_cost || 0).toLocaleString()}</p>
          <p class="ingredient-state ${(ing.state || "inactivo").toLowerCase()}">${escapeHtml(ing.state || "Inactivo")}</p>
          <p><strong>Cantidad:</strong> ${escapeHtml(String(ing.amount || 0))} ${escapeHtml(ing.unit || '')}</p>
          <p><strong>Mínimo:</strong> ${escapeHtml(String(ing.minimum_quantity || '0'))}</p>
          <p class="ingredient-desc">${escapeHtml(ing.description || "Sin descripción")}</p>
          <p><strong>Proveedor:</strong> ${escapeHtml(ing.supplier || "No especificado")}</p>
          <p><strong>Ubicación:</strong> ${escapeHtml(ing.location || "Sin ubicación")}</p>
        </div>
        <div class="card-footer">
          <button class="btn btn-edit" onclick='editIngredient(${ingEscaped})'>✏️</button>
          <button class="btn btn-delete" onclick="deleteIngredient(${ing.id})">🗑️</button>
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
    if (!data.success) throw new Error(data.error);

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
    Alerts?.error?.("Error al filtrar: " + error.message);
  }
};

async function deleteIngredient(id) {
  const confirm = await Swal.fire({
    title: "¿Eliminar ingrediente?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  });
  if (!confirm.isConfirmed) return;

  const data = await safeFetchJson("../php/inventario/ingredienteController.php", {
    method: "POST",
    body: new URLSearchParams({ id, action: "delete" }),
  });

  if (data.status === "error") return Alerts?.warning(data.message);
  Alerts?.success(data.message || "Eliminado.");
  loadStorage();
  reloadCategories();
}

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

