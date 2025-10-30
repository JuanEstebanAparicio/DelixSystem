document.addEventListener("DOMContentLoaded", () => {
  const openBtns = [
    document.getElementById("openControlCenter"),
    document.getElementById("menuToggle")
  ].filter(Boolean); // Filtra los que existen

  const closeBtn = document.getElementById("closeControlCenter");
  const overlay = document.getElementById("controlCenter");
  const panel = document.getElementById("controlPanel");

  if (!overlay || !panel) return; // Si el panel no está en el DOM, no hacer nada

  const openPanel = () => {
    overlay.style.display = "block";
    setTimeout(() => overlay.classList.add("active"), 10);
  };

  const closePanel = () => {
    overlay.classList.remove("active");
    setTimeout(() => (overlay.style.display = "none"), 400);
  };

  // Vincula todos los botones de apertura (header, botón principal, etc.)
  openBtns.forEach((btn) => btn.addEventListener("click", openPanel));

  // Botón de cierre
  closeBtn?.addEventListener("click", closePanel);

  // Cierre al hacer clic fuera del panel
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) closePanel();
  });

  // Animación de salida con tecla ESC
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && overlay.classList.contains("active")) closePanel();
  });
});
