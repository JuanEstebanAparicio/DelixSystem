document.addEventListener("DOMContentLoaded", () => {

    // ==== FILTRO CATEGORIAS NAV ====
document.querySelectorAll(".nav-cat").forEach(btn=>{
    btn.addEventListener("click", ()=>{
        document.querySelectorAll(".nav-cat").forEach(b=>b.classList.remove("active"));
        btn.classList.add("active");

        const cat = btn.dataset.cat;

        document.querySelectorAll(".platillo-card").forEach(card=>{
            if(cat === "all"){ 
                card.style.display = "block";
            } else {
                card.style.display = (card.dataset.cat === cat) ? "block" : "none";
            }
        });
    });
});


    // ====== APP INFO ======
    const APP = {
        id_user: parseInt(document.body.dataset.id_user),
        restaurant_name: document.body.dataset.restaurant_name,
        id_area: parseInt(document.body.dataset.id_area),
        area: document.body.dataset.area,
        id_mesa: parseInt(document.body.dataset.id_mesa),
        mesa: document.body.dataset.mesa,
        nombre_cliente: localStorage.getItem("nombre_cliente") || null
    };

    // ====== REFRESCAR PLATOS ======
async function refrescarPlatos(){
    try{
        const r = await fetch(`/DelixSystem/app/api/get_dishes.php?u=${APP.id_user}`);
        const text = await r.text();  // <-- primero leer texto

        if(!text.trim()) return; // evita error cuando viene vacío

        const data = JSON.parse(text);
        renderPlatos(data);
        function renderPlatos(data){
    const cont = document.getElementById("contenedorPlatillos");
    if(!cont) return;

    cont.innerHTML = "";

    data.forEach(p => {
        cont.innerHTML += renderDishCard(p);
    });

    attachAddCartEvents(); // << para reactivar eventos a los botones nuevos renderizados
}

    }catch(e){
        console.error("Error refresco platos:",e);
    }
}

function renderDishCard(p){
    return `
    <div class="platillo-card" data-cat="${p.category}">
        <div class="img-box"
            onclick="openDishModal('${p.name_dish.replace(/'/g,"\\'")}', '${p.description?.replace(/'/g,"\\'") || ''}', '${p.photo}', '${p.price}', '${p.id}')">
            <img src="/DelixSystem/app/pages/dishes_manager/${p.photo}" alt="${p.name_dish}">
        </div>

        <div class="info-box">
            <h3>${p.name_dish}</h3>

            ${p.description ? `<p class="dish-desc">${p.description}</p>` : ""}

            <p class="price">$${new Intl.NumberFormat().format(p.price)}</p>

            <button class="add-btn"
                data-id="${p.id}"
                data-nombre="${p.name_dish}"
                data-precio="${p.price}"
            >
                Agregar al carrito
            </button>
        </div>
    </div>`;
}

function attachAddCartEvents(){
    document.querySelectorAll(".add-btn").forEach(btn=>{
        btn.onclick = ()=>{
            const id = parseInt(btn.dataset.id);
            const nombre = btn.dataset.nombre;
            const precio = parseFloat(btn.dataset.precio);

            const existente = carrito.find(item => item.id === id);
            if (existente) existente.cantidad++;
            else carrito.push({ id, nombre, precio, cantidad: 1 });

            // animación carrito
            const img = btn.closest(".platillo-card").querySelector("img");
            const imgClone = img.cloneNode(true);
            const rect = img.getBoundingClientRect();
            imgClone.style.position = "absolute";
            imgClone.style.width = "80px";
            imgClone.style.zIndex = "9999";
            imgClone.style.left = rect.left+"px";
            imgClone.style.top = rect.top+"px";
            document.body.appendChild(imgClone);
            const cartPos = document.getElementById("verCarritoBtn").getBoundingClientRect();
            imgClone.animate([
                { transform:`translate(0,0)`, opacity:1 },
                { transform:`translate(${cartPos.left-rect.left}px, ${cartPos.top-rect.top}px) scale(0.2)`, opacity:0 }
            ],{
                duration:600,
                easing:"ease-in-out"
            }).onfinish = ()=> imgClone.remove();

            actualizarContadorCarrito();
            actualizarCarrito();
        }
    })
}




// iniciamos intervalo
setInterval(refrescarPlatos, 6000);


    // ====== CARRITO ======
    let carrito = [];
    const carritoBtn = document.getElementById("verCarritoBtn");
    const carritoModal = document.getElementById("carritoModal");
    const carritoLista = document.getElementById("carritoLista");
    const totalCarrito = document.getElementById("totalCarrito");
    const cerrarCarrito = document.getElementById("cerrarCarrito");
    const badgeCarrito = document.getElementById("cartCount");

    // ====== FUNCIONES AUXILIARES ======
    function actualizarContadorCarrito(){
        let totalCantidad = carrito.reduce((acc,i)=> acc + i.cantidad, 0);
        badgeCarrito.textContent = totalCantidad;
        badgeCarrito.classList.add("bump");
        setTimeout(()=> badgeCarrito.classList.remove("bump"), 250);
    }

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
                if (carrito[index] && carrito[index].cantidad <= 0) carrito.splice(index, 1);
                actualizarCarrito();
                actualizarContadorCarrito();
            });
        });
    }


    // ====== MOSTRAR / OCULTAR CARRITO ======
    carritoBtn.onclick = () => carritoModal.style.display = "block";
    cerrarCarrito.onclick = () => carritoModal.style.display = "none";

    // ====== GUARDAR CLIENTE ======
    const guardarClienteBtn = document.getElementById("guardarClienteBtn");
    const nombreClienteHeader = document.getElementById("nombreClienteHeader");

    if(APP.nombre_cliente && nombreClienteHeader){
        nombreClienteHeader.textContent = `Cliente: ${APP.nombre_cliente}`;
    }

    // si ya tiene nombre guardado -> esconder modal cliente automáticamente al cargar
