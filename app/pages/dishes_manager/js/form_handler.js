// === FUNCIONES DE MODAL ===
function showModal(id) {
  document.getElementById(id).classList.remove("hidden");
}

function hideModal(id) {
  document.getElementById(id).classList.add("hidden");
}

// === FECHA ACTUAL AL CARGAR ===
document.addEventListener("DOMContentLoaded", () => {
  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;
});

// === CREAR NUEVO PLATO ===
function newDish() {
  const form = document.getElementById("dishForm");
  form.reset();

  // ID vacío porque es un nuevo registro
  document.getElementById("dish_id").value = "";

  // Título y botón
  document.getElementById("modalTitle").textContent = "Registrar Plato";
  document.getElementById("submitBtn").textContent = "Registrar Plato";

  // Acción del formulario (ruta PHP de inserción)
  form.action = "../php/dish_add.php";

  // Fecha actual
  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;

  // Ocultar imagen previa
  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden");

  showModal("formModal");
}

// === EDITAR PLATO EXISTENTE ===
function editDish(data) {
  // Llenar campos
  document.getElementById("dish_id").value = data.id || "";
  document.getElementById("name_dish").value = data.name_dish || "";
  document.getElementById("price").value = data.price || "";
  document.getElementById("category").value = data.category || "";
  document.getElementById("description").value = data.description || "";
  document.getElementById("state").value = data.state || "Activo";

  // Fecha actual (para actualizaciones también)
  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;

  // Imagen previa
  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  const currentPhoto = document.getElementById("currentPhoto");

  if (data.photo && data.photo.trim() !== "") {
    currentPhoto.src = "../" + data.photo;
    currentPhotoContainer.classList.remove("hidden");
  } else {
    currentPhotoContainer.classList.add("hidden");
  }

  // Título y botón
  document.getElementById("modalTitle").textContent = "Editar Plato";
  document.getElementById("submitBtn").textContent = "Actualizar Plato";

  // Acción del formulario (ruta PHP de actualización)
  document.getElementById("dishForm").action = "../php/dish_edit.php";

  showModal("formModal");
}
