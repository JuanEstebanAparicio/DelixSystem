document.addEventListener("DOMContentLoaded", () => {
  const userBtn = document.getElementById("userMenuBtn");
  const userMenu = document.getElementById("userMenu");

  if (userBtn && userMenu) {
    userBtn.addEventListener("click", () => userMenu.classList.toggle("hidden"));
    window.addEventListener("click", (e) => {
      if (!userBtn.contains(e.target) && !userMenu.contains(e.target)) {
        userMenu.classList.add("hidden");
      }
    });
  }

  const menuToggle = document.getElementById("menuToggle");
  if (menuToggle) {
    menuToggle.addEventListener("click", () => {
      alert("Centro de Control próximamente: cambio entre gestores.");
    });
  }

  const controlCenterBtn = document.getElementById("controlCenterBtn");
  if (controlCenterBtn) {
    controlCenterBtn.addEventListener("click", () => {
      alert("Centro de Control — cambiar entre módulos próximamente.");
    });
  }
});
