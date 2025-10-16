
document.addEventListener("DOMContentLoaded", () => {
  const editButtons = document.querySelectorAll("button[data-modal-target='#editAreaModal']");
  const idInput = document.getElementById("editAreaId");
  const nameInput = document.getElementById("editAreaName");

  editButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      idInput.value = btn.dataset.idArea;
      nameInput.value = btn.dataset.nombreArea;
    });
  });
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

