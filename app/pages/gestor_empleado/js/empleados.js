// empleados.js
document.addEventListener("DOMContentLoaded", () => {
  const codigoEl = document.getElementById("codigoDinamico");
  const copiarBtn = document.getElementById("copiarCodigo");
  const nuevoBtn = document.getElementById("nuevoCodigo");
  const timerText = document.getElementById("timerText");
  const ring = document.querySelector(".progress");
  const estado = document.getElementById("estadoCodigo");

  const circ = 283;
  let timer = null;
  let tiempo = 60;

  // ⚠️ Verificamos que el userId esté disponible
  if (typeof userId === "undefined" || !userId) {
    console.error("❌ No se encontró userId. El usuario no está logueado correctamente.");
    estado.textContent = "Error: sesión inválida.";
    estado.classList.add("expirado");
    return;
  }

  // 🔄 Timer circular
  const startTimer = (exp) => {
    clearInterval(timer);
    const expireTime = new Date(exp + " UTC").getTime();

    timer = setInterval(() => {
      const now = Date.now();
      const diff = Math.max(0, Math.floor((expireTime - now) / 1000));
      tiempo = diff;

      ring.style.strokeDashoffset = circ * (1 - diff / 60);
      timerText.textContent = `${tiempo}s`;

      if (diff <= 0) {
        clearInterval(timer);
        estado.textContent = "Código expirado";
        estado.classList.remove("activo");
        estado.classList.add("expirado");
        codigoEl.textContent = "••••••••";
        autoRegenerate();
      }
    }, 1000);
  };

  // 📤 Request con user_id incluido
  // 📤 Request con manejo de permisos (403) y alertas elegantes
const request = async (action) => {
  try {
    const res = await fetch("../php/key/DynamicKeyController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        action,
        user_id: userId, // 👈 ID del usuario logueado
      }),
    });

    // --- Detectar 403 Forbidden ---
    if (res.status === 403) {
      const data = await res.json().catch(() => ({
        message: "No tienes permisos suficientes para realizar esta acción.",
      }));

      Alerts.warning(data.message || "No tienes permisos para esta acción.", "Acceso denegado");
      return { status: "error", message: data.message };
    }

    // --- Otras respuestas ---
    const text = await res.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch {
      throw new Error("Respuesta inválida del servidor: " + text);
    }

    return data;
  } catch (err) {
    console.error("🚨 Error en request:", err);
    Alerts.error("Error de conexión con el servidor.", "Error de red");
    return { status: "error", message: "Error de red" };
  }
};

  // 🧩 Obtener código
  const getCode = async () => {
    try {
      const data = await request("get");
      if (data.status === "success") {
        codigoEl.textContent = data.code;
        estado.textContent = "Código activo";
        estado.classList.add("activo");
        startTimer(data.expires_at);
      } else {
        codigoEl.textContent = "••••••••";
        estado.textContent = "Código expirado";
      }
    } catch (e) {
      console.error(e);
    }
  };

  // 🆕 Generar nuevo código
  const generateCode = async () => {
    try {
      const data = await request("generate");
      if (data.status === "success") {
        codigoEl.textContent = data.code;
        startTimer(data.expires_at);
        estado.textContent = "Código activo";
      }
    } catch (e) {
      console.error(e);
    }
  };

  // ♻️ Auto regenerar
  const autoRegenerate = () => {
    setTimeout(generateCode, 2000);
  };

  copiarBtn.addEventListener("click", () => {
    navigator.clipboard.writeText(codigoEl.textContent);
    copiarBtn.textContent = "✅ Copiado!";
    setTimeout(() => (copiarBtn.textContent = "Copiar"), 1500);
  });

  nuevoBtn.addEventListener("click", generateCode);

  // 🚀 Init
  getCode();
});

