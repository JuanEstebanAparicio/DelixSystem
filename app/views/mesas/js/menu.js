document.addEventListener("DOMContentLoaded", () => {

    const APP = {
      id_user: parseInt(document.body.dataset.id_user),
      restaurant_name: document.body.dataset.restaurant_name,
      id_area: parseInt(document.body.dataset.id_area),
      area: document.body.dataset.area,
      id_mesa: parseInt(document.body.dataset.id_mesa),
      mesa: document.body.dataset.mesa,
      nombre_cliente: localStorage.getItem("nombre_cliente") || null
  };

// 🛒 Manejo del carrito de compras
let carrito = [];
const carritoBtn = document.getElementById("verCarritoBtn");
const carritoModal = document.getElementById("carritoModal");
const carritoLista = document.getElementById("carritoLista");
const totalCarrito = document.getElementById("totalCarrito");
const cerrarCarrito = document.getElementById("cerrarCarrito");

// badge numero carrito
const badgeCarrito = document.getElementById("cartCount");

// ---------- FUNCIONES AUX ----------

// actualizar contador REAL = suma cantidades
function actualizarContadorCarrito(){
  let totalCantidad = carrito.reduce((acc,i)=> acc + i.cantidad, 0);
  badgeCarrito.textContent = totalCantidad;

  // pequeña animacion
  badgeCarrito.classList.add("bump");
  setTimeout(()=> badgeCarrito.classList.remove("bump"), 250);
}


// ---------- AGREGAR PLATILLO ----------
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

    // ANIMACION vuelo hacia carrito
    const img = btn.closest(".platillo-card").querySelector("img");
    const imgClone = img.cloneNode(true);
    const rect = img.getBoundingClientRect();

    imgClone.style.position="absolute";
    imgClone.style.width="80px";
    imgClone.style.zIndex="9999";
    imgClone.style.left = rect.left+"px";
    imgClone.style.top = rect.top+"px";
    document.body.appendChild(imgClone);

    const cartPos = carritoBtn.getBoundingClientRect();

    imgClone.animate([
      { transform:`translate(0,0)`, opacity:1 },
      { transform:`translate(${cartPos.left-rect.left}px, ${cartPos.top-rect.top}px) scale(0.2)`, opacity:0 }
    ],{
      duration:600,
      easing:"ease-in-out"
    }).onfinish = ()=> imgClone.remove();

    actualizarContadorCarrito();
    actualizarCarrito();
  });
});


// ---------- ACTUALIZAR CARRITO ----------
function actualizarCarrito() {
  carritoLista.innerHTML = "";
  let total = 0;

  carrito.forEach((item, i) => {
    const subtotal = item.precio * item.cantidad;
    total += subtotal;

    const li = document.createElement("li");
   li.innerHTML = `
  <div class="cart-item-left">
    <span class="cart-item-name">${item.nombre}</span>
    <span class="cart-item-price">$${item.precio.toLocaleString()}</span>
  </div>

  <div class="cart-qty">
    <button class="cantidad-btn" data-index="${i}" data-action="menos">−</button>
    <span>${item.cantidad}</span>
    <button class="cantidad-btn" data-index="${i}" data-action="mas">+</button>
  </div>

  <div class="cart-sub">
    <strong>$${subtotal.toLocaleString()}</strong>
  </div>
`;

    carritoLista.appendChild(li);
  });

  totalCarrito.textContent = total.toLocaleString();

  document.querySelectorAll(".cantidad-btn").forEach(btn => {
    btn.addEventListener("click", e => {
      const index = parseInt(e.target.dataset.index);
      const action = e.target.dataset.action;

      if (action === "mas") carrito[index].cantidad++;
      if (action === "menos") carrito[index].cantidad--;

      if (carrito[index].cantidad <= 0) carrito.splice(index, 1);

      actualizarCarrito();
      actualizarContadorCarrito();
    });
  });
}


// ---------- Mostrar / Ocultar Carrito ----------
carritoBtn.onclick = () => carritoModal.style.display = "block";
cerrarCarrito.onclick = () => carritoModal.style.display = "none";


// ---------- Pago ----------
const pagoModal = document.getElementById("pagoModal");
const pagarBtn = document.getElementById("pagarBtn");
const cancelarPago = document.getElementById("cancelarPago");
const confirmarPago = document.getElementById("confirmarPagoBtn");
const metodoPago = document.getElementById("metodoPago");
const tarjetaInfo = document.getElementById("tarjetaInfo");

