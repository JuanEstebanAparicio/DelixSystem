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

// === Cargar combos ===
async function loadCombos() {
  const grid = document.getElementById("comboGrid");
  grid.innerHTML = "<p class='loading'>Cargando combos...</p>";

  try {
    const response = await fetch("../php/utilidades/combo_get.php");
    const data = await response.json();

    if (!data.success) throw new Error(data.error);
    renderCombos(data.combos);
  } catch (error) {
    grid.innerHTML = `<p class='error'>Error: ${error.message}</p>`;
  }
}

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
      </div>
      <div class="card-footer">
        <button class="btn-edit" onclick='editCombo(${JSON.stringify(combo)})'>✏️</button>
        <button class="btn-delete" onclick="deleteCombo(${combo.id})">🗑️</button>
      </div>
    `;
    grid.appendChild(card);
  });

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

document.addEventListener("DOMContentLoaded", loadCombos);