// -------------------------------------------------------------
// 🧩 GESTOR DE EMPLEADOS — con control interno de modales
// -------------------------------------------------------------
// empleados.js (reemplazo del bloque principal — mantiene polling, eliminación, listado)
document.addEventListener("DOMContentLoaded", () => {
  console.log("[empleados.js] init");

  // ====== Config / elementos ======
  const tablaBody = document.querySelector("#tablaEmpleados tbody");
  const modalRoles = document.getElementById("modalRoles");
  const REFRESH_INTERVAL = 5000;
  const LIST_URL = "../php/employee/EmpleadoListController.php";
  let empleadosActuales = new Map();
  let refreshTimer = null;

  // Si no existe tabla, salimos (no romperá otras páginas)
  if (!tablaBody) {
    console.warn("[empleados.js] No table body found, aborting employees block.");
    return;
  }

  // Aseguramos modal oculto al inicio (defensivo)
  if (modalRoles) {
    try { modalRoles.style.display = "none"; } catch (e) {}
  } else {
    console.warn("[empleados.js] modalRoles not found in DOM.");
  }

  // ====== Mini helpers de modal ======
  const openModal = (selectorOrEl) => {
    const el = (typeof selectorOrEl === "string") ? document.querySelector(selectorOrEl) : selectorOrEl;
    if (!el) { console.warn("[openModal] modal not found:", selectorOrEl); return; }
    el.classList.add("show");
    el.style.display = "flex";
    const first = el.querySelector("input, button, textarea, select");
    if (first) first.focus();
    console.log("[openModal] opened", el.id || el);
  };

  const closeModal = (selectorOrEl) => {
    const el = (typeof selectorOrEl === "string") ? document.querySelector(selectorOrEl) : selectorOrEl;
    if (!el) return;
    el.classList.remove("show");
    el.style.display = "none";
    console.log("[closeModal] closed", el.id || el);
  };

  // Cerrar modal al hacer clic fuera o tecla ESC
  document.addEventListener("click", (e) => {
    // Cerrar botones (tienen clase .close)
    if (e.target.matches(".modal .close") || e.target.matches(".modal .btn-outline.close")) {
      const modal = e.target.closest(".modal");
      if (modal) closeModal(modal);
    }
    // Cerrar al hacer clic directamente en el backdrop (el mismo div.modal)
    if (e.target.classList && e.target.classList.contains("modal")) {
      closeModal(e.target);
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") document.querySelectorAll(".modal.show").forEach(m => closeModal(m));
  });

  // ====== Animación fila nueva ======
  const highlightRow = (row) => {
    row.classList.add("highlight-new");
    setTimeout(() => row.classList.remove("highlight-new"), 2000);
  };

  // ====== Render / update tabla ======
  const createRow = (emp) => {
  const tr = document.createElement("tr");
  tr.dataset.id = emp.id;

  const estadoHTML = emp.is_online
    ? `<span class="estado online"><span class="dot"></span> 🟢Conectado</span>`
    : `<span class="estado offline"><span class="dot"></span> ⚫Desconectado</span>`;

  tr.innerHTML = `
    <td>${emp.full_name}</td>
    <td>${emp.email}</td>
    <td>${emp.documento ?? ''}</td>
    <td>${emp.role ?? ''}</td>
    <td>${estadoHTML}</td>
    <td>${new Date(emp.created_at).toLocaleString()}</td>
    <td class="acciones">
      <button class="btn btn-primary btn-sm asignar-rol" 
              data-id="${emp.id}" 
              data-modal-target="#modalRoles">Asignar roles</button>
      <button class="btn btn-danger btn-sm eliminar" data-id="${emp.id}">Eliminar</button>
    </td>
  `;
  return tr;
};



  const renderTable = (empleados) => {
    tablaBody.innerHTML = "";
    empleados.forEach(emp => {
      const row = createRow(emp);
      tablaBody.appendChild(row);
      empleadosActuales.set(emp.id, emp);
    });
  };

  const updateTable = (nuevosEmpleados) => {
  const nuevosMap = new Map(nuevosEmpleados.map(e => [e.id, e]));

  // 1️⃣ Insertar nuevos empleados
  nuevosEmpleados.forEach(emp => {
    const existente = empleadosActuales.get(emp.id);
    if (!existente) {
      const newRow = createRow(emp);
      newRow.classList.add("fade-in");
      tablaBody.insertBefore(newRow, tablaBody.firstChild);
      highlightRow(newRow);
      empleadosActuales.set(emp.id, emp);
    } else {
      // 2️⃣ Si existe, verificar si algo cambió (nombre, correo, roles)
      if (
        existente.full_name !== emp.full_name ||
        existente.email !== emp.email ||
        existente.role !== emp.role ||
        existente.documento !== emp.documento ||
        existente.is_online !== emp.is_online
        ) {
        const row = tablaBody.querySelector(`tr[data-id="${emp.id}"]`);
      if (row) {
    // columnas:
    // 0: Nombre, 1: Correo, 2: Documento, 3: Rol, 4: Estado, 5: Fecha, 6: Acciones
        row.children[0].textContent = emp.full_name;
        row.children[1].textContent = emp.email;
        row.children[2].textContent = emp.documento ?? '';
        row.children[3].textContent = emp.role ?? '';

      const estadoHTML = emp.is_online
        ? `<span class="estado online"><span class="dot"></span> 🟢Conectado</span>`
        : `<span class="estado offline"><span class="dot"></span> ⚫Desconectado</span>`;
      row.children[4].innerHTML = estadoHTML;

    highlightRow(row);
  }
  empleadosActuales.set(emp.id, emp);
}
 
    }
  });
  // 3️⃣ Eliminar empleados que ya no existan
  empleadosActuales.forEach((_, id) => {
    if (!nuevosMap.has(id)) {
      const row = tablaBody.querySelector(`tr[data-id="${id}"]`);
      if (row) row.remove();
      empleadosActuales.delete(id);
    }
  });
};


  // ====== Fetch empleados ======
  const fetchEmployees = async () => {
    try {
      const res = await fetch(LIST_URL, { cache: "no-store" });
      const data = await res.json();
      if (data.status !== "success") throw new Error(data.message || "Invalid response");
      const empleados = data.data || [];
      if (empleadosActuales.size === 0) renderTable(empleados); else updateTable(empleados);
      if (empleados.length === 0 && tablaBody.children.length === 0) {
        tablaBody.innerHTML = `<tr><td colspan="6">⚠️ No hay empleados registrados</td></tr>`;
      }
    } catch (err) {
      console.error("[fetchEmployees] error:", err);
    }
  };

  // ====== Polling ======
  const startAutoRefresh = () => { refreshTimer = setInterval(fetchEmployees, REFRESH_INTERVAL); };

  // ====== Eliminar empleado ======
  tablaBody.addEventListener("click", async (e) => {
    // eliminar
    if (e.target.matches(".btn-danger")) {
      const id = e.target.dataset.id;
      const row = e.target.closest("tr");
      const confirmed = await Alerts.confirm("¿Deseas eliminar este empleado?", "Confirmar eliminación");
      if (!confirmed) return;
      try {
        Alerts.loading("Eliminando empleado...");
        const res = await fetch("../php/employee/EmpleadoController.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ action: "delete", id }),
        });
        const json = await res.json();
        Alerts.close();
        if (json.status === "success") {
          Alerts.success(json.message || "Empleado eliminado");
          row.classList.add("fade-out");
          setTimeout(() => row.remove(), 350);
          empleadosActuales.delete(parseInt(id));
        } else Alerts.error(json.message || "No se pudo eliminar");
      } catch (err) {
        Alerts.close();
        console.error("[delete] error:", err);
        Alerts.error("Error de conexión");
      }
      return;
    }

    // abrir modal genérico (solo si NO es el botón de asignar rol)
const btnModal = e.target.closest("[data-modal-target]");
if (btnModal) {
  // si es un botón de asignar rol, dejamos que lo maneje el bloque de roles
  if (btnModal.classList.contains("asignar-rol")) return;

  const target = btnModal.dataset.modalTarget;
  console.log("[delegation] clicked modal button, target:", target, "data-id:", btnModal.dataset.id);
  openModal(target);
  return;
}

  });

  // Forzar actualización cuando un empleado se registre (evento disparado por tu flujo)
  document.addEventListener("empleado-registrado", fetchEmployees);

  // ====== Inicio ======
  fetchEmployees();
  startAutoRefresh();

});

