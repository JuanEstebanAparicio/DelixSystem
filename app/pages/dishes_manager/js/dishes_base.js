// ---------- BASE: CARGA DE DATOS, CATEGORÍAS Y RENDER ---------- //

document.addEventListener("DOMContentLoaded", () => {
  loadDishes();
  reloadCategories();
});

// Cargar platos
async function loadDishes() {
  const grid = document.getElementById("dishGrid");
  grid.innerHTML = "<p class='loading'>Cargando platos...</p>";
  try {
    const response = await fetch("../php/utilidades/get_dish.php");
    const data = await response.json();
    if (!data.success) throw new Error(data.error);
    renderDishes(data.platos);
  } catch (error) {
    grid.innerHTML = `<p class='error'>Error: ${error.message}</p>`;
  }
}

// Render de platos
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

// Render ingredientes
function renderIngredients(ingredients) {
  if (!ingredients || ingredients.length === 0) return "";
  const listItems = ingredients.map(ing => `<li>${ing.name} (${ing.quantity_used} ${ing.unit})</li>`).join("");
  return `<p><strong>Ingredientes:</strong></p><ul>${listItems}</ul>`;
}

// Filtro de categorías
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

// Recargar categorías
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

// Sidebar
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
