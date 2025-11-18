/* control_center_propietario.js
   Expose an init function so the control center can be re-initialized
   after partial DOM updates (AJAX polling). The script will also
   auto-run on DOMContentLoaded for the first load. */

function initControlCenterPropietario() {
  const openBtns = [
    document.getElementById("openControlCenter"),
    document.getElementById("menuToggle")
  ].filter(Boolean);

  const closeBtn = document.getElementById("closeControlCenter");
  const overlay = document.getElementById("controlCenter");

  if (!overlay) return;

  // Remove previously attached listeners to avoid doubling
  // We'll use namespaced handlers attached to elements so we can remove them.
  // For simplicity, remove the element and re-query handlers by cloning.
  // (This avoids tracking all listener references.)

  // Helper to attach handlers cleanly
  const openPanel = () => {
    overlay.classList.remove("translate-x-full");
    overlay.classList.add("translate-x-0");
  };

  const closePanel = () => {
    overlay.classList.remove("translate-x-0");
    overlay.classList.add("translate-x-full");
  };

  // First remove any existing click listeners by cloning buttons (cheap, safe)
  openBtns.forEach((btn, idx) => {
    const fresh = btn.cloneNode(true);
    btn.parentNode.replaceChild(fresh, btn);
    fresh.addEventListener('click', openPanel);
  });

  if (closeBtn) {
    const freshClose = closeBtn.cloneNode(true);
    closeBtn.parentNode.replaceChild(freshClose, closeBtn);
    freshClose.addEventListener('click', closePanel);
  }

  // Overlay click
  overlay.addEventListener("click", (e) => {
    const isOutside = !e.target.closest("#controlCenter > div");
    if (isOutside) closePanel();
  });

  // ESC key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && overlay.classList.contains("translate-x-0")) {
      closePanel();
    }
  });
}

// Auto-run on first load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initControlCenterPropietario);
} else {
  initControlCenterPropietario();
}

// Expose to global scope so pages can re-init after AJAX replacements
window.initControlCenterPropietario = initControlCenterPropietario;