// ================================
// 🎯 BLOQUE NUEVO: CARGA Y ACTUALIZACIÓN DE ROLES
// ================================

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
// Efecto visual de selección de roles
// 🧩 Efecto visual de selección de roles (versión estable)
// Evita duplicar listeners si el modal se abre más de una vez
if (!rolesContainer.dataset.listenerAttached) {
  rolesContainer.addEventListener('click', (e) => {
    const card = e.target.closest('.role-card');
    if (!card) return;

    const checkbox = card.querySelector('input[type="checkbox"]');
    checkbox.checked = !checkbox.checked;
    card.classList.toggle('active', checkbox.checked);
  });

  // Marcamos que ya tiene listener
  rolesContainer.dataset.listenerAttached = "true";
}



    // 4️⃣ Mostrar modal
    modal.classList.add("show");
    modal.style.display = "flex";

    // 5️⃣ Evitar duplicar eventos al guardar
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
          rolesContainer.querySelectorAll('.role-card.active').forEach(card => {
          card.classList.add('pulse-success');
          setTimeout(() => card.classList.remove('pulse-success'), 800);
          });

          // 🔁 Volver a marcar correctamente según la nueva asignación
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



function closeModal(selectorOrEl) {
  const el = (typeof selectorOrEl === "string") ? document.querySelector(selectorOrEl) : selectorOrEl;
  if (!el) return;
  el.classList.remove("show");
  el.style.display = "none";
  console.log("[closeModal - global] closed", el.id || el);
}

