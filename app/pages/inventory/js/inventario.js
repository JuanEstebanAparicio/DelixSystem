// ===============================
//   🔹 MANEJO DE SIDEBAR
// ===============================
function toggleSidebar() {
  const sidebar = document.getElementById('sidebarMenu');
  let overlay = document.getElementById('sidebarOverlay');
  const mainContent = document.querySelector('.main-content');

  if (!overlay) {
    overlay = document.createElement('div');
    overlay.id = 'sidebarOverlay';
    overlay.classList.add('sidebar-overlay');
    document.body.appendChild(overlay);
    overlay.addEventListener('click', toggleSidebar);
  }

  sidebar.classList.toggle('active');
  overlay.classList.toggle('active');
  mainContent.classList.toggle('sidebar-open');
}

document.addEventListener('click', function (e) {
  const sidebar = document.getElementById('sidebarMenu');
  const hamburger = document.querySelector('.hamburger');
  const overlay = document.getElementById('sidebarOverlay');
  const mainContent = document.querySelector('.main-content');

  if (
    sidebar.classList.contains('active') &&
    !sidebar.contains(e.target) &&
    !hamburger.contains(e.target)
  ) {
    sidebar.classList.remove('active');
    overlay?.classList.remove('active');
    mainContent.classList.remove('sidebar-open');
  }
});

function mostrarCategoria(cat) {
  const cards = document.querySelectorAll('.ingredient-card');

  cards.forEach(card => {
    card.classList.remove('visible');
    setTimeout(() => {
      if (cat === 'Todos' || card.dataset.category === cat || card.id === 'globalCreateCard') {
        card.style.display = 'flex';
        setTimeout(() => card.classList.add('visible'), 50);
      } else {
        card.style.display = 'none';
      }
    }, 150);
  });
}

// ===============================
//   🔹 RECARGAR CATEGORÍAS
// ===============================
async function reloadCategories() {
  try {
    const response = await fetch("../php/utilidades/reload_storage.php");
    const categorias = await response.json();

    const list = document.getElementById("categoryList");
    list.innerHTML = `<li class="sidebar-item" onclick="mostrarCategoria('Todos')">Todos</li>`;

    categorias.forEach(cat => {
      const li = document.createElement("li");
      li.className = "sidebar-item";
      li.textContent = cat;
      li.onclick = () => mostrarCategoria(cat);
      list.appendChild(li);
    });

    console.log("✅ Categorías recargadas correctamente");
  } catch (err) {
    console.error("❌ Error al recargar categorías:", err);
  }
}

// ===============================
//   🔹 MOSTRAR / OCULTAR NUEVA CATEGORÍA EN FORM
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
