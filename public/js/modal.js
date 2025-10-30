// DelixSystem/public/js/modal.js

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




// DelixSystem/public/js/employee_modal_switcher.js
// Script dedicado: asegura que cuando se abra el modal de login de empleado
// se cierre el modal de registro (codeModal). También hace la acción inversa.

document.addEventListener('DOMContentLoaded', () => {
  try {
    const loginModal = document.getElementById('employeeLoginModal');
    const codeModal = document.getElementById('codeModal');

    // helper: determinar si un modal está visible (considera estilos inline y computed)
    const isVisible = (el) => {
      if (!el) return false;
      // chequea display y visibilidad computada (por si usan flex/block/none o clases)
      const cs = window.getComputedStyle(el);
      return cs.display !== 'none' && cs.visibility !== 'hidden' && cs.opacity !== '0';
    };

    // helper para cerrar un modal de forma segura
    const closeModal = (el) => {
      if (!el) return;
      try {
        el.style.display = 'none';
        // remover clase que usan algunos frameworks
        el.classList.remove('show', 'open', 'is-active');
        // también disparar evento custom por si alguien lo escucha
        el.dispatchEvent(new CustomEvent('modal:closed', { bubbles: true }));
      } catch (err) {
        console.warn('No se pudo cerrar modal:', err);
      }
    };

    // helper para abrir un modal (solo para consistencia y focus)
    const openModal = (el) => {
      if (!el) return;
      try {
        el.style.display = 'flex';
        el.classList.add('show');
        const first = el.querySelector('input, button, textarea, select');
        if (first) first.focus();
        el.dispatchEvent(new CustomEvent('modal:opened', { bubbles: true }));
      } catch (err) {
        console.warn('No se pudo abrir modal:', err);
      }
    };

    // 1) Interceptar clics en elementos con data-modal-target que apunten a #employeeLoginModal
    document.querySelectorAll('[data-modal-target="#employeeLoginModal"], [data-modal-target=\'#employeeLoginModal\']')
      .forEach(btn => {
        btn.addEventListener('click', (e) => {
          // permitir comportamiento por defecto que otros scripts realicen (no e.preventDefault())
          // pero forzamos el cierre inmediato del codeModal para evitar solapamientos
          if (codeModal && isVisible(codeModal)) {
            closeModal(codeModal);
          }
          // si por alguna razón quieres abrir el login aquí, puedes descomentar:
          // openModal(loginModal);
        });
      });

    // 2) Interceptar el enlace "Entra desde aquí" si existe (id="switchToEmployeeLogin")
    const switchLink = document.getElementById('switchToEmployeeLogin');
    if (switchLink) {
      switchLink.addEventListener('click', (e) => {
        e.preventDefault(); // evitar navegación #
        if (codeModal && isVisible(codeModal)) closeModal(codeModal);
        if (loginModal && !isVisible(loginModal)) openModal(loginModal);
      });
    }

    // 3) MutationObserver: detectar cambios en atributos (style, class) y en subtree
    const observeModal = (targetEl, onVisible) => {
      if (!targetEl) return null;
      const mo = new MutationObserver((mutations) => {
        // usar setTimeout 0 para dejar que otros scripts apliquen su cambio primero
        setTimeout(() => {
          if (isVisible(targetEl)) {
            try { onVisible(); } catch (err) { console.error(err); }
          }
        }, 0);
      });

      mo.observe(targetEl, { attributes: true, attributeFilter: ['style', 'class'], subtree: false });
      return mo;
    };

    // Si se abre loginModal => cerrar codeModal
    if (loginModal && codeModal) {
      observeModal(loginModal, () => {
        if (isVisible(loginModal) && isVisible(codeModal)) {
          closeModal(codeModal);
        }
      });

      // (opcional / simetrico) Si se abre codeModal => cerrar loginModal
      observeModal(codeModal, () => {
        if (isVisible(codeModal) && isVisible(loginModal)) {
          closeModal(loginModal);
        }
      });
    }

    // 4) Fallback: si algún script muestra modal usando inline style después de un retraso,
    // comprobamos periódicamente durante unos segundos (solo en carga).
    let checks = 0;
    const maxChecks = 20; // ~2 segundos si interval 100ms
    const int = setInterval(() => {
      checks++;
      if (loginModal && codeModal && isVisible(loginModal) && isVisible(codeModal)) {
        closeModal(codeModal);
      }
      if (checks >= maxChecks) clearInterval(int);
    }, 100);

    // listo
    console.log('employee_modal_switcher cargado — vigilancia de employeeLoginModal/codeModal activa.');
  } catch (err) {
    console.error('Error en employee_modal_switcher:', err);
  }
});
