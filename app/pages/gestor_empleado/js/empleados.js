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
// 🧩 NUEVO BLOQUE OPTIMIZADO: Tabla reactiva de empleados
// -------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
  const tablaBody = document.querySelector("#tablaEmpleados tbody");
  if (!tablaBody) return;

  const LIST_URL = "../php/employee/EmpleadoListController.php";
  const REFRESH_INTERVAL = 5000; // cada 5 segundos

  let empleadosActuales = new Map();
  let refreshTimer = null;

  // 🎨 Animaciones suaves
  const highlightRow = (row) => {
    row.classList.add("highlight-new");
    setTimeout(() => row.classList.remove("highlight-new"), 2000);
  };

  // 🧩 Render inicial
  const renderTable = (empleados) => {
    tablaBody.innerHTML = "";
    empleados.forEach((emp) => {
      const row = createRow(emp);
      tablaBody.appendChild(row);
      empleadosActuales.set(emp.id, emp);
    });
  };

  // 🧱 Crear fila
  const createRow = (emp) => {
    const tr = document.createElement("tr");
    tr.dataset.id = emp.id;
    tr.innerHTML = `
      <td>${emp.id}</td>
      <td>${emp.full_name}</td>
      <td>${emp.email}</td>
      <td>${emp.role}</td>
      <td>${new Date(emp.created_at).toLocaleString()}</td>
      <td><button class="btn btn-danger btn-sm" data-id="${emp.id}">Eliminar</button></td>
    `;
    return tr;
  };

  // 🔍 Comparar y actualizar tabla sin recargar todo
  const updateTable = (nuevosEmpleados) => {
    const nuevosMap = new Map(nuevosEmpleados.map((e) => [e.id, e]));

    // 1️⃣ Insertar nuevos empleados
    nuevosEmpleados.forEach((emp) => {
      if (!empleadosActuales.has(emp.id)) {
        const newRow = createRow(emp);
        newRow.classList.add("fade-in");
        tablaBody.insertBefore(newRow, tablaBody.firstChild);
        highlightRow(newRow);
        empleadosActuales.set(emp.id, emp);
      }
    });

    // 2️⃣ Eliminar empleados que ya no existen (opcional)
    empleadosActuales.forEach((_, id) => {
      if (!nuevosMap.has(id)) {
        const row = tablaBody.querySelector(`tr[data-id="${id}"]`);
        if (row) row.remove();
        empleadosActuales.delete(id);
      }
    });
  };

  // 📡 Obtener empleados del servidor
  const fetchEmployees = async () => {
    try {
      const res = await fetch(LIST_URL, { cache: "no-store" });
      const data = await res.json();

      if (data.status !== "success") throw new Error(data.message);
      const empleados = data.data || [];

      if (empleadosActuales.size === 0) {
        renderTable(empleados);
      } else {
        updateTable(empleados);
      }

      if (empleados.length === 0 && tablaBody.children.length === 0) {
        tablaBody.innerHTML = `<tr><td colspan="6">⚠️ No hay empleados registrados</td></tr>`;
      }
    } catch (err) {
      console.error("⚠️ Error al obtener empleados:", err);
    }
  };

  // 🔁 Refresco periódico
  const startAutoRefresh = () => {
    refreshTimer = setInterval(fetchEmployees, REFRESH_INTERVAL);
  };

  // 🧩 Evento que fuerza actualización inmediata (sin esperar al polling)
  document.addEventListener("empleado-registrado", fetchEmployees);

  // 🗑️ Manejar eliminación de empleados directamente en la tabla
  tablaBody.addEventListener("click", async (e) => {
    if (!e.target.matches(".btn-danger")) return;

    const id = e.target.dataset.id;
    const fila = e.target.closest("tr");

    const confirmado = await Alerts.confirm(
      "¿Deseas eliminar este empleado?",
      "Confirmar eliminación"
    );

    if (!confirmado) return;

    try {
      Alerts.loading("Eliminando empleado...");

      const res = await fetch("../php/employee/EmpleadoController.php", {
  method: "POST",
  headers: { "Content-Type": "application/x-www-form-urlencoded" },
  body: new URLSearchParams({
    action: "delete",  // 👈 esto es lo que faltaba
    id
  }),
});


      const result = await res.json();
      Alerts.close();

      if (result.status === "success") {
        Alerts.success(result.message || "Empleado eliminado correctamente");

        // 🧩 Remover fila con animación suave
        fila.classList.add("fade-out");
        setTimeout(() => fila.remove(), 400);

        // 💾 También eliminamos del mapa local para mantener coherencia
        empleadosActuales.delete(parseInt(id));

      } else {
        Alerts.error(result.message || "No se pudo eliminar el empleado");
      }
    } catch (err) {
      Alerts.close();
      console.error("Error al eliminar empleado:", err);
      Alerts.error("Error de conexión con el servidor");
    }
  });

  
  // 🚀 Inicialización
  fetchEmployees();
  startAutoRefresh();

  
});
