// =============================================================
// ⭐ HISTORIAL DEL SISTEMA — DelixSystem
// Carga dinámica + filtros + refresco automático
// =============================================================

// --- DOM Elements ---
const tablaBody = document.querySelector("#tablaHistorial tbody");
const filtroUsuario = document.querySelector("#filtroUsuario");
const filtroGestor = document.querySelector("#filtroGestor");
const filtroAccion = document.querySelector("#filtroAccion");
const filtroFecha = document.querySelector("#filtroFecha");
const btnFiltrar = document.querySelector("#btnFiltrar");

// URL del controlador
const API_URL = "../php/HistorialController.php?action=list";

// Datos en memoria
let auditorias = [];
let usuariosUnicos = new Set();

// Refrescar cada X segundos
const REFRESH_INTERVAL = 5000;


// =============================================================
// 🔥 1. Cargar auditorías desde el backend
// =============================================================
async function cargarAuditorias() {
    try {
        const res = await fetch(API_URL);
        const data = await res.json();

        if (!data.status) {
            console.error("Error al cargar auditorías:", data.error);
            return;
        }

        auditorias = data.data;

        poblarUsuarios();
        renderTabla();

    } catch (e) {
        console.error("Error fetch:", e);
    }
}


// =============================================================
// 👤 2. Poblar select de Usuario dinámicamente
// =============================================================
function poblarUsuarios() {
    usuariosUnicos.clear();
    filtroUsuario.innerHTML = `<option value="">Todos</option>`;

    auditorias.forEach(a => {
        if (a.actor_nombre) usuariosUnicos.add(a.actor_nombre);
    });

    usuariosUnicos.forEach(nombre => {
        const opt = document.createElement("option");
        opt.value = nombre;
        opt.textContent = nombre;
        filtroUsuario.appendChild(opt);
    });
}


// =============================================================
// 🎯 3. Filtros
// =============================================================
function filtrarAuditorias() {
    return auditorias.filter(a => {

        if (filtroUsuario.value && a.actor_nombre !== filtroUsuario.value) return false;
        if (filtroGestor.value && a.gestor !== filtroGestor.value) return false;
        if (filtroAccion.value && a.action !== filtroAccion.value) return false;

        if (filtroFecha.value) {
            const fechaAuditoria = a.created_at.split(" ")[0];
            if (fechaAuditoria !== filtroFecha.value) return false;
        }

        return true;
    });
}


// =============================================================
// 📊 4. Renderizar tabla
// =============================================================
function renderTabla() {
    tablaBody.innerHTML = ""; // limpiar

    const filtradas = filtrarAuditorias();

    filtradas.forEach(a => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${a.actor_nombre || "Usuario desconocido"}</td>
            <td>${a.gestor}</td>
            <td>${a.action}</td>
            <td><button class="detalles-btn" data-id="${a.id}">Ver detalles</button></td>
            <td>${a.created_at}</td>
        `;

        tablaBody.appendChild(tr);
    });

    activarBotonesDetalles();
}


// =============================================================
// 🔍 5. Ver detalles — Modal elegante
// =============================================================
function activarBotonesDetalles() {
    const botones = document.querySelectorAll(".detalles-btn");

    botones.forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.dataset.id;
            const item = auditorias.find(a => a.id == id);

            mostrarDetalles(item);
        });
    });
}


function mostrarDetalles(a) {
    Swal.fire({
        title: "📄 Detalles de la acción",
        html: `
            <div style="text-align:left; font-size:14px">
                <b>Usuario:</b> ${a.actor_nombre}<br>
                <b>Gestor:</b> ${a.gestor}<br>
                <b>Acción:</b> ${a.action}<br><br>

                <b>Datos antiguos:</b>
                <pre>${formatJSON(a.old)}</pre>

                <b>Datos nuevos:</b>
                <pre>${formatJSON(a.new)}</pre>

                <b>Metadata:</b>
                <pre>${formatJSON(a.meta)}</pre>
            </div>
        `,
        width: 650,
        background: "#131c33",
        color: "#f2f2f2",
        confirmButtonColor: "#ff9f43"
    });
}

function formatJSON(data) {
    if (!data) return "—";
    try {
        return JSON.stringify(JSON.parse(data), null, 2);
    } catch {
        return data;
    }
}


// =============================================================
// 🔄 6. Refresco automático (cada 5 segundos)
// =============================================================
setInterval(async () => {
    await cargarAuditorias();
    animarActualizacion();
}, REFRESH_INTERVAL);

function animarActualizacion() {
    tablaBody.style.opacity = 0;
    setTimeout(() => {
        tablaBody.style.opacity = 1;
    }, 200);
}


// =============================================================
// ⏯️ 7. Botón aplicar filtros
// =============================================================
btnFiltrar.addEventListener("click", () => {
    renderTabla();
});


// =============================================================
// 🚀 Inicializar
// =============================================================
cargarAuditorias();
