function toggleSidebar() {
  document.getElementById('sidebarMenu').classList.toggle('hidden');
}

function mostrarCategoria(cat) {
  const cards = document.querySelectorAll('.ingredient-card');
  if (cat === 'Todos') {
    cards.forEach(card => card.style.display = 'flex');
  } else {
    cards.forEach(card => {
      if (card.dataset.category === cat || card.id === 'globalCreateCard') {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }
}

document.addEventListener('click', function(e) {
  const sidebar = document.getElementById('sidebarMenu');
  const hamburger = document.querySelector('.hamburger');
  if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
    sidebar.classList.add('hidden');
  }
});

async function reloadCategories() {
  try {
    const response = await fetch("../php/reload_categories.php");
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

    console.log("Categorías actualizadas correctamente");

  } catch (err) {
    console.error("Error al recargar categorías:", err);
  }
}
