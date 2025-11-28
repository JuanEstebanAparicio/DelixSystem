console.log("%c[INIT] pedidos.js cargado correctamente", "color: #4caf50; font-weight: bold;");

// ======================================================
//  ⚠ FLAG para evitar que el polling reemplace el pedido
// ======================================================
let modalAbierto = false;
let ultimoPedidoVisto = null;

// ======================================================
//  FILTROS, POLLING Y EVENTOS DE ÁREAS
// ======================================================
(function(){
  const AREA_KEY = 'pedidos_area_actual';
  const POLL_INTERVAL = 6000;

  const navAreas = document.querySelectorAll('.nav-area');
  const ordersGridSelector = '#ordersGridContainer .orders-grid';
  const ordersGrid = document.querySelector(ordersGridSelector);

  let areaActual = localStorage.getItem(AREA_KEY) || 'all';
  let pollingTimer = null;

  function setActiveButton() {
    document.querySelectorAll('.nav-area').forEach(btn => {
      if (btn.dataset.area === areaActual) btn.classList.add('active');
      else btn.classList.remove('active');
    });
  }

  function filtrarPedidosEnDOM() {
    console.log("[Filtro] Aplicando filtro DOM:", areaActual);
    const pedidos = document.querySelectorAll('#ordersGridContainer .order-card');
    pedidos.forEach(p => {
      const a = (p.dataset.area || '').toLowerCase();
      p.style.display = (areaActual === 'all' || a === areaActual) ? '' : 'none';
    });
  }

  function attachNavEvents() {
    document.querySelectorAll('.nav-area').forEach(btn => {
      btn.addEventListener('click', () => {
        const newArea = btn.dataset.area || 'all';
        if (newArea === areaActual) return;
        console.log("[Filtro] Área cambiada a:", newArea);
        areaActual = newArea;
        localStorage.setItem(AREA_KEY, areaActual);
        setActiveButton();
        filtrarPedidosEnDOM();
      });
    });
  }

  // =============================
  // 🔁 POLLING (Actualización)
  // =============================
  async function refreshGridFromServer() {

    if (modalAbierto) {
      console.log("%c[POLL] Saltado porque hay un modal abierto. Pedido:", "color: orange;", ultimoPedidoVisto);
      return; // no reescribir cuando el modal está abierto
    }

    try {
      console.log("%c[POLL] Actualizando grid...", "color: cyan;");

      const res = await fetch(window.location.pathname + '?fetch=1', {cache: 'no-store'});
      if (!res.ok) return;

      const html = await res.text();
      const container = document.querySelector('#ordersGridContainer');
      if (!container) return;

      const tmp = document.createElement('div');
      tmp.innerHTML = html.trim();

      const newGrid = tmp.querySelector('.orders-grid');
      const oldGrid = container.querySelector('.orders-grid');

      if (newGrid && oldGrid) {
        console.log("[POLL] Reemplazando grid interno");
        oldGrid.innerHTML = newGrid.innerHTML;
      } else {
        console.log("[POLL] Reemplazo completo del grid (fallback)");
        const wrapper = document.createElement('div');
        wrapper.className = 'orders-grid';
        wrapper.innerHTML = newGrid ? newGrid.innerHTML : tmp.innerHTML;
        container.innerHTML = '';
        container.appendChild(wrapper);
      }

      setActiveButton();
      filtrarPedidosEnDOM();
      console.log("%c[POLL] Grid actualizado", "color: lightgreen;");

    } catch (err) {
      console.error('Error refrescando pedidos:', err);
    }
  }

  function init() {
    console.log("%c[INIT] Inicializando filtro y polling", "color: #03a9f4;");
    setActiveButton();
    attachNavEvents();
    filtrarPedidosEnDOM();
    pollingTimer = setInterval(refreshGridFromServer, POLL_INTERVAL);
  }

  window.refreshOrdersGrid = refreshGridFromServer;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();


// ======================================================
//  🟩 MARCAR COMO PAGADO
// ======================================================
document.addEventListener("DOMContentLoaded", ()=>{
  const btn = document.getElementById("btnMarcarPagado");

  if(btn){
    btn.addEventListener("click", ()=>{
      console.log("[PAGO] Marcando como pagado id:", btn.dataset.id);

      fetch("../php/marcar_pagado.php",{
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"id="+btn.dataset.id
      })
      .then(r=>r.json())
      .then(data=>{
        if(data.ok){
          new Audio("/DelixSystem/public/audio/pagado.mp3").play();
          alert("Pedido marcado como pagado");

          if (typeof window.refreshOrdersGrid === 'function') {
            window.refreshOrdersGrid();
          } else {
            location.reload();
          }
        }
      });
    });
  }
});


// ======================================================
//  🟦 ABRIR MODAL VER PEDIDO
// ======================================================
document.addEventListener("click", async (e) => {

  const btn = e.target.closest(".verPedidoBtn");

  if (!btn){
    return;
  }

  const id = btn.dataset.id;
  console.log(`%c[MODAL] Click detectado en botón verPedidoBtn. ID=${id}`, "color: yellow; font-weight: bold;");

  modalAbierto = true;          // bloquear polling
  ultimoPedidoVisto = id;

  const modal = document.getElementById("modalVerPedido");
  const contenido = document.getElementById("modalPedidoContenido");

  contenido.innerHTML = "<div class='loading-state'><div class='spinner'></div><p>Cargando detalles del pedido...</p></div>";
  modal.classList.remove("hidden");

  console.log("[MODAL] Fetch a ver_pedido.php?id=" + id);

  const res = await fetch(`../php/ver_pedido.php?id=${id}`);
  const html = await res.text();

  console.log("[MODAL] Respuesta recibida, renderizando...");
  contenido.innerHTML = html;
});


// ======================================================
//  🟥 CERRAR MODAL
// ======================================================
const cerrarBtn = document.getElementById("cerrarModalPedido");
const cerrarBtnFooter = document.getElementById("cerrarModalPedidoFooter");

function cerrarModal() {
    console.log("%c[MODAL] Cerrando modal", "color: red;");
    modalAbierto = false;   // permitir polling otra vez
    ultimoPedidoVisto = null;
    document.getElementById("modalVerPedido").classList.add("hidden");
}

if (cerrarBtn) {
    cerrarBtn.addEventListener("click", cerrarModal);
}

if (cerrarBtnFooter) {
    cerrarBtnFooter.addEventListener("click", cerrarModal);
}

// Cerrar al hacer click fuera del modal
document.addEventListener("click", (e) => {
    const modal = document.getElementById("modalVerPedido");
    if (e.target === modal) {
        cerrarModal();
    }
});