/* ============================================================
   🧩 BLOQUE: ADMIN_LOCAL Exclusivo en el Modal de Roles
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
  const rolesContainer = document.getElementById('rolesContainer');
  if (!rolesContainer) return;

  const ADMIN_ROLE_NAME = 'ADMIN_LOCAL';

  // Función para encontrar el checkbox del rol ADMIN_LOCAL
  function getAdminCheckbox() {
    const labels = rolesContainer.querySelectorAll('label');
    for (const label of labels) {
      const strong = label.querySelector('strong');
      if (strong && strong.textContent.trim().toUpperCase() === ADMIN_ROLE_NAME) {
        return label.querySelector('input[type="checkbox"]');
      }
    }
    return null;
  }

  // Desactivar visualmente otros checkboxes
  function toggleOtherRoles(disabled, except) {
    const checkboxes = rolesContainer.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(chk => {
      if (chk !== except) {
        chk.disabled = disabled;
        const card = chk.closest('.role-card') || chk.parentElement;
        card.style.opacity = disabled ? '0.6' : '1';
        card.style.pointerEvents = disabled ? 'none' : 'auto';

      }
    });
  }

  // Lógica principal cuando se marca/desmarca ADMIN_LOCAL
  function handleAdminToggle() {
    const adminChk = getAdminCheckbox();
    if (!adminChk) return;
    if (adminChk.checked) {
      toggleOtherRoles(true, adminChk);
      // Desmarcar otros roles (por coherencia)
      rolesContainer.querySelectorAll('input[type="checkbox"]').forEach(chk => {
        if (chk !== adminChk) chk.checked = false;
      });
    } else {
      toggleOtherRoles(false);
    }
  }

  // Lógica para evitar marcar ADMIN_LOCAL junto a otros
  rolesContainer.addEventListener('change', e => {
    const adminChk = getAdminCheckbox();
    if (!adminChk) return;
    const target = e.target;

    // Si marcó ADMIN_LOCAL
    if (target === adminChk) {
      handleAdminToggle();
    } else if (adminChk.checked && target.checked) {
      // Si marcó otro mientras ADMIN_LOCAL está activo → quitar ADMIN_LOCAL
      adminChk.checked = false;
      toggleOtherRoles(false);
    }
  });

  // Reaplica estado cuando se abre el modal (por si cambia dinámicamente)
  const modalRoles = document.getElementById('modalRoles');
  const observer = new MutationObserver(() => handleAdminToggle());
  observer.observe(rolesContainer, { childList: true, subtree: true });
});
