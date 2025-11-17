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
    if (typeof Alerts !== "undefined" && Alerts.error) {
      Alerts.error("Error al cargar ingredientes: " + error.message);
    } else {
      console.error(error);
    }
  }
}

function renderStorage(ingredientes) {
  const grid = document.getElementById("ingredientGrid");
  if (!grid) return;
  grid.innerHTML = "";

  const categorias = {};
  ingredientes.forEach(ing => {
    const cat = ing.category || "Sin categoría";
    if (!categorias[cat]) categorias[cat] = [];
    categorias[cat].push(ing);
  });

  for (const cat in categorias) {
    categorias[cat].forEach(ing => {
      const imgPath = ing.photo && ing.photo.trim() !== ""
        ? ing.photo
        : "../img/default.png";

      const card = document.createElement("div");
      card.className = "ingredient-card card";
      card.dataset.category = cat;

      const ingEscaped = JSON.stringify(ing).replace(/'/g, "\\'");

      card.innerHTML = `
        <div class="card-image">
          <img src="${imgPath}" alt="${escapeHtml(ing.name || 'Ingrediente')}">
        </div>
        <div class="card-body">
          <h4 class="ingredient-name">${escapeHtml(ing.name || '')}</h4>
          <p class="ingredient-cost">$${Number(ing.unit_cost || 0).toLocaleString()}</p>
          <p class="ingredient-state ${(ing.state || "inactivo").toLowerCase()}">${escapeHtml(ing.state || "Inactivo")}</p>
          <p><strong>Cantidad:</strong> ${escapeHtml(String(ing.amount || 0))} ${escapeHtml(ing.unit || '')}</p>
          <p><strong>Mínimo:</strong> ${escapeHtml(String(ing.minimum_quantity || '0'))}</p>
          <p class="ingredient-desc">${escapeHtml(ing.description || "Sin descripción")}</p>
          <p><strong>Proveedor:</strong> ${escapeHtml(ing.supplier || "No especificado")}</p>
          <p><strong>Ubicación:</strong> ${escapeHtml(ing.location || "Sin ubicación")}</p>
        </div>
        <div class="card-footer">
          <button class="btn btn-edit" onclick='editIngredient(${ingEscaped})'>✏️</button>
          <button class="btn btn-delete" onclick="deleteIngredient(${Number(ing.id)})">🗑️</button>
        </div>
      `;
      grid.appendChild(card);
    });
  }

  const createCard = document.createElement("div");
  createCard.className = "ingredient-card card create-card";
  createCard.id = "globalCreateCard";
  createCard.onclick = () => newIngredient();
  createCard.innerHTML = `
    <div class="card-body text-center">
      <span class="plus-icon">+</span>
      <p>Crear Ingrediente</p>
    </div>
  `;
  grid.appendChild(createCard);
}

window.mostrarCategoria = async function (categoria) {
  const grid = document.getElementById("ingredientGrid");
  if (!grid) return;
  grid.innerHTML = "<p class='loading'>Filtrando ingredientes...</p>";

  try {
    const response = await fetch("../php/utilidades/get_storage.php");
    const data = await response.json();
    if (!data.success) throw new Error(data.error || "Respuesta inválida del servidor.");
    let ingredientes = data.insumos || [];

    if (categoria !== "Todos") {
      ingredientes = ingredientes.filter(ing => (ing.category || "Sin categoría") === categoria);
    }

    renderStorage(ingredientes);

    document.querySelectorAll(".sidebar-item").forEach(item => {
      item.classList.toggle("active", item.textContent.trim() === categoria);
    });

    document.getElementById("sidebarMenu")?.classList.remove("active");
    document.getElementById("sidebarOverlay")?.classList.remove("active");
    filtrarIngredientes();

  } catch (error) {
    grid.innerHTML = `<p class='error'>Error al filtrar: ${error.message}</p>`;
    if (typeof Alerts !== "undefined" && Alerts.error)
      Alerts.error("Error al filtrar: " + error.message);
  }
};

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

    categoryList.innerHTML = `<li class="sidebar-item active" onclick="mostrarCategoria('Todos')">Todos</li>`;

    [...categoriasBase, ...categoriasBD].forEach(cat => {
      const li = document.createElement("li");
      li.classList.add("sidebar-item");
      li.textContent = cat;
      li.onclick = () => mostrarCategoria(cat);
      categoryList.appendChild(li);
    });

    const opcionesFijas = Array.from(categorySelect.options).filter(opt =>
      categoriasBase.includes(opt.value) ||
      opt.value === "" ||
      opt.value === "__new__"
    );

    categorySelect.innerHTML = "";

    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.disabled = true;
    defaultOption.selected = true;
    defaultOption.textContent = "Seleccione o cree una categoría";
    categorySelect.appendChild(defaultOption);

    categoriasBase.forEach(cat => {
      const opt = document.createElement("option");
      opt.value = cat;
      opt.textContent = cat;
      categorySelect.appendChild(opt);
    });

    categoriasBD.forEach(cat => {
      if (!categoriasBase.includes(cat)) {
        const opt = document.createElement("option");
        opt.value = cat;
        opt.textContent = cat;
        categorySelect.appendChild(opt);
      }
    });

    const newOpt = document.createElement("option");
    newOpt.value = "__new__";
    newOpt.textContent = "+ Nueva categoría...";
    categorySelect.appendChild(newOpt);

  } catch (error) {
    console.error(error);
    if (typeof Alerts !== "undefined" && Alerts.error)
      Alerts.error("Error al recargar categorías: " + error.message);
  }
};

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
