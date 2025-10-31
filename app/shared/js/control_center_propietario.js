document.addEventListener("DOMContentLoaded", () => {
  const openBtns = [
    document.getElementById("openControlCenter"),
    document.getElementById("menuToggle")
  ].filter(Boolean);

  const closeBtn = document.getElementById("closeControlCenter");
  const overlay = document.getElementById("controlCenter");

  if (!overlay) return;

  const openPanel = () => {
    overlay.classList.remove("translate-x-full");
    overlay.classList.add("translate-x-0");
  };

  const closePanel = () => {
    overlay.classList.remove("translate-x-0");
    overlay.classList.add("translate-x-full");
  };

  openBtns.forEach((btn) => btn.addEventListener("click", openPanel));
  closeBtn?.addEventListener("click", closePanel);

  overlay.addEventListener("click", (e) => {
    const isOutside = !e.target.closest("#controlCenter > div");
    if (isOutside) closePanel();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && overlay.classList.contains("translate-x-0")) {
      closePanel();
    }
  });
});