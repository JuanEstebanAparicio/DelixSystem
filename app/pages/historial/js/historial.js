// ================================
//  HISTORIAL.JS
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

        llenarSelectUsuarios(datos);
        renderTabla(datos);

        window.__AUDIT_DATA__ = datos; // cache global
    } catch (error) {
        console.error("Error fetch:", error);
    }
}

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
//  LLENAR SELECT DE USUARIOS
// ================================
function llenarSelectUsuarios(data) {
    filtroUsuario.innerHTML = `<option value="">Todos</option>`;

    const unicos = new Set();

    data.forEach(row => {
        if (row.usuario_nombre) unicos.add(row.usuario_nombre);
    });

    unicos.forEach(nombre => {
        const op = document.createElement("option");
        op.value = nombre;
        op.textContent = nombre;
        filtroUsuario.appendChild(op);
    });
}

// ================================
//  FILTRAR DATOS
// ================================
btnFiltrar.addEventListener("click", () => {
    const datos = window.__AUDIT_DATA__ ?? [];

    const usuario = filtroUsuario.value.trim();
    const gestor = filtroGestor.value.trim();
    const accion = filtroAccion.value.trim();
    const fecha = filtroFecha.value.trim();

    const filtrado = datos.filter(row => {
        const rowFecha = row.created_at.substring(0, 10);

        return (
            (usuario === "" || row.usuario_nombre === usuario) &&
            (gestor === "" || row.gestor === gestor) &&
            (accion === "" || row.action === accion) &&
            (fecha === "" || rowFecha === fecha)
        );
    });

    renderTabla(filtrado);
});

// ================================
//  MODAL DE DETALLES
// ================================
function verDetalles(log) {
    const gestor = log.gestor;

    const parseSafe = (value) => {
        if (!value) return null;
        if (typeof value === "object") return value;
        try {
            return JSON.parse(value);
        } catch {
            return null;
        }
    };

    const oldData = parseSafe(log.old);
    const newData = parseSafe(log.new);

    // ================================================
    // 🟩 RENDER BÁSICO
    // ================================================
    let oldRender = window.formatearObjetoAuditoria(oldData, gestor);
    let newRender = window.formatearObjetoAuditoria(newData, gestor);

    // ================================================
    // 🟦 LÓGICA ESPECIAL PARA INVENTARIO
    // ================================================
    if (gestor === "inventario") {

        if (!oldData) {
            // CREAR → mostrar todo NEW
            oldRender = "<i>Sin datos</i>";
            newRender = window.formatearObjetoAuditoria(newData, gestor);
        } else {
            // EDITAR → mostrar solo cambios
            const cambios = window.prepararDatosInventario(oldData, newData);

            oldRender = window.formatearObjetoAuditoria(oldData, gestor);
            newRender = window.formatearObjetoAuditoria(cambios, gestor);

            if (Object.keys(cambios).length === 0) {
                newRender = "<i>No hubo cambios</i>";
            }
        }
    }

    // ================================================
    // 🎨 ESTILOS ELEGANTES
    // ================================================
    const OLD_BOX = `
        <div style="
            padding:12px;
            border:1px solid #ccc;
            background:#fafafa;
            border-radius:10px;
            margin-bottom:15px;
        ">
            ${oldRender}
        </div>
    `;

    const NEW_BOX = `
        <div style="
            padding:12px;
            border:1px solid #4caf50;
            background:#f0fff4;
            border-radius:10px;
        ">
            ${newRender}
        </div>
    `;

    // ================================================
    // 🟧 MOSTRAR MODAL FINAL
    // ================================================
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

                <h3 style="margin-bottom:6px; color:#333;">🔵 Antes (OLD)</h3>
                ${OLD_BOX}

                <h3 style="margin-bottom:6px; color:#333;">🟢 Después (NEW)</h3>
                ${NEW_BOX}

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
    const d = new Date(f);
    return d.toLocaleString("es-VE", { hour12: true });
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
