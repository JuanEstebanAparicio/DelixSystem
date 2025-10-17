document.addEventListener("DOMContentLoaded", () => {
  const hoy = new Date().toISOString().split("T")[0];
  const fechaIngreso = document.getElementById("fecha_ingreso");

  if (fechaIngreso) {
    fechaIngreso.value = hoy;
  }
});

function validarFechas() {
  const ingreso = document.getElementById("fecha_ingreso").value;
  const vencimiento = document.getElementById("fecha_vencimiento").value;

  if (vencimiento && vencimiento < ingreso) {
    alert("La fecha de vencimiento no puede ser anterior a la fecha de ingreso.");
    return false;
  }
  return true;
}

function newIngredient() {
  document.getElementById("ingredientForm").reset();
  document.getElementById("ingredient_id").value = "";
  document.getElementById("modalTitle").textContent = "Registrar Ingrediente";
  document.getElementById("submitBtn").textContent = "Registrar Ingrediente";
  document.getElementById("ingredientForm").action = "/Proyecto_de_aula/Controller/store/inputs_add.php";
  const hoy = new Date().toISOString().split("T")[0];
  document.getElementById("fecha_ingreso").value = hoy;

  showModal("formModal");
}

function editIngredient(data) {
  document.getElementById("ingredient_id").value = data.id;
  document.getElementById("nombre").value = data.nombre;
  document.getElementById("cantidad").value = data.cantidad;
  document.getElementById("cantidad_minima").value = data.cantidad_minima;
  document.getElementById("unidad").value = data.unidad;
  document.getElementById("costo_unitario").value = data.costo_unitario;
  document.getElementById("categoria").value = data.categoria;
  document.getElementById("fecha_ingreso").value = data.fecha_ingreso;
  document.getElementById("fecha_vencimiento").value = data.fecha_vencimiento;
  document.getElementById("lote").value = data.lote;
  document.getElementById("descripcion").value = data.descripcion;
  document.getElementById("ubicacion").value = data.ubicacion;
  document.getElementById("estado").value = data.estado;
  document.getElementById("proveedor").value = data.proveedor;

  document.getElementById("modalTitle").textContent = "Editar Ingrediente";
  document.getElementById("submitBtn").textContent = "Actualizar Ingrediente";
  document.getElementById("ingredientForm").action = "/Proyecto_de_aula/Controller/store/inputs_edit.php";

  showModal("formModal");
}
