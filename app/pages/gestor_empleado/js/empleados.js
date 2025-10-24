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
  const request = async (action) => {
    try {
      const res = await fetch("../php/key/DynamicKeyController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action,
          user_id: userId, // 👈 se envía el ID real del usuario logueado
        }),
      });

      const text = await res.text();
      try {
        return JSON.parse(text);
      } catch {
        throw new Error("Invalid JSON: " + text);
      }
    } catch (err) {
      console.error("🚨 Error en request:", err);
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
    tr.innerHTML = `
      <td>${emp.id}</td>
      <td>${emp.full_name}</td>
      <td>${emp.email}</td>
      <td>${emp.role ?? ''}</td>
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
    // insertar nuevos
    nuevosEmpleados.forEach(emp => {
      if (!empleadosActuales.has(emp.id)) {
        const newRow = createRow(emp);
        newRow.classList.add("fade-in");
        tablaBody.insertBefore(newRow, tablaBody.firstChild);
        highlightRow(newRow);
        empleadosActuales.set(emp.id, emp);
      }
    });
    // eliminar viejos
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

    // abrir modal (delegación robusta para botones dinámicos)
    const btnModal = e.target.closest("[data-modal-target]");
    if (btnModal) {
      const target = btnModal.dataset.modalTarget;
      // debug rápido:
      console.log("[delegation] clicked modal button, target:", target, "data-id:", btnModal.dataset.id);
      // set empleadoId hidden inside modal if exists
      const empleadoIdInput = document.querySelector("#formRoles #empleadoId") || document.getElementById("empleadoId");
      if (empleadoIdInput) empleadoIdInput.value = btnModal.dataset.id || "";
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
// 🎯 BLOQUE NUEVO: CARGA DE ROLES
// ================================

// Este bloque se mantiene independiente pero usa la tabla y modal ya existentes
document.addEventListener("click", async (e) => {
  const btn = e.target.closest(".asignar-rol");
  if (!btn) return;

  const empleadoId = btn.dataset.id;
  const modal = document.querySelector("#modalRoles");
  const rolesContainer = modal.querySelector("#rolesContainer");
  const inputEmpleadoId = modal.querySelector("#empleadoId");

  inputEmpleadoId.value = empleadoId;

  try {
    // Obtener todos los roles disponibles
    const rolesRes = await fetch("../php/roles/RolesListController.php");
    const rolesData = await rolesRes.json();
    if (rolesData.status !== "success") throw new Error("Error al obtener roles");

    // Obtener roles asignados al empleado
    const empRes = await fetch(`../php/roles/RolesByEmployeeController.php?id=${empleadoId}`);
    const empData = await empRes.json();
    const rolesAsignados = empData.data ? empData.data.map(r => r.id) : [];

    // Renderizar checkboxes
    rolesContainer.innerHTML = "";
    rolesData.data.forEach((rol) => {
      const checked = rolesAsignados.includes(rol.id) ? "checked" : "";
      const div = document.createElement("div");
      div.classList.add("rol-item");
      div.innerHTML = `
        <label>
          <input type="checkbox" value="${rol.id}" ${checked}>
          <strong>${rol.nombre}</strong> - <small>${rol.descripcion}</small>
        </label>
      `;
      rolesContainer.appendChild(div);
    });

    // Mostrar modal (reutilizamos la función existente)
    modal.classList.add("show");
    modal.style.display = "flex";

  } catch (err) {
    console.error("Error al cargar roles:", err);
    Alerts.error("Error al cargar roles disponibles");
  }
});

// Guardar roles seleccionados
document.addEventListener("click", async (e) => {
  const btnGuardar = e.target.closest("#guardarRoles");
  if (!btnGuardar) return;

  const modal = document.querySelector("#modalRoles");
  const empleadoId = modal.querySelector("#empleadoId").value;
  const checkboxes = modal.querySelectorAll("#rolesContainer input[type='checkbox']:checked");
  const roles = Array.from(checkboxes).map(chk => chk.value);

  try {
    Alerts.loading("Guardando roles...");
    const res = await fetch("../php/roles/RolesAssignController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        empleado_id: empleadoId,
        roles: JSON.stringify(roles)
      }),
    });

    const data = await res.json();
    Alerts.close();

    if (data.status === "success") {
      Alerts.success(data.message);
      // Opcional: cerrar modal
      modal.classList.remove("show");
      modal.style.display = "none";
    } else {
      Alerts.error(data.message || "No se pudieron asignar los roles");
    }
  } catch (err) {
    Alerts.close();
    console.error("Error al guardar roles:", err);
    Alerts.error("Error al conectar con el servidor");
  }
});

