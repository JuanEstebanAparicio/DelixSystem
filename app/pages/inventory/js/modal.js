/* ================================
   modal.js – ventanas modales
================================== */

function showModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove("hidden");
  document.body.style.overflow = "hidden";
}

function hideModal(id) {
  const el = document.getElementById(id);
  if (!el) return;

  el.classList.add("hidden");
  document.body.style.overflow = "";

  const form = document.getElementById("ingredientForm");
  if (form) form.reset();

  const photoContainer = document.getElementById("currentPhotoContainer");
  if (photoContainer) photoContainer.classList.add("hidden-img");

  const fechaIngreso = document.getElementById("fecha_ingreso");
  if (fechaIngreso) {
    fechaIngreso.value = new Date().toISOString().split("T")[0];
    fechaIngreso.readOnly = true;
  }
}

function newIngredient() {
  const form = document.getElementById("ingredientForm");
  if (!form) return;

  document.getElementById("modalTitle").textContent = "Registrar Ingrediente";
  const actionEl = document.getElementById("action");
  if (actionEl) actionEl.value = "create";

  form.reset();

  const fechaIngreso = document.getElementById("fecha_ingreso");
  if (fechaIngreso) {
    fechaIngreso.value = new Date().toISOString().split("T")[0];
    fechaIngreso.readOnly = true;
  }

  document.getElementById("currentPhotoContainer")?.classList.add("hidden-img");

  showModal("formModal");
}

function editIngredient(ing) {
  const form = document.getElementById("ingredientForm");
  if (!form) return;

  document.getElementById("modalTitle").textContent = "Editar Ingrediente";
  const actionEl = document.getElementById("action");
  if (actionEl) actionEl.value = "update";

  form.ingredient_id.value = ing.id ?? "";
  form.name.value = ing.name ?? "";
  form.amount.value = ing.amount ?? "";
  form.minimum_quantity.value = ing.minimum_quantity ?? "";
  form.unit.value = ing.unit ?? "Unidad";
  form.unit_cost.value = ing.unit_cost ?? "";
  form.category.value = ing.category ?? "";
  form.batch.value = ing.batch ?? "";
  form.description.value = ing.description ?? "";
  form.location.value = ing.location ?? "";
  form.state.value = ing.state ?? "Activo";
  form.supplier.value = ing.supplier ?? "";

  const fechaIngreso = document.getElementById("fecha_ingreso");
  if (fechaIngreso) {
    fechaIngreso.value =
      (ing.entrance_date || ing.fecha_ingreso || new Date().toISOString().split("T")[0])
        .toString().split("T")[0];
    fechaIngreso.readOnly = true;
  }

  const fechaVenc = document.getElementById("fecha_vencimiento");
  if (fechaVenc) {
    fechaVenc.value =
      (ing.expiration_date || ing.fecha_vencimiento || "").toString().split("T")[0];
  }

  const container = document.getElementById("currentPhotoContainer");
  const img = document.getElementById("currentPhoto");

  if (ing.photo?.trim()) {
    img.src = ing.photo;
    container.classList.remove("hidden-img");
  } else {
    img.src = "../img/default.png";
    container.classList.add("hidden-img");
  }

  showModal("formModal");
}
