// Manejo del carrito
const carrito = [];
const carritoBtn = document.getElementById("verCarritoBtn");
const carritoModal = document.getElementById("carritoModal");
const carritoLista = document.getElementById("carritoLista");
const totalCarrito = document.getElementById("totalCarrito");
const cerrarCarrito = document.getElementById("cerrarCarrito");

document.querySelectorAll(".add-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    const nombre = btn.dataset.nombre;
    const precio = parseFloat(btn.dataset.precio);
    carrito.push({ nombre, precio });
    actualizarCarrito();
  });
});

function actualizarCarrito() {
  carritoLista.innerHTML = "";
  let total = 0;

  carrito.forEach((item, i) => {
    total += item.precio;
    const li = document.createElement("li");
    li.textContent = `${item.nombre} — $${item.precio.toLocaleString()}`;
    carritoLista.appendChild(li);
  });

  totalCarrito.textContent = total.toLocaleString();
}

// Mostrar y cerrar carrito
carritoBtn.onclick = () => carritoModal.style.display = "block";
cerrarCarrito.onclick = () => carritoModal.style.display = "none";

// Simulación de pago
const pagoModal = document.getElementById("pagoModal");
const pagarBtn = document.getElementById("pagarBtn");
const cancelarPago = document.getElementById("cancelarPago");
const confirmarPago = document.getElementById("confirmarPagoBtn");
const metodoPago = document.getElementById("metodoPago");
const tarjetaInfo = document.getElementById("tarjetaInfo");

pagarBtn.onclick = () => {
  carritoModal.style.display = "none";
  pagoModal.style.display = "block";
};

cancelarPago.onclick = () => pagoModal.style.display = "none";

confirmarPago.onclick = () => {
  alert("✅ Pago simulado correctamente. ¡Tu pedido está en preparación!");
  pagoModal.style.display = "none";
  carrito.length = 0;
  actualizarCarrito();
};

metodoPago.onchange = () => {
  tarjetaInfo.style.display = metodoPago.value === "tarjeta" ? "block" : "none";
};
