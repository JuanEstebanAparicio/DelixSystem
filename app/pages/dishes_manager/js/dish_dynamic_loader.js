document.addEventListener("DOMContentLoaded", () => {
  loadDishes();
});

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
  const names = ids.map(id => {
    const ing = ingredientes.find(i => i.id == id);
    return ing ? ing.name : "";
  }).filter(Boolean);
  return `
    <p><strong>Ingredientes:</strong></p>
    <ul>${names.map(n => `<li>${n}</li>`).join("")}</ul>
  `;
}

async function deleteDish(id) {
  if (!confirm("¿Eliminar plato?")) return;

  try {
    const res = await fetch("../php/dish_delet.php?id=" + id);
    if (!res.ok) throw new Error("Error al eliminar");
    loadDishes();
  } catch (err) {
    alert(err.message);
  }
}
