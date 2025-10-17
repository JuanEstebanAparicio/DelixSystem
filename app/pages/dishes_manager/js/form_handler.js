function newDish() {
  document.getElementById("dishForm").reset();
  document.getElementById("dish_id").value = "";
  document.getElementById("modalTitle").textContent = "Registrar Plato";
  document.getElementById("submitBtn").textContent = "Registrar Plato";
  document.getElementById("dishForm").action =
    "/Proyecto_de_aula/Controller/kitchen/dish_add.php";
  showModal("formModal");
}

function editDish(data) {
  document.getElementById("dish_id").value = data.id;
  document.getElementById("nombre").value = data.nombre;
  document.getElementById("categoria").value = data.categoria;
  document.getElementById("precio").value = data.precio;
  document.getElementById("descripcion").value = data.descripcion;
  document.getElementById("estado").value = data.estado;
  document.getElementById("modalTitle").textContent = "Editar Plato";
  document.getElementById("submitBtn").textContent = "Actualizar Plato";
  document.getElementById("dishForm").action =
    "/Proyecto_de_aula/Controller/kitchen/dish_edit.php";
  showModal("formModal");
}
