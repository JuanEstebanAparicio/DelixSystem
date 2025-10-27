document.addEventListener("DOMContentLoaded", () => {
  loadStorage();

  const form = document.getElementById("ingredientForm");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!validarFechas()) return;

      const formData = new FormData(form);
      const id = formData.get("id");
      const isEdit = id && id.trim() !== "";
      const url = isEdit ? "../php/inputs_edit.php" : "../php/inputs_add.php";

      try {
        const res = await fetch(url, { method: "POST", body: formData });
        const data = await res.json();

        if (data.success) {
          alert(data.message || "✅ Ingrediente guardado correctamente");
          hideModal("formModal");
          loadStorage(); // 🔄 Recarga sin refrescar
        } else {
          alert("⚠️ Error: " + (data.error || "No se pudo procesar la solicitud"));
        }
      } catch (err) {
        alert("❌ Error de red: " + err.message);
      }
    });
  }
});

async function loadStorage() {
  const grid = document.getElementById("ingredientGrid");
  grid.innerHTML = "<p class='loading'>Cargando ingredientes...</p>";

  try {
    const response = await fetch("../php/get_storage.php");
    const data = await response.json();

    if (!data.success) throw new Error(data.error);
    renderStorage(data.insumos);
  } catch (error) {
    grid.innerHTML = `<p class='error'>Error: ${error.message}</p>`;
  }
}

function renderStorage(ingredientes) {
  const grid = document.getElementById("ingredientGrid");
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
          <button class="btn btn-delet" onclick="deleteIngredient(${ing.id})">🗑️</button>
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

async function deleteIngredient(id) {
  if (!confirm("¿Eliminar ingrediente?")) return;

  try {
    const res = await fetch("../php/inputs_delete.php?id=" + id);
    if (!res.ok) throw new Error("Error al eliminar ingrediente");
    loadStorage();
  } catch (err) {
    alert(err.message);
  }
}
