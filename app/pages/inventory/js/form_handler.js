// ================================
// 🌿 form_handler.js - Versión Final Corregida ✅
// ================================

// ================================
// 🔍 VALIDAR FECHAS ANTES DE ENVIAR
// ================================
function validarFechas() {
  const ingreso = document.getElementById("fecha_ingreso").value;
  const vencimiento = document.getElementById("fecha_vencimiento").value;

  if (ingreso && vencimiento && new Date(vencimiento) < new Date(ingreso)) {
    alert("⚠️ La fecha de vencimiento no puede ser anterior a la de ingreso.");
    return false;
  }
  return true;
}

document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("formModal");
  const form = document.getElementById("ingredientForm");
  const modalTitle = document.getElementById("modalTitle");
  const actionInput = document.getElementById("action");
  const submitBtn = document.getElementById("submitBtn");
  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  const currentPhoto = document.getElementById("currentPhoto");
  const grid = document.getElementById("ingredientGrid");

  // ================================
  // 🔁 CARGAR INGREDIENTES DESDE EL SERVIDOR
  // ================================
  loadStorage();

  async function loadStorage() {
    const id_user = grid.dataset.user;
    grid.innerHTML = "<p class='loading'>Cargando ingredientes...</p>";

    try {
      const response = await fetch(`../php/utilidades/get_storage.php?id_user=${id_user}`);
      const data = await response.json();

      if (!data.success) throw new Error(data.error);
      renderStorage(data.insumos);
    } catch (error) {
      grid.innerHTML = `<p class='error'>Error al cargar ingredientes: ${error.message}</p>`;
    }
  }

  function renderStorage(ingredientes) {
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
          ? (ing.photo.startsWith("media/") ? "../" + ing.photo : "../media/" + ing.photo)
          : "../img/default.png";

        const card = document.createElement("div");
        card.className = "ingredient-card card";
        card.dataset.category = cat;

        card.innerHTML = `
          <div class="card-image">
            <img src="${imgPath}" alt="${ing.name}">
          </div>
          <div class="card-body">
            <h4 class="ingredient-name">${ing.name}</h4>
            <p class="ingredient-cost">$${Number(ing.unit_cost || 0).toLocaleString()}</p>
            <p class="ingredient-state ${ing.state?.toLowerCase() || "inactivo"}">${ing.state || "Inactivo"}</p>
            <p><strong>Cantidad:</strong> ${ing.amount} ${ing.unit}</p>
            <p><strong>Mínimo:</strong> ${ing.minimum_quantity}</p>
            <p class="ingredient-desc">${ing.description || "Sin descripción"}</p>
            <p><strong>Proveedor:</strong> ${ing.supplier || "No especificado"}</p>
            <p><strong>Ubicación:</strong> ${ing.location || "Sin ubicación"}</p>
          </div>
          <div class="card-footer">
            <button class="btn btn-edit" onclick='editIngredient(${JSON.stringify(ing)})'>✏️</button>
            <button class="btn btn-delete" onclick="deleteIngredient(${ing.id})">🗑️</button>
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

  // ================================
  // 🟢 NUEVO INGREDIENTE
  // ================================
  window.newIngredient = function () {
    modalTitle.textContent = "Registrar Ingrediente";
    submitBtn.textContent = "Guardar";
    actionInput.value = "create";

    form.reset();
    document.getElementById("ingredient_id").value = "";
    currentPhotoContainer.classList.add("hidden");

    modal.classList.remove("hidden");
  };

  // ================================
  // 🟡 EDITAR INGREDIENTE
  // ================================
  window.editIngredient = function (data) {
    modalTitle.textContent = "Editar Ingrediente";
    submitBtn.textContent = "Actualizar";
    actionInput.value = "update";

    document.getElementById("ingredient_id").value = data.id;
    document.getElementById("name").value = data.name;
    document.getElementById("amount").value = data.amount;
    document.getElementById("minimum_quantity").value = data.minimum_quantity;
    document.getElementById("unit").value = data.unit;
    document.getElementById("unit_cost").value = data.unit_cost;
    document.getElementById("category").value = data.category;
    document.getElementById("batch").value = data.batch;
    document.getElementById("description").value = data.description;
    document.getElementById("location").value = data.location;
    document.getElementById("state").value = data.state;
    document.getElementById("supplier").value = data.supplier;

    if (data.entrance_date) document.getElementById("fecha_ingreso").value = data.entrance_date.substring(0, 10);
    if (data.expiration_date) document.getElementById("fecha_vencimiento").value = data.expiration_date.substring(0, 10);

    if (data.photo) {
      currentPhoto.src = "../" + data.photo;
      currentPhotoContainer.classList.remove("hidden");
    } else {
      currentPhotoContainer.classList.add("hidden");
    }

    modal.classList.remove("hidden");
  };

  // ================================
  // 📤 ENVIAR FORMULARIO (CREAR / EDITAR)
  // ================================
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!validarFechas()) return;

    const formData = new FormData(form);
    const id = formData.get("id");
    const isEdit = id && id.trim() !== "";
    formData.append("action", isEdit ? "update" : "create");

    try {
      const res = await fetch("../php/inventario/ingredienteController.php", {
        method: "POST",
        body: formData,
      });

      const data = await res.json();

      if (data.success) {
        alert(data.message || "✅ Ingrediente guardado correctamente");
        hideModal();
        loadStorage();
      } else {
        alert("⚠️ Error: " + (data.error || "No se pudo procesar la solicitud"));
      }
    } catch (err) {
      alert("❌ Error de red: " + err.message);
    }
  });

  // ================================
  // 🗑️ ELIMINAR INGREDIENTE (USANDO CONTROLADOR)
  // ================================
  window.deleteIngredient = async function (id) {
    if (!confirm("¿Seguro que deseas eliminar este ingrediente?")) return;

    try {
      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("id", id);

      const res = await fetch("../php/inventario/ingredienteController.php", {
        method: "POST",
        body: formData,
      });

      const data = await res.json();

      if (data.success) {
        alert(data.message || "🗑️ Ingrediente eliminado correctamente.");
        loadStorage();
      } else {
        throw new Error(data.error || "Error al eliminar el ingrediente.");
      }
    } catch (err) {
      alert("❌ " + err.message);
    }
  };
});
// ================================
// 🔴 FUNCIÓN PARA CERRAR EL MODAL
// ================================
window.hideModal = function (modalId = "formModal") {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add("hidden");
  } else {
    console.error(`Modal con id "${modalId}" no encontrado.`);
  }
};
