function makeOrder() {
    alert("🛒 Pedido registrado con éxito. ¡Gracias por tu orden!");
    // Aquí podrías redirigir a otra página o guardar el pedido en Supabase:
    // window.location.href = "pedido.php?id_mesa=" + mesaId;
}
let carrito = [];
const verCarritoBtn = document.getElementById("verCarritoBtn");
const carritoModal = document.getElementById("carritoModal");
const carritoLista = document.getElementById("carritoLista");
const totalCarrito = document.getElementById("totalCarrito");
const pagarBtn = document.getElementById("pagarBtn");
const cerrarCarrito = document.getElementById("cerrarCarrito");
const pagoModal = document.getElementById("pagoModal");
const confirmarPagoBtn = document.getElementById("confirmarPagoBtn");
const cancelarPago = document.getElementById("cancelarPago");
const metodoPago = document.getElementById("metodoPago");
const tarjetaInfo = document.getElementById("tarjetaInfo");

document.querySelectorAll(".add-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    const nombre = btn.dataset.nombre;
    const precio = parseInt(btn.dataset.precio);
    carrito.push({ nombre, precio });
    alert(`${nombre} agregado al carrito`);
  });
});

verCarritoBtn.addEventListener("click", () => {
  carritoLista.innerHTML = "";
  let total = 0;
  carrito.forEach(item => {
    carritoLista.innerHTML += `<li>${item.nombre} - $${item.precio}</li>`;
    total += item.precio;
  });
  totalCarrito.textContent = total;
  carritoModal.style.display = "block";
});

cerrarCarrito.addEventListener("click", () => carritoModal.style.display = "none");

pagarBtn.addEventListener("click", () => {
  carritoModal.style.display = "none";
  pagoModal.style.display = "block";
});

cancelarPago.addEventListener("click", () => pagoModal.style.display = "none");

metodoPago.addEventListener("change", () => {
  tarjetaInfo.style.display = metodoPago.value === "tarjeta" ? "block" : "none";
});

confirmarPagoBtn.addEventListener("click", async () => {
  pagoModal.style.display = "none";
  alert("✅ Pago confirmado. Pedido en preparación...");

  // Simulación de registro del pedido en Supabase
  const pedido = {
    id_mesa: idMesa,
    items: carrito,
    total: carrito.reduce((acc, i) => acc + i.precio, 0),
    estado: "Pendiente"
  };

  console.log("Pedido enviado:", pedido);

  // Aquí puedes usar fetch() para enviar a un endpoint PHP o directamente a Supabase.
  carrito = [];
});
