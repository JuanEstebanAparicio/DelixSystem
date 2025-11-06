// DelixSystem/app/pages/pedidos/js/pedidos.js
// Controla el filtrado por áreas, polling para refrescar y persistencia del filtro
(function(){
  const AREA_KEY = 'pedidos_area_actual';
  const POLL_INTERVAL = 6000; // 6s

  const navAreas = document.querySelectorAll('.nav-area');
  const ordersGridSelector = '#ordersGridContainer .orders-grid';
  const ordersGrid = document.querySelector(ordersGridSelector);

  // Estado
  let areaActual = localStorage.getItem(AREA_KEY) || 'all';
  let pollingTimer = null;

  // Helper: aplicar clase active en botones de nav
  function setActiveButton() {
    document.querySelectorAll('.nav-area').forEach(btn => {
      if (btn.dataset.area === areaActual) btn.classList.add('active');
      else btn.classList.remove('active');
    });
  }

  // Filtrar pedidos en DOM por data-area
  function filtrarPedidosEnDOM() {
    const pedidos = document.querySelectorAll('#ordersGridContainer .order-card');
    pedidos.forEach(p => {
      const a = (p.dataset.area || '').toLowerCase();
      if (areaActual === 'all' || a === areaActual) {
        p.style.display = '';
      } else {
        p.style.display = 'none';
      }
    });
  }

  // Re-attach events (if needed in el futuro)
  function attachNavEvents() {
    document.querySelectorAll('.nav-area').forEach(btn => {
      btn.addEventListener('click', () => {
        const newArea = btn.dataset.area || 'all';
        if (newArea === areaActual) return;
        areaActual = newArea;
        localStorage.setItem(AREA_KEY, areaActual);
        setActiveButton();
        filtrarPedidosEnDOM();
      });
    });
  }

  // Fetch partial grid HTML from server and replace innerHTML of .orders-grid
  async function refreshGridFromServer() {
    try {
      // fetch relative to current file: listar_pedidos.php?fetch=1
      const res = await fetch(window.location.pathname + '?fetch=1', {cache: 'no-store'});
      if (!res.ok) return;
      const html = await res.text();

      const container = document.querySelector('#ordersGridContainer');
      if (!container) return;

      // Replace .orders-grid content
      // build temporary element to parse
      const tmp = document.createElement('div');
      tmp.innerHTML = html.trim();

      // If server returned a .orders-grid wrapper, replace inner. If just article nodes, replace inner as well.
      const newGrid = tmp.querySelector('.orders-grid');
      if (newGrid) {
        // Replace current .orders-grid
        const oldGrid = container.querySelector('.orders-grid');
        if (oldGrid) {
          oldGrid.replaceWith(newGrid);
        } else {
          container.innerHTML = '';
          container.appendChild(newGrid);
        }
      } else {
        // server returned fragment (maybe only articles). We'll replace innerHTML
        const oldGrid = container.querySelector('.orders-grid');
        if (oldGrid) {
          oldGrid.innerHTML = tmp.innerHTML;
        } else {
          const wrapper = document.createElement('div');
          wrapper.className = 'orders-grid';
          wrapper.innerHTML = tmp.innerHTML;
          container.appendChild(wrapper);
        }
      }

      // After replacing, reapply filter & nav events
      setActiveButton();
      filtrarPedidosEnDOM();
    } catch (err) {
      // silent fail (network temporarily down)
      console.error('Error refrescando pedidos:', err);
    }
  }

  // Init
  function init() {
    // mark active button and attach events
    setActiveButton();
    attachNavEvents();

    // apply initial filter
    filtrarPedidosEnDOM();

    // Start polling
    pollingTimer = setInterval(async () => {
      await refreshGridFromServer();
    }, POLL_INTERVAL);
  }

  // Wait DOM loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
