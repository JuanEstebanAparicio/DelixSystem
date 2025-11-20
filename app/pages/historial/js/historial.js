// ================================
//  HISTORIAL.JS (OPTIMIZADO)
// ================================

// URL del controlador
const API_URL = "../php/HistorialController.php?action=list";

// Referencias DOM
const tablaBody = document.querySelector("#tablaHistorial tbody");
const filtroUsuario = document.getElementById("filtroUsuario");
const filtroGestor = document.getElementById("filtroGestor");
const filtroAccion = document.getElementById("filtroAccion");
const filtroFecha = document.getElementById("filtroFecha");
const btnFiltrar = document.getElementById("btnFiltrar");
const btnReset = document.getElementById("btnReset");

// ========================================
//  ESTADO GLOBAL CONTROLADO
// ========================================
const STATE = {
    datos: [],
    filtros: {
        usuario: "",
        gestor: "",
        accion: "",
        fecha: ""
    }
};

// ========================================
//  BOTÓN RESET (solo se agrega una vez)
// ========================================
btnReset.addEventListener("click", () => {
    STATE.filtros = { usuario: "", gestor: "", accion: "", fecha: "" };

    filtroUsuario.value = "";
    filtroGestor.value = "";
    filtroAccion.value = "";
    filtroFecha.value = "";

    renderTabla(STATE.datos);
});

// ================================
//  CARGAR AUDITORÍAS
// ================================
async function cargarAuditorias() {
    try {
        const res = await fetch(API_URL);
        const json = await res.json();

        if (!json.status) {
            console.error("Error del servidor:", json.error);
            return;
        }

        const datos = json.data;
        STATE.datos = datos;

        const hayFiltros =
            STATE.filtros.usuario ||
            STATE.filtros.gestor ||
            STATE.filtros.accion ||
            STATE.filtros.fecha;

        // 🔥 Solo regeneramos selects si NO hay filtros activos
        if (!hayFiltros) {
            llenarSelectUsuarios(datos);
            llenarSelectGestores(datos);
            llenarSelectAcciones(datos);
        }

        // 🔥 Si había filtros activos → restaurarlos
        if (hayFiltros) {
            filtroUsuario.value = STATE.filtros.usuario;
            filtroGestor.value = STATE.filtros.gestor;
            filtroAccion.value = STATE.filtros.accion;
            filtroFecha.value = STATE.filtros.fecha;

            aplicarFiltros();
        } else {
            renderTabla(datos);
        }

    } catch (error) {
        console.error("Error fetch:", error);
    }
}

// ================================
//  FILTRAR DATOS
// ================================
function aplicarFiltros() {

    const datos = STATE.datos;

    const usuario = STATE.filtros.usuario;
    const gestor = STATE.filtros.gestor;
    const accion = STATE.filtros.accion;
    const fecha = STATE.filtros.fecha;

    const filtrado = datos.filter(row => {
        const rowFecha = row.created_at.substring(0, 10);

        const usuarioMatch =
            usuario === "" ||
            (row.usuario_nombre &&
                row.usuario_nombre.toLowerCase().includes(usuario.toLowerCase()));

        const gestorMatch = gestor === "" || row.gestor === gestor;
        const accionMatch = accion === "" || row.action === accion;
        const fechaMatch = fecha === "" || rowFecha === fecha;

        return usuarioMatch && gestorMatch && accionMatch && fechaMatch;
    });

    renderTabla(filtrado);
}

// Listener del botón filtrar (solo 1 vez)
btnFiltrar.addEventListener("click", () => {
    STATE.filtros = {
        usuario: filtroUsuario.value.trim(),
        gestor: filtroGestor.value.trim(),
        accion: filtroAccion.value.trim(),
        fecha: filtroFecha.value.trim()
    };

    aplicarFiltros();
});

