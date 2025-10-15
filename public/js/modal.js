/* B) — Reemplaza el archivo public/assets/js/modal.js con esto */
/* modal.js — manejo robusto de modales */
document.addEventListener("DOMContentLoaded", () => {
  try {
    const openButtons = document.querySelectorAll("[data-modal-target]");
    const modals = document.querySelectorAll(".modal");
    const closeButtons = document.querySelectorAll(".modal .close");

    // Si no hay botones ni modales, salir (evita errores en páginas sin modales)
    if ((!openButtons || openButtons.length === 0) && (!modals || modals.length === 0)) {
      return;
    }

    // Abrir modal al hacer clic en botones con data-modal-target
    openButtons.forEach(btn => {
      // defensivo: comprobar dataset y selector
      if (!btn.dataset || !btn.dataset.modalTarget) return;
      btn.addEventListener("click", () => {
        const targetSelector = btn.dataset.modalTarget;
        if (!targetSelector) return;
        const modal = document.querySelector(targetSelector);
        if (modal) {
          // mostrar modal de forma consistente
          modal.style.display = "flex";
          // opcional: focusear primer input si existe
          const firstInput = modal.querySelector("input, button, textarea");
          if (firstInput) firstInput.focus();
        } else {
          // en desarrollo, no romper; opcional: console.warn
          console.warn("Modal no encontrado para selector:", targetSelector);
        }
      });
    });

    // Cerrar modal al hacer clic en .close dentro del modal
    closeButtons.forEach(closeBtn => {
      closeBtn.addEventListener("click", (e) => {
        const modal = e.target.closest(".modal");
        if (modal) modal.style.display = "none";
      });
    });

    // Cerrar modal al hacer clic fuera del contenido
    modals.forEach(modal => {
      modal.addEventListener("click", (e) => {
        if (e.target === modal) modal.style.display = "none";
      });
    });

    // Cerrar con ESC
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        modals.forEach(m => {
          if (m.style.display === "flex") m.style.display = "none";
        });
      }
    });
  } catch (err) {
    // Si algo falla, evitar romper toda la app
    console.error("modal.js error:", err);
  }
});


// modal-handler.js
document.addEventListener("DOMContentLoaded", () => {
  // Abrir cualquier modal al hacer clic en un botón con data-modal-target
  document.querySelectorAll("[data-modal-target]").forEach(btn => {
    btn.addEventListener("click", () => {
      const modal = document.querySelector(btn.dataset.modalTarget);
      if (modal) modal.style.display = "flex"; // usar flex para respetar tu CSS existente
    });
  });

  // Cerrar modal al hacer clic en el botón .close
  document.querySelectorAll(".modal .close").forEach(closeBtn => {
    closeBtn.addEventListener("click", e => {
      const modal = e.target.closest(".modal");
      if (modal) modal.style.display = "none";
    });
  });

  // Cerrar modal al hacer clic fuera del contenido
  document.querySelectorAll(".modal").forEach(modal => {
    modal.addEventListener("click", e => {
      if (e.target === modal) modal.style.display = "none";
    });
  });
});
