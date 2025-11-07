// ============================================================
// 🧩 Módulo: Roles Manager (roles_manager.js)
// ------------------------------------------------------------
// Controla:
//  - Carga dinámica de roles
//  - Modal de asignación
//  - Selección visual tipo “tarjeta”
//  - Lógica exclusiva para ADMIN_LOCAL
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  console.log("[roles_manager.js] inicializado ✅");

  const rolesContainer = document.getElementById("rolesContainer");
  const modalRoles = document.getElementById("modalRoles");
  const formRoles = document.getElementById("formRoles");
  const guardarBtn = document.getElementById("guardarRoles");
  const API_ROLES = "../php/roles/RoleController.php";
  const API_ASIGNAR = "../php/employee/EmpleadoController.php";

  if (!rolesContainer || !modalRoles) {
    console.warn("[roles_manager.js] No se encontró el modal o el contenedor de roles.");
    return;
  }

  // ============================================================
  // 🟦 1. CARGAR ROLES
  // ============================================================
  const cargarRoles = async (empleadoId) => {
    try {
      const res = await fetch(`${API_ROLES}?action=list`);
      const data = await res.json();

      if (data.status !== "success") throw new Error("No se pudieron cargar los roles");
      const roles = data.data;

      rolesContainer.innerHTML = "";
      roles.forEach((rol) => {
        const card = document.createElement("div");
        card.className = "role-card";
        card.innerHTML = `
          <input type="checkbox" id="rol_${rol.id}" value="${rol.id}">
          <div class="role-info">
            <h4>${rol.nombre}</h4>
            <p>${rol.descripcion || "Sin descripción"}</p>
          </div>
        `;
        rolesContainer.appendChild(card);
      });

      // Marcar roles actuales del empleado
      await marcarRolesAsignados(empleadoId);
    } catch (err) {
      console.error("[cargarRoles] Error:", err);
      Alerts.error("No se pudieron cargar los roles disponibles");
    }
  };

  // ============================================================
  // 🟩 2. MARCAR ROLES EXISTENTES DEL EMPLEADO
  // ============================================================
  const marcarRolesAsignados = async (empleadoId) => {
    try {
      const res = await fetch(`${API_ASIGNAR}?action=getRoles&id=${empleadoId}`);
      const data = await res.json();

      if (data.status !== "success") return;
      const rolesEmpleado = data.data.map(r => r.role_id);

      rolesContainer.querySelectorAll("input[type='checkbox']").forEach(chk => {
        if (rolesEmpleado.includes(parseInt(chk.value))) {
          chk.checked = true;
          chk.closest(".role-card").classList.add("active");
        }
      });

      aplicarLogicaAdmin();
    } catch (err) {
      console.error("[marcarRolesAsignados] error:", err);
    }
  };

  // ============================================================
  // 🟨 3. EFECTO VISUAL: Selección por tarjetas
  // ============================================================
  rolesContainer.addEventListener("click", (e) => {
    const card = e.target.closest(".role-card");
    if (!card) return;

    const checkbox = card.querySelector("input[type='checkbox']");
    checkbox.checked = !checkbox.checked;
    card.classList.toggle("active", checkbox.checked);

    aplicarLogicaAdmin();
  });

  // ============================================================
  // 🟥 4. LÓGICA ADMIN_LOCAL (bloqueo visual y lógico)
  // ============================================================
  const aplicarLogicaAdmin = () => {
    const adminChk = [...rolesContainer.querySelectorAll("input[type='checkbox']")]
      .find(chk => chk.closest(".role-card").querySelector("h4").textContent.trim().toUpperCase() === "ADMIN_LOCAL");

    if (!adminChk) return;

    const isAdmin = adminChk.checked;

    rolesContainer.querySelectorAll("input[type='checkbox']").forEach(chk => {
      const card = chk.closest(".role-card");
      if (chk !== adminChk) {
        chk.disabled = isAdmin;
        card.style.opacity = isAdmin ? "0.5" : "1";
        card.style.pointerEvents = isAdmin ? "none" : "auto";
      } else {
        card.style.opacity = "1";
        card.style.pointerEvents = "auto";
      }
    });
  };

  // ============================================================
  // 🟧 5. ABRIR MODAL DE ROLES
  // ============================================================
  document.addEventListener("click", async (e) => {
    const btn = e.target.closest(".asignar-rol");
    if (!btn) return;

    const empleadoId = btn.dataset.id;
    formRoles.querySelector("#empleadoId").value = empleadoId;

    await cargarRoles(empleadoId);

    modalRoles.classList.add("show");
    modalRoles.style.display = "flex";
  });

  // ============================================================
  // 🟦 6. GUARDAR ASIGNACIÓN
  // ============================================================
  guardarBtn.addEventListener("click", async () => {
    const empleadoId = formRoles.querySelector("#empleadoId").value;
    const rolesSeleccionados = [...rolesContainer.querySelectorAll("input[type='checkbox']:checked")].map(chk => chk.value);

    if (rolesSeleccionados.length === 0) {
      Alerts.warning("Debes seleccionar al menos un rol.");
      return;
    }

    try {
      Alerts.loading("Guardando roles...");
      const res = await fetch(API_ASIGNAR, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "assignRoles",
          empleado_id: empleadoId,
          roles: JSON.stringify(rolesSeleccionados),
        }),
      });
      const json = await res.json();
      Alerts.close();

      if (json.status === "success") {
        Alerts.success("Roles asignados correctamente");
        modalRoles.classList.remove("show");
        modalRoles.style.display = "none";
      } else {
        Alerts.error(json.message || "No se pudieron asignar los roles");
      }
    } catch (err) {
      Alerts.close();
      console.error("[guardarRoles] error:", err);
      Alerts.error("Error al conectar con el servidor.");
    }
  });

  // ============================================================
  // 🟪 7. CIERRE DEL MODAL
  // ============================================================
  document.querySelectorAll(".modal .close, .modal .btn-outline.close").forEach(btn => {
    btn.addEventListener("click", () => {
      modalRoles.classList.remove("show");
      modalRoles.style.display = "none";
    });
  });
});
