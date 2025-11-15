document.addEventListener("DOMContentLoaded", () => {
    cargarHistorial();

    // Auto-refresh cada 15s
    setInterval(cargarHistorial, 15000);

    document.getElementById("btn-refresh").addEventListener("click", cargarHistorial);
    document.getElementById("btn-filtrar").addEventListener("click", cargarHistorial);
});

// =========================
// Cargar historial
// =========================
function cargarHistorial() {
    const filtros = {
        buscador: document.getElementById("search").value,
        accion: document.getElementById("filtro-accion").value,
        modulo: document.getElementById("filtro-modulo").value,
        desde: document.getElementById("fecha-desde").value,
        hasta: document.getElementById("fecha-hasta").value
    };

    fetch("api/historial/listar.php", {
        method: "POST",
        body: new URLSearchParams(filtros)
    })
    .then(r => r.json())
    .then(data => renderHistorial(data.registros))
    .catch(err => console.log("Error historial:", err));
}

// =========================
// Pintar tabla
// =========================
function renderHistorial(lista) {
    const tbody = document.getElementById("tabla-body");
    tbody.innerHTML = "";

    lista.forEach(item => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${item.fecha}</td>
            <td>${item.usuario}</td>
            <td>${item.modulo}</td>
            <td>${item.accion}</td>
            <td>${item.descripcion}</td>
            <td><button class="btn-detalle" data-id="${item.id}">👁️</button></td>
        `;

        tbody.appendChild(tr);
    });

    document.querySelectorAll(".btn-detalle").forEach(btn => {
        btn.addEventListener("click", abrirDetalle);
    });
}

// =========================
// Modal de detalle
// =========================
function abrirDetalle(e) {
    const id = e.target.dataset.id;

    fetch("api/historial/detalle.php", {
        method: "POST",
        body: new URLSearchParams({ id })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById("modal-body").innerHTML = `
            <b>Usuario:</b> ${data.usuario}<br>
            <b>Módulo:</b> ${data.modulo}<br>
            <b>Acción:</b> ${data.accion}<br><br>

            <h4>ANTES:</h4>
            <pre>${JSON.stringify(data.old, null, 2)}</pre>

            <h4>DESPUÉS:</h4>
            <pre>${JSON.stringify(data.new, null, 2)}</pre>
        `;

        document.getElementById("modal-detalle").style.display = "block";
    });
}

// Cerrar modal
document.querySelector(".close").onclick = () =>
    document.getElementById("modal-detalle").style.display = "none";
