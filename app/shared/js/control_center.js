document.addEventListener("DOMContentLoaded", () => {
  const openBtn = document.getElementById("openControlCenter");
  const closeBtn = document.getElementById("closeControlCenter");
  const overlay = document.getElementById("controlCenter");

  const openPanel = () => {
    overlay.classList.add("active");
  };

  const closePanel = () => {
    overlay.classList.remove("active");
    setTimeout(() => (overlay.style.display = "none"), 400);
  };

  openBtn?.addEventListener("click", () => {
    overlay.style.display = "block";
    setTimeout(openPanel, 10);
  });

  closeBtn?.addEventListener("click", closePanel);

  overlay?.addEventListener("click", (e) => {
    if (e.target === overlay) closePanel();
  });
});
