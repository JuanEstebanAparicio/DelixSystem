document.addEventListener("DOMContentLoaded", () => {

// 🛒 Manejo del carrito de compras
let carrito = [];
const carritoBtn = document.getElementById("verCarritoBtn");
const carritoModal = document.getElementById("carritoModal");
const carritoLista = document.getElementById("carritoLista");
const totalCarrito = document.getElementById("totalCarrito");
const cerrarCarrito = document.getElementById("cerrarCarrito");

// 🔹 Agregar platillo al carrito
document.querySelectorAll(".add-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    const id = parseInt(btn.dataset.id);
    const nombre = btn.dataset.nombre;
    const precio = parseFloat(btn.dataset.precio);

    const existente = carrito.find(item => item.id === id);

    if (existente) {
      existente.cantidad += 1;
    } else {
      carrito.push({ id, nombre, precio, cantidad: 1 });
    }

    actualizarCarrito();
  });
});

// 🔹 Actualizar contenido del carrito
function actualizarCarrito() {
  carritoLista.innerHTML = "";
  let total = 0;

  carrito.forEach((item, i) => {
    const subtotal = item.precio * item.cantidad;
    total += subtotal;

    const li = document.createElement("li");
    li.innerHTML = `
      ${item.nombre} — $${item.precio.toLocaleString()} x 
      <button class="cantidad-btn" data-index="${i}" data-action="menos">−</button>
      ${item.cantidad}
      <button class="cantidad-btn" data-index="${i}" data-action="mas">+</button>
      = <strong>$${subtotal.toLocaleString()}</strong>
    `;
    carritoLista.appendChild(li);
  });

  totalCarrito.textContent = total.toLocaleString();

  // Asignar eventos a botones + y -
  document.querySelectorAll(".cantidad-btn").forEach(btn => {
    btn.addEventListener("click", e => {
      const index = parseInt(e.target.dataset.index);
      const action = e.target.dataset.action;

      if (action === "mas") carrito[index].cantidad++;
      if (action === "menos") carrito[index].cantidad--;

      if (carrito[index].cantidad <= 0) carrito.splice(index, 1);

      actualizarCarrito();
    });
  });
}

// 🔹 Mostrar y cerrar carrito
carritoBtn.onclick = () => carritoModal.style.display = "block";
cerrarCarrito.onclick = () => carritoModal.style.display = "none";

// 💳 Manejo del pago
const pagoModal = document.getElementById("pagoModal");
const pagarBtn = document.getElementById("pagarBtn");
const cancelarPago = document.getElementById("cancelarPago");
const confirmarPago = document.getElementById("confirmarPagoBtn");
const metodoPago = document.getElementById("metodoPago");
const tarjetaInfo = document.getElementById("tarjetaInfo");

// 🔸 Abrir modal de pago
pagarBtn.onclick = () => {
  if (carrito.length === 0) {
    alert("🛒 El carrito está vacío.");
    return;
  }

  carritoModal.style.display = "none";
  pagoModal.style.display = "block";
};

// 🔸 Cancelar pago
cancelarPago.onclick = () => pagoModal.style.display = "none";

// 🔸 Mostrar datos de tarjeta
metodoPago.onchange = () => {
  tarjetaInfo.style.display = metodoPago.value === "tarjeta" ? "block" : "none";
};

// 🧾 Confirmar y enviar pedido real al backend
confirmarPago.onclick = async () => {
  if (carrito.length === 0) {
    alert("El carrito está vacío.");
    return;
  }

  const idMesa = document.body.dataset.mesa || 1; // ⚙️ temporal, asigna el ID real de la mesa
  const idUser = document.body.dataset.user || 1; // ⚙️ temporal, asigna el ID real del propietario

  const items = carrito.map(item => ({
    id: item.id,
    cantidad: item.cantidad,
    subtotal: item.precio * item.cantidad
  }));

  const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);

  try {
    const res = await fetch("/DelixSystem/app/pages/orders/php/add_order.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_mesa: idMesa, id_user: idUser, items, total })
    });

    const data = await res.json();

    if (data.success) {
      alert("✅ Pedido realizado con éxito. ¡Tu orden está en preparación!");
      carrito = [];
      actualizarCarrito();
      pagoModal.style.display = "none";
    } else {
      alert("❌ Error al procesar el pedido: " + (data.error || "Intenta de nuevo."));
    }
  } catch (err) {
    console.error(err);
    alert("⚠️ Error de conexión con el servidor.");
  }
};

const guardarClienteBtn = document.getElementById("guardarClienteBtn");

if (guardarClienteBtn) {
    guardarClienteBtn.addEventListener("click", () => {
        const nombre = document.getElementById("nombreCliente").value.trim();
        if(nombre === ""){
            alert("Ingresa un nombre o alias para continuar");
            return;
        }

        fetch("../php/guardar_cliente.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "nombre=" + encodeURIComponent(nombre)
        })
        .then(res => res.text())
        .then(data => {
            location.reload();
        });

    });
}

}); // cierre DOMContentLoaded
