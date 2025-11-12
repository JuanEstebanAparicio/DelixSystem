// === Redirecciones ===
function goToDashboard() {
  window.location.href = "/DelixSystem/app/pages/dashboard_propietario/view/index.php";
}
function goToDishes() {
  window.location.href = "/DelixSystem/app/pages/dishes_manager/view/dishes_manager.php";
}
function goToInventory() {
  window.location.href = "/DelixSystem/app/pages/inventory/view/ingredient_manager.php";
}

// === Sidebar ===
const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("toggleSidebar");
const overlay = document.getElementById("overlay");

toggleBtn.addEventListener("click", () => {
  const open = sidebar.classList.toggle("open");
  overlay.classList.toggle("active", open);
  toggleBtn.classList.toggle("rotate", open);
});

overlay.addEventListener("click", () => {
  sidebar.classList.remove("open");
  overlay.classList.remove("active");
  toggleBtn.classList.remove("rotate");
});

// === Cargar combos desde PHP ===
async function loadCombos() {
  const grid = document.getElementById("comboGrid");
  grid.innerHTML = "<p class='loading'>Cargando combos...</p>";

  try {
    const response = await fetch("../php/combo_get.php");
    const data = await response.json();

    if (!data.success) throw new Error(data.error);
    renderCombos(data.combos);
  } catch (error) {
    grid.innerHTML = `<p class='error'>Error: ${error.message}</p>`;
  }
}

// === Renderizar combos ===
function renderCombos(combos) {
  const grid = document.getElementById("comboGrid");
  grid.innerHTML = "";

  combos.forEach(combo => {
    const card = document.createElement("div");
    card.className = "combo-card";
    const photo = combo.photo
      ? `<img src="${combo.photo}" alt="Combo" class="combo-photo">`
      : `<div class="no-photo">📦</div>`;

    card.innerHTML = `
      <div class="card-body">
        ${photo}
        <h4>Combo #${combo.id}</h4>
        <p><strong>Plato:</strong> ${combo.name_dish || "Sin nombre"}</p>
        <p>${combo.description || "Sin descripción"}</p>
        <p><strong>Tipo:</strong> ${combo.type || "Estatico"}</p>
        <p><strong>Estado:</strong> ${combo.state}</p>
        <p><strong>Creado:</strong> ${combo.created_at}</p>
      </div>
      <div class="card-footer">
        <button class="btn-edit" onclick='editCombo(${JSON.stringify(combo)})'>✏️</button>
        <button class="btn-delete" onclick="deleteCombo(${combo.id})">🗑️</button>
      </div>
    `;
    grid.appendChild(card);
  });

  // === Card para crear nuevo combo ===
  const createCard = document.createElement("div");
  createCard.className = "combo-card create-card";
  createCard.onclick = () => newCombo();
  createCard.innerHTML = `
    <div class="card-body text-center">
      <span class="plus-icon">+</span>
      <p>Crear Combo</p>
    </div>
  `;
  grid.appendChild(createCard);
}

// === Modal: crear / editar combo ===
const comboModal = document.getElementById("comboModal");
const comboForm = document.getElementById("comboForm");
const comboModalTitle = document.getElementById("comboModalTitle");

// Abrir modal nuevo
function newCombo() {
  comboModalTitle.textContent = "Nuevo Combo";
  comboForm.reset();
  comboForm.dataset.mode = "create";
  comboModal.classList.remove("hidden");
}

// Abrir modal editar
function editCombo(combo) {
  comboModalTitle.textContent = "Editar Combo";
  comboForm.dataset.mode = "edit";
  comboForm.dataset.id = combo.id;

  document.getElementById("id_dish").value = combo.id_dish;
  document.getElementById("description").value = combo.description || "";
  document.getElementById("state").value = combo.state || "Activo";
  document.getElementById("type").value = combo.type || "Estatico";
  document.getElementById("start_time").value = combo.start_time || "";
  document.getElementById("end_time").value = combo.end_time || "";
  document.getElementById("start_date").value = combo.start_date || "";
  document.getElementById("end_date").value = combo.end_date || "";

  handleTypeFields(combo.type);
  comboModal.classList.remove("hidden");
}

// Cerrar modal
function closeComboModal() {
  comboModal.classList.add("hidden");
}

// Mostrar campos según tipo
document.getElementById("type").addEventListener("change", (e) => {
  handleTypeFields(e.target.value);
});

function handleTypeFields(type) {
  document.getElementById("timeFields").classList.toggle("hidden", type !== "Por_Horas");
  document.getElementById("dateFields").classList.toggle("hidden", type !== "Temporal");
}

// === Guardar combo ===
comboForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(comboForm);
  const mode = comboForm.dataset.mode;
  const url = mode === "create" 
    ? "../php/combo_add.php" 
    : "../php/combo_edit.php";

  if (mode === "edit") {
    formData.append("id", comboForm.dataset.id);
  }

  try {
    const response = await fetch(url, { method: "POST", body: formData });
    const data = await response.json();
    if (!data.success) throw new Error(data.error);

    closeComboModal();
    loadCombos();
  } catch (err) {
    alert("Error: " + err.message);
  }
});

// === Eliminar combo ===
async function deleteCombo(id) {
  if (!confirm("¿Seguro que deseas eliminar este combo?")) return;

  try {
    const response = await fetch("../php/combo_delete.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id=" + encodeURIComponent(id),
    });
    const data = await response.json();

    if (!data.success) throw new Error(data.error);
    loadCombos();
  } catch (err) {
    alert("Error al eliminar: " + err.message);
  }
}

// === Cargar combos al iniciar ===
document.addEventListener("DOMContentLoaded", loadCombos);
