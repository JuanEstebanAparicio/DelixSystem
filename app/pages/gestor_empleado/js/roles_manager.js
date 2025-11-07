// ============================================================
// 🧩 MÓDULO: GESTOR DE ROLES (roles_manager.js)
// ------------------------------------------------------------
// Basado al 100% en el bloque de roles dentro de empleados.js
// Mantiene:
//   - Carga dinámica de roles
//   - Modal #modalRoles
//   - Bloqueo lógico/visual ADMIN_LOCAL
//   - Alertas y animaciones originales
// ============================================================

document.addEventListener("click", async (e) => {
  const btn = e.target.closest(".asignar-rol");
  if (!btn) return;

  const empleadoId = btn.dataset.id;
  const modal = document.querySelector("#modalRoles");
  const rolesContainer = modal.querySelector("#rolesContainer");
  const inputEmpleadoId = modal.querySelector("#empleadoId");
  const guardarBtn = modal.querySelector("#guardarRoles");

  inputEmpleadoId.value = empleadoId;

  try {
    // 1️⃣ Obtener todos los roles disponibles
    const rolesRes = await fetch("../php/roles/RolesListController.php");
    const rolesData = await rolesRes.json();
    if (rolesData.status !== "success") throw new Error("Error al obtener roles");

    // 2️⃣ Obtener roles asignados al empleado
    const empRes = await fetch(`../php/roles/RolesByEmployeeController.php?id=${empleadoId}`);
    const empData = await empRes.json();

    const rolesAsignados = Array.isArray(empData.data)
      ? empData.data.map(r => parseInt(r.rol_id ?? r.id))
      : [];

    // 3️⃣ Renderizar checkboxes
    rolesContainer.innerHTML = "";
    rolesData.data.forEach((rol) => {
      const checked = rolesAsignados.includes(parseInt(rol.id)) ? "checked" : "";
      const div = document.createElement("div");

      div.classList.add("role-card");
      if (checked) div.classList.add("active");
      if (rol.nombre.toUpperCase() === "ADMIN_LOCAL") div.classList.add("admin-local");

      div.innerHTML = `
        <input type="checkbox" id="role_${rol.id}" value="${rol.id}" ${checked}>
        <div class="role-name">${rol.nombre}</div>
        <div class="role-desc">${rol.descripcion}</div>
      `;
      rolesContainer.appendChild(div);
    });

    // 🧩 Efecto visual de selección de roles (versión estable)
    // Evita duplicar listeners si el modal se abre más de una vez
    if (!rolesContainer.dataset.listenerAttached) {
      rolesContainer.addEventListener("click", (e) => {
        const card = e.target.closest(".role-card");
        if (!card) return;

        const checkbox = card.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        card.classList.toggle("active", checkbox.checked);
      });

      rolesContainer.dataset.listenerAttached = "true";
    }

    // 4️⃣ Mostrar modal
    modal.classList.add("show");
    modal.style.display = "flex";

    // 5️⃣ Guardar asignación
    guardarBtn.onclick = async (ev) => {
      ev.preventDefault();

      const seleccionados = [...rolesContainer.querySelectorAll("input[type='checkbox']:checked")].map(chk => chk.value);
      try {
        const res = await fetch("../php/roles/RolesAssignController.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
            empleado_id: empleadoId,
            roles: JSON.stringify(seleccionados),
          }),
        });

        const data = await res.json();

        if (data.status === "success") {
          Alerts.success("✅ Roles actualizados correctamente");

          // ✨ Efecto visual de confirmación
          rolesContainer.querySelectorAll(".role-card.active").forEach(card => {
            card.classList.add("pulse-success");
            setTimeout(() => card.classList.remove("pulse-success"), 800);
          });

          // 🔁 Volver a marcar según nueva asignación
          setTimeout(async () => {
            const empReload = await fetch(`../php/roles/RolesByEmployeeController.php?id=${empleadoId}`);
            const empNewData = await empReload.json();
            const nuevosRoles = Array.isArray(empNewData.data)
              ? empNewData.data.map(r => parseInt(r.rol_id ?? r.id))
              : [];

            rolesContainer.querySelectorAll("input[type='checkbox']").forEach(chk => {
              chk.checked = nuevosRoles.includes(parseInt(chk.value));
            });
          }, 600);
        } else {
          Alerts.error(data.message || "Error al asignar roles");
        }
      } catch (err) {
        console.error("🚨 Error al asignar roles:", err);
        Alerts.error("Error al asignar roles (ver consola)");
      }
    };
  } catch (err) {
    console.error("Error al cargar roles:", err);
    Alerts.error("Error al cargar roles disponibles (ver consola)");
  }
});

// ============================================================
// 🧩 BLOQUE: ADMIN_LOCAL (bloqueo visual/lógico de roles)
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  const modalRoles = document.getElementById("modalRoles");
  const rolesContainer = document.getElementById("rolesContainer");
  if (!rolesContainer) return;

  const ADMIN_ROLE_NAME = "ADMIN_LOCAL";

  function getAdminCard() {
    return [...rolesContainer.querySelectorAll(".role-card")]
      .find(card => card.querySelector(".role-name")?.textContent.trim().toUpperCase() === ADMIN_ROLE_NAME);
  }

  function getAdminCheckbox() {
    const adminCard = getAdminCard();
    return adminCard ? adminCard.querySelector('input[type="checkbox"]') : null;
  }

  function toggleOtherRoles(disabled, except) {
    const allCards = rolesContainer.querySelectorAll(".role-card");
    allCards.forEach(card => {
      const chk = card.querySelector('input[type="checkbox"]');
      if (chk !== except) {
        chk.disabled = disabled;
        if (disabled) {
          card.classList.add("locked-role");
          card.classList.remove("active");
          chk.checked = false;
        } else {
          card.classList.remove("locked-role");
        }
      }
    });
  }

  function handleAdminToggle() {
    const adminChk = getAdminCheckbox();
    const adminCard = getAdminCard();
    if (!adminChk || !adminCard) return;

    if (adminChk.checked) {
      adminCard.classList.add("admin-active");
      toggleOtherRoles(true, adminChk);
    } else {
      adminCard.classList.remove("admin-active");
      toggleOtherRoles(false);
    }
  }

  // 📦 Listener de cambios reales
  if (!rolesContainer.dataset.adminListenerAttached) {
    rolesContainer.addEventListener("change", (e) => {
      const adminChk = getAdminCheckbox();
      if (!adminChk) return;
      const target = e.target;

      if (target === adminChk) {
        handleAdminToggle();
      } else if (adminChk.checked && target.checked) {
        adminChk.checked = false;
        handleAdminToggle();
      }
    });

    rolesContainer.addEventListener("click", (e) => {
      const card = e.target.closest(".role-card");
      if (!card) return;
      setTimeout(() => handleAdminToggle(), 50);
    });

    rolesContainer.dataset.adminListenerAttached = "true";
  }

  const observer = new MutationObserver(() => handleAdminToggle());
  observer.observe(rolesContainer, { childList: true, subtree: true });
});

// ============================================================
// 🔧 Helper global (igual que empleados.js)
// ============================================================
function closeModal(selectorOrEl) {
  const el = (typeof selectorOrEl === "string") ? document.querySelector(selectorOrEl) : selectorOrEl;
  if (!el) return;
  el.classList.remove("show");
  el.style.display = "none";
  console.log("[closeModal - roles_manager] closed", el.id || el);
}
