// Resalta el elemento activo del menú lateral
document.querySelectorAll('.sidebar ul li').forEach(item => {
  item.addEventListener('click', () => {
    document.querySelectorAll('.sidebar ul li').forEach(li => li.classList.remove('active'));
    item.classList.add('active');
  });
});
