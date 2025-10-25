function showModal(id) {
  document.getElementById(id).classList.remove("hidden");
}

function hideModal(id) {
  document.getElementById(id).classList.add("hidden");
}

document.addEventListener("DOMContentLoaded", () => {
  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;

  if (window.jQuery) {
    $('#ingredients').select2({
      placeholder: 'Selecciona los ingredientes...',
      width: '100%'
    });
  }
});

function newDish() {
  const form = document.getElementById("dishForm");
  form.reset();

  document.getElementById("dish_id").value = "";
  document.getElementById("modalTitle").textContent = "Registrar Plato";
  document.getElementById("submitBtn").textContent = "Registrar Plato";
  form.action = "../php/dish_add.php";

  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;

  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden");

  $('#ingredients').val(null).trigger('change');

  showModal("formModal");
}

function editDish(data) {
  document.getElementById("dish_id").value = data.id || "";
  document.getElementById("name_dish").value = data.name_dish || "";
  document.getElementById("price").value = data.price || "";
  document.getElementById("category").value = data.category || "";
  document.getElementById("description").value = data.description || "";
  document.getElementById("state").value = data.state || "Activo";

  const hoy = new Date().toISOString().split("T")[0];
  const fecha = document.getElementById("created_at");
  if (fecha) fecha.value = hoy;

  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  const currentPhoto = document.getElementById("currentPhoto");
  if (data.photo && data.photo.trim() !== "") {
    currentPhoto.src = "../" + data.photo;
    currentPhotoContainer.classList.remove("hidden");
  } else {
    currentPhotoContainer.classList.add("hidden");
  }

  if (data.ingredients && Array.isArray(data.ingredients)) {
    $('#ingredients').val(data.ingredients).trigger('change');
  } else {
    $('#ingredients').val(null).trigger('change');
  }

  document.getElementById("modalTitle").textContent = "Editar Plato";
  document.getElementById("submitBtn").textContent = "Actualizar Plato";
  document.getElementById("dishForm").action = "../php/dish_edit.php";

  showModal("formModal");
}
