function showModal(id) {
  document.getElementById(id).classList.remove("hidden");
}

function hideModal(id) {
  document.getElementById(id).classList.add("hidden");
}

document.addEventListener("DOMContentLoaded", () => {
  const selectCategoria = document.getElementById("category");
  const inputNuevaCategoria = document.getElementById("newCategoryInput");

  if (selectCategoria && inputNuevaCategoria) {
    selectCategoria.addEventListener("change", () => {
      if (selectCategoria.value === "__new__") {
        inputNuevaCategoria.classList.remove("hidden");
        inputNuevaCategoria.required = true;
        inputNuevaCategoria.focus();
      } else {
        inputNuevaCategoria.classList.add("hidden");
        inputNuevaCategoria.required = false;
        inputNuevaCategoria.value = "";
      }
    });
  }

  const form = document.getElementById("ingredientForm");
  if (form) {
    form.addEventListener("submit", (e) => {
      if (selectCategoria && selectCategoria.value === "__new__") {
        const nuevaCat = inputNuevaCategoria.value.trim();

        if (!nuevaCat) {
          e.preventDefault();
          alert("⚠️ Debes escribir un nombre para la nueva categoría.");
          inputNuevaCategoria.focus();
          return false;
        }

        let hiddenCat = document.getElementById("realCategory");
        if (!hiddenCat) {
          hiddenCat = document.createElement("input");
          hiddenCat.type = "hidden";
          hiddenCat.name = "category";
          hiddenCat.id = "realCategory";
          form.appendChild(hiddenCat);
        }

        hiddenCat.value = nuevaCat;
        selectCategoria.disabled = true;
      } else {
        selectCategoria.disabled = false;
        const hiddenCat = document.getElementById("realCategory");
        if (hiddenCat) hiddenCat.remove();
      }
    });
  }
});

function validarFechas() {
  const ingreso = document.getElementById("fecha_ingreso").value;
  const vencimiento = document.getElementById("fecha_vencimiento").value;
  if (vencimiento && vencimiento < ingreso) {
    alert("⚠️ La fecha de vencimiento no puede ser anterior a la de ingreso.");
    return false;
  }
  return true;
}

function newIngredient() {
  const form = document.getElementById("ingredientForm");
  form.reset();
  document.getElementById("ingredient_id").value = "";
  document.getElementById("modalTitle").textContent = "Registrar Ingrediente";
  document.getElementById("submitBtn").textContent = "Registrar Ingrediente";
  form.action = "../php/inputs_add.php";

  const hoy = new Date().toISOString().split("T")[0];
  document.getElementById("fecha_ingreso").value = hoy;

  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden");

  const inputNuevaCategoria = document.getElementById("newCategoryInput");
  if (inputNuevaCategoria) {
    inputNuevaCategoria.classList.add("hidden");
    inputNuevaCategoria.required = false;
    inputNuevaCategoria.value = "";
    inputNuevaCategoria.removeAttribute("data-edit-value");
  }

  const selectCategoria = document.getElementById("category");
  if (selectCategoria) {
    selectCategoria.disabled = false;
    selectCategoria.value = "";
  }

  const hiddenCat = document.getElementById("realCategory");
  if (hiddenCat) hiddenCat.remove();

  showModal("formModal");
}
function formatDateForInput(dateStr) {
  if (!dateStr) return "";
  const date = new Date(dateStr);
  if (isNaN(date)) return "";
  return date.toISOString().split("T")[0];
}

function editIngredient(data) {
  document.getElementById("ingredient_id").value = data.id || "";
  document.getElementById("name").value = data.name || "";
  document.getElementById("amount").value = data.amount || "";
  document.getElementById("minimum_quantity").value = data.minimum_quantity || "";
  document.getElementById("unit").value = data.unit || "Unidad";
  document.getElementById("unit_cost").value = data.unit_cost || "";
  document.getElementById("batch").value = data.batch || "";
  document.getElementById("description").value = data.description || "";
  document.getElementById("location").value = data.location || "";
  document.getElementById("state").value = data.state || "Activo";
  document.getElementById("supplier").value = data.supplier || "";
    
  const ingreso = data.entrance_date || data.fecha_ingreso || data.ingreso || "";
  const vencimiento = data.expiration_date || data.fecha_vencimiento || data.vencimiento || "";

  document.getElementById("fecha_ingreso").value = formatDateForInput(ingreso);
  document.getElementById("fecha_vencimiento").value = formatDateForInput(vencimiento);

  const selectCategoria = document.getElementById("category");
  const inputNuevaCategoria = document.getElementById("newCategoryInput");
  let existe = false;

  if (selectCategoria) {
    for (let i = 0; i < selectCategoria.options.length; i++) {
      if (selectCategoria.options[i].value === data.category) {
        existe = true;
        break;
      }
    }
  }

  if (existe) {
    selectCategoria.value = data.category;
    inputNuevaCategoria.classList.add("hidden");
    inputNuevaCategoria.required = false;
    inputNuevaCategoria.value = "";
  } else {
    selectCategoria.value = "__new__";
    inputNuevaCategoria.classList.remove("hidden");
    inputNuevaCategoria.required = true;
    inputNuevaCategoria.value = data.category || "";
  }

  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  const currentPhoto = document.getElementById("currentPhoto");
  if (data.photo && data.photo.trim() !== "") {
    currentPhoto.src = "../" + data.photo;
    currentPhotoContainer.classList.remove("hidden");
  } else {
    currentPhotoContainer.classList.add("hidden");
  }

  document.getElementById("modalTitle").textContent = "Editar Ingrediente";
  document.getElementById("submitBtn").textContent = "Actualizar Ingrediente";
  document.getElementById("ingredientForm").action = "../php/inputs_edit.php";

  const hiddenCat = document.getElementById("realCategory");
  if (hiddenCat) hiddenCat.remove();

  selectCategoria.disabled = false;
  showModal("formModal");
}