const modalCliente = document.getElementById("clienteLoginModal");
if(APP.nombre_cliente && modalCliente){
    modalCliente.style.display = "none";
}


    if (guardarClienteBtn) {
        guardarClienteBtn.addEventListener("click", () => {
            const input = document.getElementById("nombreCliente");
            const nombre = input.value.trim();
            if(nombre === ""){
                input.classList.add("input-error");
                const original = input.placeholder;
                input.placeholder = "Escribe tu nombre";
                input.value = "";
                setTimeout(() => {
                    input.classList.remove("input-error");
                    input.placeholder = original;
                },300);
                return;
            }
            localStorage.setItem("nombre_cliente", nombre);
            APP.nombre_cliente = nombre;
            if(nombreClienteHeader) nombreClienteHeader.textContent = `Cliente: ${nombre}`;
            const modal = document.getElementById("clienteLoginModal");
            if(modal){
                modal.style.opacity = 0;
                setTimeout(()=> modal.style.display="none",300);
            }
        });
    }

   
   // ====== FUNCION CREAR PEDIDO ======
async function crearPedido(metodo_pago, pagado = false){
    if(carrito.length === 0) return Swal.fire("🛒 Carrito vacío.");

    const body = {
        id_user: APP.id_user,
        restaurant_name: APP.restaurant_name,
        id_area: APP.id_area,
        area: APP.area,
        id_mesa: APP.id_mesa,
        mesa: APP.mesa,
        nombre_cliente: APP.nombre_cliente,
        total: carrito.reduce((a,i)=>a+(i.precio*i.cantidad),0),
        metodo_pago: metodo_pago,
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
            body: JSON.stringify(body)
        });
        const data = await res.json();
        if(data.success){
            carrito = [];
            actualizarCarrito();
            actualizarContadorCarrito();
            return true;
        } else {
            Swal.fire("❌ Error creando pedido", data.error || "Intenta de nuevo.", "error");
            return false;
        }
    } catch(err){
        console.error(err);
        Swal.fire("⚠️ Error de conexión", "No se pudo procesar el pedido.", "error");
        return false;
    }
}


    // ====== MODAL SIMULACION TARJETA ======
    const sheet = document.getElementById("bottomSheetPago");
    const simulacionTarjetaModal = document.getElementById("simulacionTarjetaModal");
    const totalTarjetaSpan = document.getElementById("totalTarjeta");
    const confirmarPagoTarjetaBtn = document.getElementById("confirmarPagoTarjetaBtn");
    const cancelarPagoTarjeta = document.getElementById("cancelarPagoTarjeta");

    // Abrir bottom sheet pago
    const pagarBtn = document.getElementById("pagarBtn");
    pagarBtn.onclick = () => {
        if(carrito.length === 0) return Swal.fire("🛒 Carrito vacío.");
        carritoModal.style.display="none";
        sheet.classList.add("show");
    };

    // Listener selección método pago
    document.querySelectorAll(".bs-item").forEach(b => {
        b.addEventListener("click", async () => {
            const metodo = b.dataset.metodo;
            sheet.classList.remove("show");

            if(carrito.length === 0) return Swal.fire("🛒 Carrito vacío.");

            if(metodo === "tarjeta"){
                totalTarjetaSpan.textContent = carrito.reduce((a,i)=>a+(i.precio*i.cantidad),0).toLocaleString();
                simulacionTarjetaModal.style.display = "block";
            } else {
                const ok = await crearPedido(metodo, false);
                if(ok) Swal.fire("✅ Pedido creado!", "Método: "+metodo, "success");
            }
        });
    });

    cancelarPagoTarjeta.onclick = () => simulacionTarjetaModal.style.display="none";

    confirmarPagoTarjetaBtn.onclick = async () => {
        simulacionTarjetaModal.style.display = "none";
        const ok = await crearPedido("tarjeta", true);
        if(ok) Swal.fire("✅ Pago exitoso", "Tu pedido ha sido pagado correctamente.", "success");
    };



});


// ====== MODAL INFO PLATO ======
function openDishModal(name, desc, photo, price, id){
    document.getElementById("dishModalImg").src = "/DelixSystem/app/pages/dishes_manager/" + photo;
    document.getElementById("dishModalName").innerText = name;
    document.getElementById("dishModalDesc").innerText = desc || "Sin descripción";
    document.getElementById("dishModalPrice").innerText = price;

    const btn = document.getElementById("dishModalAdd");
    btn.dataset.id = id;
    btn.dataset.nombre = name;
    btn.dataset.precio = price;

    document.getElementById("dishModal").style.display = "block";
}

function closeDishModal(){
    document.getElementById("dishModal").style.display = "none";
}

