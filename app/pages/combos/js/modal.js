// === Modal: crear / editar combo ===
const comboModal = document.getElementById("comboModal");
const comboForm = document.getElementById("comboForm");
const comboModalTitle = document.getElementById("comboModalTitle");

// Abrir modal nuevo
function newCombo() {
  comboModalTitle.textContent = "Nuevo Combo";
  comboForm.reset();
  comboForm.dataset.mode = "create";
  comboModal.classList.remove("hidden");
}

// Abrir modal editar
function editCombo(combo) {
  comboModalTitle.textContent = "Editar Combo";
  comboForm.dataset.mode = "edit";
  comboForm.dataset.id = combo.id;

  document.getElementById("id_dish").value = combo.id_dish;
  document.getElementById("description").value = combo.description || "";
  document.getElementById("state").value = combo.state || "Activo";
  document.getElementById("type").value = combo.type || "Estatico";
  document.getElementById("start_time").value = combo.start_time || "";
  document.getElementById("end_time").value = combo.end_time || "";
  document.getElementById("start_date").value = combo.start_date || "";
  document.getElementById("end_date").value = combo.end_date || "";

  handleTypeFields(combo.type);
  comboModal.classList.remove("hidden");
}

// Cerrar modal
function closeComboModal() {
  comboModal.classList.add("hidden");
}

// Mostrar campos según tipo
document.getElementById("type").addEventListener("change", (e) => {
  handleTypeFields(e.target.value);
});

function handleTypeFields(type) {
  document.getElementById("timeFields").classList.toggle("hidden", type !== "Por_Horas");
  document.getElementById("dateFields").classList.toggle("hidden", type !== "Temporal");
}

// === Guardar combo ===
comboForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(comboForm);
  const mode = comboForm.dataset.mode;
  const url = mode === "create" 
    ? "../php/combo_add.php" 
    : "../php/combo_edit.php";

  if (mode === "edit") {
    formData.append("id", comboForm.dataset.id);
  }

  try {
    const response = await fetch(url, { method: "POST", body: formData });
    const data = await response.json();
    if (!data.success) throw new Error(data.error);

    closeComboModal();
    loadCombos();
  } catch (err) {
    alert("Error: " + err.message);
  }
});

// === Eliminar combo ===
async function deleteCombo(id) {
  if (!confirm("¿Seguro que deseas eliminar este combo?")) return;

  try {
    const response = await fetch("../php/combo_delete.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id=" + encodeURIComponent(id),
    });
    const data = await response.json();

    if (!data.success) throw new Error(data.error);
    loadCombos();
  } catch (err) {
    alert("Error al eliminar: " + err.message);
  }
}
