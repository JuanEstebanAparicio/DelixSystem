/* control_center_propietario.js
   Expose an init function so the control center can be re-initialized
   after partial DOM updates (AJAX polling). The script will also
   auto-run on DOMContentLoaded for the first load. */

function initControlCenterPropietario() {
  console.log("🚀 initControlCenterPropietario() ejecutado");

  const openBtns = [
    document.getElementById("openControlCenter"),
    document.getElementById("menuToggle")
  ].filter(Boolean);

  console.log("🔍 Botones para abrir encontrados:", openBtns);

  const closeBtn = document.getElementById("closeControlCenter");
  console.log("🔍 Botón cerrar encontrado:", closeBtn);

  const overlay = document.getElementById("controlCenter");
  console.log("🔍 Overlay encontrado:", overlay);

  if (!overlay) {
    console.error("❌ No se encontró el #controlCenter, saliendo...");
    return;
  }

  const openPanel = () => {
    console.log("✅ openPanel() ejecutado");
    overlay.classList.remove("translate-x-full");
    overlay.classList.add("translate-x-0");
  };

  const closePanel = () => {
    console.log("✅ closePanel() ejecutado");
    overlay.classList.remove("translate-x-0");
    overlay.classList.add("translate-x-full");
  };

  // Reemplazar botones de abrir para limpiar listeners
  openBtns.forEach((btn, idx) => {
    console.log(`♻ Clonando botón OPEN ${idx}`, btn);

    if (!btn.parentNode) {
      console.warn("⚠ Botón no tiene parentNode:", btn);
      return;
    }

    const fresh = btn.cloneNode(true);
    btn.parentNode.replaceChild(fresh, btn);

    fresh.addEventListener("click", () => {
      console.log(`🖱 Click en botón OPEN ${idx}`);
      openPanel();
    });
  });

  if (closeBtn) {
    console.log("♻ Clonando botón CLOSE", closeBtn);

    if (!closeBtn.parentNode) {
      console.warn("⚠ Botón close no tiene parentNode:", closeBtn);
    } else {
      const freshClose = closeBtn.cloneNode(true);
      closeBtn.parentNode.replaceChild(freshClose, closeBtn);

      freshClose.addEventListener("click", () => {
        console.log("🖱 Click en botón CLOSE");
        closePanel();
      });
    }
  } else {
    console.warn("⚠ No existe botón con id #closeControlCenter");
  }

  // Overlay click
  overlay.addEventListener("click", (e) => {
    const isOutside = !e.target.closest("#controlCenter > div");
    console.log("🖱 Click en overlay | Outside:", isOutside, "| Target:", e.target);

    if (isOutside) closePanel();
  });

  // ESC key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      console.log("⌨ Tecla presionada: ESC");
      if (overlay.classList.contains("translate-x-0")) {
        closePanel();
      }
    }
  });
}

// Auto-run on first load
if (document.readyState === 'loading') {
  console.log("📄 DOM cargando, esperando DOMContentLoaded...");
  document.addEventListener('DOMContentLoaded', () => {
    console.log("📄 DOMContentLoaded detectado");
    initControlCenterPropietario();
  });
} else {
  console.log("📄 DOM ya estaba listo");
  initControlCenterPropietario();
}

// Expose to global scope so pages can re-init after AJAX replacements
window.initControlCenterPropietario = initControlCenterPropietario;
console.log("🌍 initControlCenterPropietario expuesto en window");
