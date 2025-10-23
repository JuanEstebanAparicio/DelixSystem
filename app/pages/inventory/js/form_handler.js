function showModal(id) {
  document.getElementById(id).classList.remove("hidden");
}

function hideModal(id) {
  document.getElementById(id).classList.add("hidden");
}

document.addEventListener("DOMContentLoaded", () => {
  const hoy = new Date().toISOString().split("T")[0];
  const fechaIngreso = document.getElementById("fecha_ingreso");
  if (fechaIngreso) fechaIngreso.value = hoy;
});

function validarFechas() {
  const ingreso = document.getElementById("fecha_ingreso").value;
  const vencimiento = document.getElementById("fecha_vencimiento").value;
  if (vencimiento && vencimiento < ingreso) {
    alert("La fecha de vencimiento no puede ser anterior a la de ingreso.");
    return false;
  }
  return true;
}

function newIngredient() {
  document.getElementById("ingredientForm").reset();
  document.getElementById("ingredient_id").value = "";
  document.getElementById("modalTitle").textContent = "Registrar Ingrediente";
  document.getElementById("submitBtn").textContent = "Registrar Ingrediente";
  document.getElementById("ingredientForm").action = "../php/inputs_add.php";

  const hoy = new Date().toISOString().split("T")[0];
  document.getElementById("fecha_ingreso").value = hoy;

  // Ocultar imagen previa si se estaba editando antes
  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden");

  showModal("formModal");
}

function editIngredient(data) {
  document.getElementById("ingredient_id").value = data.id;
  document.getElementById("name").value = data.name;
  document.getElementById("amount").value = data.amount;
  document.getElementById("minimum_quantity").value = data.minimum_quantity;
  document.getElementById("unit").value = data.unit;
  document.getElementById("unit_cost").value = data.unit_cost;
  document.getElementById("category").value = data.category;
  document.getElementById("batch").value = data.batch || "";
  document.getElementById("description").value = data.description || "";
  document.getElementById("location").value = data.location || "";
  document.getElementById("status").value = data.status || "Activo";
  document.getElementById("supplier").value = data.supplier;
  document.getElementById("fecha_ingreso").value = data.entrance_date || "";
  document.getElementById("fecha_vencimiento").value = data.expiration_date || "";

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

  showModal("formModal");
}
