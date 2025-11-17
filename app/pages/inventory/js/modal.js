document.addEventListener("DOMContentLoaded", () => {
  const fechaIngreso = document.getElementById("fecha_ingreso");
  const hoy = new Date().toISOString().split("T")[0];
  if (fechaIngreso) {
    if (!fechaIngreso.value) fechaIngreso.value = hoy;
    fechaIngreso.readOnly = true;
  }

  loadStorage();
  reloadCategories();

  const form = document.getElementById("ingredientForm");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      if (!validarFechas()) return;

      const formData = new FormData(form);

      const data = await safeFetchJson(
        "../php/inventario/ingredienteController.php",
        {
          method: "POST",
          body: formData
        }
      );

      if (data.status === "error") {
        Alerts?.warning && Alerts.warning(data.message);
        return;
      }

      Alerts?.success && Alerts.success(data.message || "Guardado correctamente.");
      hideModal("formModal");
      loadStorage();
      reloadCategories();
    });
  }

  const photoInput = document.getElementById("photo");
  if (photoInput) {
    photoInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (ev) {
          const preview = document.getElementById("currentPhoto");
          const container = document.getElementById("currentPhotoContainer");
          if (preview) preview.src = ev.target.result;
          if (container) container.classList.remove("hidden-img");
        };
        reader.readAsDataURL(file);
      }
    });
  }

  const categorySelect = document.getElementById("category");
  const newCategoryInput = document.getElementById("newCategoryInput");
  if (categorySelect && newCategoryInput) {
    categorySelect.addEventListener("change", () => {
      if (categorySelect.value === "__new__") {
        newCategoryInput.classList.remove("hidden-input");
        newCategoryInput.required = true;
      } else {
        newCategoryInput.classList.add("hidden-input");
        newCategoryInput.required = false;
        newCategoryInput.value = "";
      }
    });
  }
});

function escapeHtml(str) {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

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
  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden-img");
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
  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden-img");
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
    if (ing.entrance_date) {
      fechaIngreso.value = String(ing.entrance_date).split("T")[0];
    } else if (ing.fecha_ingreso) {
      fechaIngreso.value = String(ing.fecha_ingreso).split("T")[0];
    } else {
      fechaIngreso.value = new Date().toISOString().split("T")[0];
    }
    fechaIngreso.readOnly = true;
  }

  const fechaVenc = document.getElementById("fecha_vencimiento");
  if (fechaVenc) {
    if (ing.expiration_date) {
      fechaVenc.value = String(ing.expiration_date).split("T")[0];
    } else if (ing.fecha_vencimiento) {
      fechaVenc.value = String(ing.fecha_vencimiento).split("T")[0];
    } else {
      fechaVenc.value = "";
    }
  }

  const currentPhotoContainer = document.getElementById("currentPhotoContainer");
  const currentPhoto = document.getElementById("currentPhoto");
  if (ing.photo && ing.photo.trim() !== "") {
    if (currentPhoto) currentPhoto.src = ing.photo;
    if (currentPhotoContainer) currentPhotoContainer.classList.remove("hidden-img");
  } else {
    if (currentPhoto) currentPhoto.src = "../img/default.png";
    if (currentPhotoContainer) currentPhotoContainer.classList.add("hidden-img");
  }

  showModal("formModal");
}

async function deleteIngredient(id) {
  const confirm = await Swal.fire({
    title: "¿Eliminar ingrediente?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  });
  if (!confirm.isConfirmed) return;

  const data = await safeFetchJson("../php/inventario/ingredienteController.php", {
    method: "POST",
    body: new URLSearchParams({ id, action: "delete" }),
  });

  if (data.status === "error") return Alerts?.warning(data.message);
  Alerts?.success(data.message || "Eliminado.");
  loadStorage();
  reloadCategories();
}

const filterState = document.getElementById("filterState");
const searchInput = document.getElementById("searchInput");
const toggleFilters = document.getElementById("toggleFilters");
const filtersContainer = document.querySelector(".filters");

filterState.addEventListener("change", filtrarIngredientes);
searchInput.addEventListener("input", filtrarIngredientes);

function filtrarIngredientes() {
  const stateValue = filterState.value;
  const searchValue = searchInput.value.toLowerCase();

  const cards = document.querySelectorAll(".ingredient-card.card:not(.create-card)");

  cards.forEach(card => {
    const state = card.querySelector(".ingredient-state")?.textContent.trim() || "";
    const name = card.querySelector(".ingredient-name")?.textContent.toLowerCase() || "";

    const matchesState = stateValue === "Todos" || state.toLowerCase() === stateValue.toLowerCase();
    const matchesSearch = name.includes(searchValue);

    card.style.display = matchesState && matchesSearch ? "block" : "none";
  });
}

toggleFilters.addEventListener("click", () => {
  filtersContainer.classList.toggle("hidden-filters");
  toggleFilters.textContent = filtersContainer.classList.contains("hidden-filters")
    ? "Mostrar filtros"
    : "Ocultar filtros";
});
