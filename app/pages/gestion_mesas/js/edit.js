
// edit.js
document.addEventListener("click", (e) => {
  const btn = e.target.closest("button[data-modal-target='#editAreaModal']");
  if (!btn) return; // No es un botón de edición de área

  // 🔹 Obtener campos del modal
  const idInput = document.getElementById("editAreaId");
  const nameInput = document.getElementById("editAreaName");

  // 🔹 Asignar valores
  idInput.value = btn.dataset.idArea;
  nameInput.value = btn.dataset.nombreArea;
});

// === Modal Editar Mesa ===
document.addEventListener("DOMContentLoaded", () => {
  const mesaEditButtons = document.querySelectorAll("button[data-modal-target='#editMesaModal']");
  const idMesaInput = document.getElementById("editMesaId");
  const idAreaInput = document.getElementById("editMesaAreaId");
  const nombreMesaInput = document.getElementById("editMesaName");

  mesaEditButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      idMesaInput.value = btn.dataset.idMesa;
      idAreaInput.value = btn.dataset.idArea;
      nombreMesaInput.value = btn.dataset.nombreMesa;
    });
  });
});