pagarBtn.onclick = () => {
  if (carrito.length === 0) {
    alert("🛒 El carrito está vacío.");
    return;
  }

  carritoModal.style.display = "none";
  pagoModal.style.display = "block";
};

cancelarPago.onclick = () => pagoModal.style.display = "none";

metodoPago.onchange = () => {
  tarjetaInfo.style.display = metodoPago.value === "tarjeta" ? "block" : "none";
};


// ---------- Confirmar Pedido ----------
confirmarPago.onclick = async () => {
  if (carrito.length === 0) {
    alert("El carrito está vacío.");
    return;
  }

  const idMesa = document.body.dataset.mesa || 1;
  const idUser = document.body.dataset.user || 1;

  const items = carrito.map(item => ({
    id: item.id,
    cantidad: item.cantidad,
    subtotal: item.precio * item.cantidad
  }));

  const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);

  try {
    const res = await fetch("/DelixSystem/app/pages/pedidos/php/pedidosController.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
     body: JSON.stringify({
  id_user: APP.id_user,
  restaurant_name: APP.restaurant_name,
  id_area: APP.id_area,
  area: APP.area,
  id_mesa: APP.id_mesa,
  mesa: APP.mesa,
  nombre_cliente: APP.nombre_cliente,
  total: total,
  metodo_pago: metodo_pago_seleccionado, // el que hicimos arriba
  items: items
})

    });

    const data = await res.json();

    if (data.success) {
      alert("✅ Pedido realizado con éxito. ¡Tu orden está en preparación!");
      carrito = [];
      actualizarCarrito();
      actualizarContadorCarrito();
      pagoModal.style.display = "none";
    } else {
      alert("❌ Error al procesar el pedido: " + (data.error || "Intenta de nuevo."));
    }
  } catch (err) {
    console.error(err);
    alert("⚠️ Error de conexión con el servidor.");
  }
};

// ---------- Guardar cliente OPCIONAL ----------
const guardarClienteBtn = document.getElementById("guardarClienteBtn");

if (guardarClienteBtn) {
    guardarClienteBtn.addEventListener("click", () => {
        const nombre = document.getElementById("nombreCliente").value.trim();
        if(nombre === ""){
            alert("Ingresa un nombre o alias para continuar");
            return;
        }

        // Guardar en localStorage
        localStorage.setItem("nombre_cliente", nombre);

        fetch("../php/guardar_cliente.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "nombre=" + encodeURIComponent(nombre)
        })
        .then(res => res.text())
        .then(data => {
            location.reload(); // APP.nombre_cliente tendrá valor
        });
    });
}


// ==== abrir bottom sheet premium ====
const sheet = document.getElementById("bottomSheetPago");

pagarBtn.onclick = () => {
  if (carrito.length === 0) return alert("🛒 El carrito está vacío.");
  carritoModal.style.display="none";
  sheet.classList.add("show");
};

// seleccionar método (CREA PEDIDO REAL YA)
document.querySelectorAll(".bs-item").forEach(b => {
    b.addEventListener("click", async () => {
        const metodo = b.dataset.metodo;
        sheet.classList.remove("show");

        if(carrito.length === 0) return alert("🛒 Carrito vacío.");

 APP.nombre_cliente = localStorage.getItem("nombre_cliente") || null;

        const body = {
    id_user: APP.id_user,
    restaurant_name: APP.restaurant_name,
    id_area: APP.id_area,
    area: APP.area,
    id_mesa: APP.id_mesa,
    mesa: APP.mesa,
   nombre_cliente: APP.nombre_cliente,
    total: carrito.reduce((a,i)=>a+(i.precio*i.cantidad),0),
    metodo_pago: metodo,
    items: carrito.map(i=>({
        id_platillo: i.id,
        nombre_platillo: i.nombre,
        precio: i.precio,
        cantidad: i.cantidad
    }))
};


        try {
            const res = await fetch("/DelixSystem/app/pages/pedidos/php/pedidosController.php", {
                method:"POST",
                headers:{"Content-Type":"application/json"},
                body:JSON.stringify(body)
            });
            const data = await res.json();

            if(data.success){
                alert("✅ Pedido creado! Método: "+metodo);
                carrito = [];
                actualizarCarrito();
                actualizarContadorCarrito();
            } else {
                alert("❌ Error creando pedido: "+(data.error || "Intenta de nuevo."));
            }
        } catch(err){
            console.error(err);
            alert("⚠️ Error de conexión con el servidor.");
        }
    });
});




}); // cierre DOMContentLoaded


