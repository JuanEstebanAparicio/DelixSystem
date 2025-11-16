/* ================================
   core.js – utilidades y carga inicial
================================== */

async function safeFetchJson(url, options = {}) {
  try {
    const resp = await fetch(url, options);
    const text = await resp.text();

    try {
      return JSON.parse(text);
    } catch {
      return {
        status: "error",
        message: "Respuesta no válida desde el servidor.",
        raw: text
      };
    }
  } catch (err) {
    return {
      status: "error",
      message: "Error al conectar con el servidor.",
      error: err.message
    };
  }
}

function validarFechas() {
  const ingresoEl = document.getElementById("fecha_ingreso");
  const vencimientoEl = document.getElementById("fecha_vencimiento");
  if (!ingresoEl || !vencimientoEl) return true;

  const ingreso = ingresoEl.value;
  const vencimiento = vencimientoEl.value;

  if (ingreso && vencimiento && new Date(vencimiento) < new Date(ingreso)) {
    if (typeof Alerts !== "undefined" && Alerts.warning) {
      Alerts.warning("La fecha de vencimiento no puede ser anterior a la de ingreso.");
    } else {
      alert("La fecha de vencimiento no puede ser anterior a la de ingreso.");
    }
    return false;
  }
  return true;
}

async function loadStorage() {
  const grid = document.getElementById("ingredientGrid");
  if (!grid) return;
  grid.innerHTML = "<p class='loading'>Cargando ingredientes...</p>";

  try {
    const response = await fetch("../php/utilidades/get_storage.php");
    const data = await response.json();

    if (!data.success) throw new Error(data.error || "Respuesta inválida del servidor.");
    renderStorage(data.insumos || []);

  } catch (error) {
    grid.innerHTML = `<p class='error'>Error al cargar ingredientes: ${error.message}</p>`;
    Alerts?.error?.("Error al cargar ingredientes: " + error.message);
  }
}

function escapeHtml(str) {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

document.addEventListener("DOMContentLoaded", () => {
  const fechaIngreso = document.getElementById("fecha_ingreso");
  const hoy = new Date().toISOString().split("T")[0];
  if (fechaIngreso) {
    if (!fechaIngreso.value) fechaIngreso.value = hoy;
    fechaIngreso.readOnly = true;
  }

  loadStorage();
  reloadCategories();
});
function toggleSidebar() {
  const sidebar = document.getElementById("sidebarMenu");
  let overlay = document.getElementById("sidebarOverlay");

  if (!overlay) {
    overlay = document.createElement("div");
    overlay.id = "sidebarOverlay";
    overlay.classList.add("sidebar-overlay");
    document.body.appendChild(overlay);
    overlay.addEventListener("click", toggleSidebar);
  }

  sidebar.classList.toggle("active");
  overlay.classList.toggle("active");
}
window.reloadCategories = async function () {
  const categoryList = document.getElementById("categoryList");
  const categorySelect = document.getElementById("category");
  if (!categoryList || !categorySelect) return;

  const categoriasBase = [
    "Proteína", "Carbohidrato", "Vegetal", "Lácteo",
    "Bebida", "Salsa", "Fruta", "Cereal / Harina", "Snack"
  ];

  try {
    const res = await fetch("../php/utilidades/get_storage.php");
    const data = await res.json();
    if (!data.success) throw new Error(data.error || "Respuesta inválida");

    const categoriasBD = data.categorias || [];

    categoryList.innerHTML = `
      <li class="sidebar-item active" onclick="mostrarCategoria('Todos')">Todos</li>
    `;

    [...categoriasBase, ...categoriasBD].forEach(cat => {
      const li = document.createElement("li");
      li.classList.add("sidebar-item");
      li.textContent = cat;
      li.onclick = () => mostrarCategoria(cat);
      categoryList.appendChild(li);
    });

    categorySelect.innerHTML = `
      <option value="" disabled selected>Seleccione o cree una categoría</option>
    `;

    categoriasBase.forEach(cat => {
      categorySelect.innerHTML += `<option value="${cat}">${cat}</option>`;
    });

    categoriasBD.forEach(cat => {
      if (!categoriasBase.includes(cat)) {
        categorySelect.innerHTML += `<option value="${cat}">${cat}</option>`;
      }
    });

    categorySelect.innerHTML += `<option value="__new__">+ Nueva categoría...</option>`;

  } catch (error) {
    console.error(error);
    Alerts?.error?.("Error al recargar categorías: " + error.message);
  }
};
/* ============================================
   🔄 Sistema en tiempo real (Smart Polling)
============================================ */
let ultimaActualizacion = null;

async function checkForUpdates() {
  const userId = document.getElementById("ingredientGrid")?.dataset.user;
  if (!userId) return;

  try {
    const res = await fetch("../php/utilidades/check_updates.php?id_user=" + userId);
    const data = await res.json();

    if (!data.success) return;

    // Si es la primera vez, guardamos el timestamp
    if (!ultimaActualizacion) {
      ultimaActualizacion = data.last_update;
      return;
    }

    // Si detectamos un cambio
    if (data.last_update !== ultimaActualizacion) {
      ultimaActualizacion = data.last_update;

      // Recargar ingredientes sin recargar la página
      loadStorage();

      // Recargar categorías
      reloadCategories();

      console.log("♻ Inventario actualizado automáticamente.");
    }

  } catch (error) {
    console.error("Error checkForUpdates:", error);
  }
}

// Ejecutar cada 4 segundos sin afectar el rendimiento
setInterval(checkForUpdates, 4000);
