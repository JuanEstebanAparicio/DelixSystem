// ============================================================
// 🧩 Módulo: Gestor de Empleados (empleados_core.js)
// ------------------------------------------------------------
// Controla:
//  - Listado dinámico de empleados (polling cada 5s)
//  - Render de tabla con highlight y animaciones
//  - Eliminación de empleados
//  - Control del modal base (abrir/cerrar)
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  console.log("[empleados_core.js] inicializado ✅");

  // ====== Config / elementos ======
  const tablaBody = document.querySelector("#tablaEmpleados tbody");
  const modalRoles = document.getElementById("modalRoles");
  const REFRESH_INTERVAL = 5000;
  const LIST_URL = "../php/employee/EmpleadoListController.php";
  let empleadosActuales = new Map();
  let refreshTimer = null;

  // Si no existe tabla, abortar (para evitar romper otras páginas)
  if (!tablaBody) {
    console.warn("[empleados_core.js] No se encontró tabla de empleados.");
    return;
  }

  // ====== Asegurar modal oculto ======
  if (modalRoles) modalRoles.style.display = "none";

  // ====== Helpers: modal ======
  const openModal = (selectorOrEl) => {
    const el = (typeof selectorOrEl === "string") ? document.querySelector(selectorOrEl) : selectorOrEl;
    if (!el) return;
    el.classList.add("show");
    el.style.display = "flex";
    const focusable = el.querySelector("input, button, textarea, select");
    if (focusable) focusable.focus();
  };

  const closeModal = (selectorOrEl) => {
    const el = (typeof selectorOrEl === "string") ? document.querySelector(selectorOrEl) : selectorOrEl;
    if (!el) return;
    el.classList.remove("show");
    el.style.display = "none";
  };

  // Cierre universal
  document.addEventListener("click", (e) => {
    if (e.target.matches(".modal .close, .modal .btn-outline.close")) {
      const modal = e.target.closest(".modal");
      if (modal) closeModal(modal);
    }
    if (e.target.classList.contains("modal")) closeModal(e.target);
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      document.querySelectorAll(".modal.show").forEach(m => closeModal(m));
    }
  });

  // ====== Efecto visual para filas ======
  const highlightRow = (row) => {
    row.classList.add("highlight-new");
    setTimeout(() => row.classList.remove("highlight-new"), 2000);
  };

  // ====== Crear fila ======
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

  // ====== Render tabla completa ======
  const renderTable = (empleados) => {
    tablaBody.innerHTML = "";
    empleados.forEach(emp => {
      const row = createRow(emp);
      tablaBody.appendChild(row);
      empleadosActuales.set(emp.id, emp);
    });
  };

  // ====== Actualización incremental ======
  const updateTable = (nuevosEmpleados) => {
    const nuevosMap = new Map(nuevosEmpleados.map(e => [e.id, e]));

    // 1️⃣ Insertar nuevos
    nuevosEmpleados.forEach(emp => {
      const existente = empleadosActuales.get(emp.id);
      if (!existente) {
        const newRow = createRow(emp);
        newRow.classList.add("fade-in");
        tablaBody.insertBefore(newRow, tablaBody.firstChild);
        highlightRow(newRow);
        empleadosActuales.set(emp.id, emp);
      } else {
        // 2️⃣ Si existe, actualizar solo si cambió algo
        if (
          existente.full_name !== emp.full_name ||
          existente.email !== emp.email ||
          existente.role !== emp.role ||
          existente.documento !== emp.documento ||
          existente.is_online !== emp.is_online
        ) {
          const row = tablaBody.querySelector(`tr[data-id="${emp.id}"]`);
          if (row) {
            row.children[0].textContent = emp.full_name;
            row.children[1].textContent = emp.email;
            row.children[2].textContent = emp.documento ?? '';
            row.children[3].textContent = emp.role ?? '';
            row.children[4].innerHTML = emp.is_online
              ? `<span class="estado online"><span class="dot"></span> 🟢Conectado</span>`
              : `<span class="estado offline"><span class="dot"></span> ⚫Desconectado</span>`;
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
      if (data.status !== "success") throw new Error(data.message || "Respuesta inválida");
      const empleados = data.data || [];

      if (empleadosActuales.size === 0) renderTable(empleados);
      else updateTable(empleados);

      if (empleados.length === 0 && tablaBody.children.length === 0) {
        tablaBody.innerHTML = `<tr><td colspan="6">⚠️ No hay empleados registrados</td></tr>`;
      }
    } catch (err) {
      console.error("[fetchEmployees] error:", err);
    }
  };

  // ====== Polling ======
  const startAutoRefresh = () => {
    refreshTimer = setInterval(fetchEmployees, REFRESH_INTERVAL);
  };

  // ====== Eliminar empleado ======
  tablaBody.addEventListener("click", async (e) => {
    const btnEliminar = e.target.closest(".btn-danger");
    if (!btnEliminar) return;

    const id = btnEliminar.dataset.id;
    const row = btnEliminar.closest("tr");
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
      } else {
        Alerts.error(json.message || "No se pudo eliminar");
      }
    } catch (err) {
      Alerts.close();
      console.error("[delete] error:", err);
      Alerts.error("Error de conexión con el servidor");
    }
  });

  // ====== Inicialización ======
  document.addEventListener("empleado-registrado", fetchEmployees);
  fetchEmployees();
  startAutoRefresh();
});
