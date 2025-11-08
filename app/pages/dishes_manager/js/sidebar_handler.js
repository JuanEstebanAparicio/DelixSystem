// ===============================
// 🔹 TOGGLE SIDEBAR
// ===============================
function toggleSidebar() {
  const sidebar = document.getElementById('sidebarMenu');
  const overlay = document.getElementById('sidebarOverlay');
  sidebar.classList.toggle('active');
  overlay.classList.toggle('active');
}

// Cierra el sidebar al hacer clic fuera
document.addEventListener('click', (e) => {
  const sidebar = document.getElementById('sidebarMenu');
  const overlay = document.getElementById('sidebarOverlay');
  const hamburger = document.querySelector('.hamburger');
  if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
  }
});

// ===============================
// 🔹 FILTRAR PLATILLOS POR CATEGORÍA
// ===============================
function mostrarCategoria(cat, element) {
  const cards = document.querySelectorAll('.ingredient-card');

  document.querySelectorAll('.sidebar-item').forEach((item) => item.classList.remove('active'));
  if (element) element.classList.add('active');

  cards.forEach((card) => {
    if (cat === 'Todos' || card.dataset.category === cat || card.id === 'globalCreateCard') {
      card.style.display = 'flex';
    } else {
      card.style.display = 'none';
    }
  });
}

// ===============================
// 🔹 RECARGAR CATEGORÍAS (SIN DUPLICADOS)
// ===============================
async function reloadCategories() {
  try {
    const response = await fetch("../php/utilidades/reload_categories.php");
    let categorias = await response.json();

    // ✅ Eliminar duplicados
    categorias = [...new Set(categorias.map(c => c.category || c))];

    const list = document.getElementById("categoryList");
    list.innerHTML = "";

    const todosLi = document.createElement("li");
    todosLi.className = "sidebar-item active";
    todosLi.textContent = "Todos";
    todosLi.onclick = (e) => mostrarCategoria("Todos", e.target);
    list.appendChild(todosLi);

    categorias.forEach((cat) => {
      const li = document.createElement("li");
      li.className = "sidebar-item";
      li.textContent = cat;
      li.onclick = (e) => mostrarCategoria(cat, e.target);
      list.appendChild(li);
    });

    console.log("✅ Categorías actualizadas correctamente");
  } catch (err) {
    console.error("❌ Error al recargar categorías:", err);
  }
}

// ===============================
// 🔹 MOSTRAR CAMPO DE NUEVA CATEGORÍA EN FORMULARIO
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  const categorySelect = document.getElementById("category");
  const newCategoryInput = document.getElementById("newCategoryInput");

  if (!categorySelect || !newCategoryInput) return;

  categorySelect.addEventListener("change", () => {
    if (categorySelect.value === "__new__") {
      newCategoryInput.classList.remove("hidden");
      newCategoryInput.required = true;
      newCategoryInput.focus();
    } else {
      newCategoryInput.classList.add("hidden");
      newCategoryInput.required = false;
      newCategoryInput.value = "";
    }
  });
});

// ===============================
// 🔹 RECARGAR PLATILLOS (SIN DUPLICADOS)
// ===============================
async function reloadDishes() {
  try {
    const response = await fetch("../php/utilidades/get_dish.php");
    const data = await response.json();

    if (!data || !data.length) {
      console.warn("No se recibieron platillos.");
      return;
    }

    // ✅ Eliminar duplicados por nombre
    const seen = new Set();
    const dishes = data.filter(dish => {
      if (seen.has(dish.name_dish)) return false;
      seen.add(dish.name_dish);
      return true;
    });

    const container = document.getElementById("dishGrid");
    container.innerHTML = "";

    dishes.forEach((dish) => {
      const imgPath = dish.photo
        ? (dish.photo.startsWith("media/") ? "../" + dish.photo : "../media/" + dish.photo)
        : "../img/default.png";

      const card = document.createElement("div");
      card.className = "ingredient-card card";
      card.dataset.category = dish.category || "Sin categoría";

      const ingredientesHTML = dish.ingredients && dish.ingredients.length > 0
        ? dish.ingredients.map(ing => `<li>${ing.name} — ${ing.quantity_used} ${ing.unit}</li>`).join("")
        : "<li><em>Sin ingredientes</em></li>";

      card.innerHTML = `
        <div class="card-image">
          <img src="${imgPath}" alt="${dish.name_dish}">
        </div>
        <div class="card-body">
          <h4 class="ingredient-name">${dish.name_dish}</h4>
          <p class="ingredient-cost">$${dish.price}</p>
          <p class="ingredient-state">${dish.state}</p>
          <p class="ingredient-desc">${dish.description || "Sin descripción"}</p>
          <p><strong>Ingredientes:</strong></p>
          <ul>${ingredientesHTML}</ul>
        </div>
      `;

      container.appendChild(card);
    });

    console.log("✅ Platillos actualizados correctamente");
  } catch (err) {
    console.error("❌ Error al recargar platillos:", err);
  }
}

// ===============================
// 🔹 INICIO AUTOMÁTICO
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  reloadCategories();
  reloadDishes();
});