// ================================
//  RENDERIZAR TABLA
// ================================
function renderTabla(data) {
    tablaBody.innerHTML = "";

    data.forEach(row => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${row.usuario_nombre ?? "—"}</td>
            <td>${row.gestor}</td>
            <td>${row.action}</td>
            <td>
                <button class="detalles-btn" onclick='verDetalles(${JSON.stringify(row).replace(/'/g, "&#39;")})'>
                    Ver
                </button>
            </td>
            <td>${formatearFecha(row.created_at)}</td>
        `;

        tablaBody.appendChild(tr);
    });
}

// ================================
//  SELECTS DINÁMICOS
// ================================
function llenarSelect(select, items) {
    select.innerHTML = `<option value="">Todos</option>`;
    items.forEach(item => {
        const op = document.createElement("option");
        op.value = item;
        op.textContent = item.charAt(0).toUpperCase() + item.slice(1);
        select.appendChild(op);
    });
}

function llenarSelectUsuarios(data) {
    const setUsuarios = new Set();
    data.forEach(row => {
        let nombre = row.usuario_nombre || row.actor_name || row.usuario || null;
        if (nombre) setUsuarios.add(nombre);
    });
    llenarSelect(filtroUsuario, [...setUsuarios]);
}

function llenarSelectGestores(data) {
    const gestores = [...new Set(data.map(row => row.gestor).filter(Boolean))];
    llenarSelect(filtroGestor, gestores);
}

function llenarSelectAcciones(data) {
    const acciones = [...new Set(data.map(row => row.action).filter(Boolean))];
    llenarSelect(filtroAccion, acciones);
}

// ================================
//  MODAL DE DETALLES
// ================================
function verDetalles(log) {
    const gestor = log.gestor;

    const parseSafe = (value) => {
        if (!value) return null;
        if (typeof value === "object") return value;
        try { return JSON.parse(value); } catch { return null; }
    };

    const oldData = parseSafe(log.old);
    const newData = parseSafe(log.new);

    let oldRender = window.formatearObjetoAuditoria(oldData, gestor);
    let newRender = window.formatearObjetoAuditoria(newData, gestor);

    if (gestor === "inventario") {
        if (!oldData) {
            oldRender = "<i>Sin datos</i>";
        } else {
            const cambios = window.prepararDatosInventario(oldData, newData);
            newRender = Object.keys(cambios).length
                ? window.formatearObjetoAuditoria(cambios, gestor)
                : "<i>No hubo cambios</i>";
        }
    }

    Swal.fire({
        title: `Detalles del Registro`,
        html: `
            <div style="text-align:left">
                <p><b>Usuario:</b> ${log.usuario_nombre || "Desconocido"}</p>
                <p><b>Tipo de Usuario:</b> ${log.actor_type || "N/D"}</p>
                <p><b>Gestor:</b> ${gestor}</p>
                <p><b>Acción:</b> ${log.action}</p>
                <p><b>Estado:</b> ${log.status}</p>
                <p><b>Fecha:</b> ${new Date(log.created_at).toLocaleString()}</p>

                <hr>

                <h3>🔵 Antes (OLD)</h3>
                <div style="padding:12px; border:1px solid #ccc; background:#fafafa; border-radius:10px; margin-bottom:15px;">
                    ${oldRender}
                </div>

                <h3>🟢 Después (NEW)</h3>
                <div style="padding:12px; border:1px solid #4caf50; background:#f0fff4; border-radius:10px;">
                    ${newRender}
                </div>
            </div>
        `,
        width: "750px",
        confirmButtonText: "Cerrar"
    });
}

// ================================
//  FORMATEAR FECHA
// ================================
function formatearFecha(f) {
    return new Date(f).toLocaleString("es-VE", { hour12: true });
}

// ================================
//  TEMA OSCURO / CLARO
// ================================
document.getElementById("themeSwitch").addEventListener("change", e => {
    document.body.classList.toggle("light", e.target.checked);
});

// ================================
//  AUTO CARGA
// ================================
cargarAuditorias();
setInterval(cargarAuditorias, 30000);
