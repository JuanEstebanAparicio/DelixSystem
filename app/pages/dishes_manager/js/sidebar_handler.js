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
