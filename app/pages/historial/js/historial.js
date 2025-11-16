// ================================
//  HISTORIAL.JS
// ================================
const diccionario = window.auditoriaDiccionario;

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

        // llenar selecciones dinámicas
        llenarSelectUsuarios(datos);

        // renderizar tabla
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
            <td>${row.actor_nombre ?? "—"}</td>
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
    // limpiar excepto "Todos"
    filtroUsuario.innerHTML = `<option value="">Todos</option>`;

    const unicos = new Set();

    data.forEach(row => {
        if (row.actor_nombre) unicos.add(row.actor_nombre);
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
            (usuario === "" || row.actor_nombre === usuario) &&
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
function prettyJSON(data) {
    if (!data || data === "null") return "<i>Sin datos</i>";

    try {
        const parsed = typeof data === "string" ? JSON.parse(data) : data;
        return `<pre>${JSON.stringify(parsed, null, 2)}</pre>`;
    } catch (e) {
        return `<pre>${data}</pre>`;
    }
}

function verDetalles(log) {
    const gestor = log.gestor;

    // 🔥 Si old/new/meta vienen como strings, intentar parsear; si ya son objetos, no hacer nada
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
    const metaData = parseSafe(log.meta);

    Swal.fire({
        title: `Detalles del Registro`,
        html: `
            <div style="text-align:left">

                <p><b>Usuario:</b> ${log.usuario_nombre || "Desconocido"}</p>
                <p><b>Gestor:</b> ${gestor}</p>
                <p><b>Acción:</b> ${log.action}</p>
                <p><b>Estado:</b> ${log.status}</p>
                <p><b>Fecha:</b> ${new Date(log.created_at).toLocaleString()}</p>

                <hr>

                <p><b>Antes (OLD):</b></p>
                ${formatearObjetoAuditoria(oldData, gestor)}

                <p><b>Después (NEW):</b></p>
                ${formatearObjetoAuditoria(newData, gestor)}

                <p><b>Meta (Detalles técnicos):</b></p>
                ${formatearObjetoAuditoria(metaData, gestor)}

            </div>
        `,
        width: "700px",
        confirmButtonText: "Cerrar"
    });
}


function formatearObjetoAuditoria(obj, gestor) {
    if (!obj || typeof obj !== "object") {
        return `<p style="color:#999">Sin datos</p>`;
    }

    // Verifica si existe un diccionario para este gestor
    const dic = diccionarioAuditoria[gestor] || {};

    let html = `<ul style="margin-left:15px">`;

    for (const key in obj) {
        const nombreLegible = dic[key] || key; // fallback por si la clave no existe
        const valor = obj[key];

        if (typeof valor === "object" && valor !== null) {
            html += `
                <li><b>${nombreLegible}:</b>
                    <ul style="margin-left:15px">
                        ${Object.entries(valor)
                            .map(([k, v]) => {
                                const subName = dic[k] || k;
                                return `<li><b>${subName}:</b> ${v}</li>`;
                            })
                            .join("")}
                    </ul>
                </li>
            `;
        } else {
            html += `<li><b>${nombreLegible}:</b> ${valor}</li>`;
        }
    }

    html += `</ul>`;
    return html;
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

// refrescar cada 30s (opcional)
setInterval(cargarAuditorias, 30000);
