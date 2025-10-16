
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
