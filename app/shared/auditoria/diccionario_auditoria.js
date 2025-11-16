// ==============================
// 📘 Diccionario General
// ==============================
const diccionarioGeneral = {
    id: "ID",
    nombre: "Nombre",
    created_at: "Fecha de creación",
    updated_at: "Última actualización",
    status: "Estado",
    id_usuario: "ID del propietario",
    actor_id: "ID del actor",
    actor_type: "Tipo de usuario",
    gestor: "Gestor",
    action: "Acción",
    target_table: "Tabla afectada",
    target_id: "ID afectado"
};

// ==============================
// 📘 Diccionarios por Gestor
// ==============================
const diccionarioPorGestor = {
    mesas: {
    id_mesa: "ID de la Mesa",
    nombre: "Nombre de la Mesa",
    id_area: "ID del Área",
    restaurant_name: "Restaurante",
    orden: "Orden",
},

areas: {
    id_area: "ID del Área",
    nombre: "Nombre del Área",
    orden: "Orden",
    id_usuario: "Propietario",
    restaurant_name: "Restaurante"
},
    productos: {
        id_producto: "ID del Producto",
        precio: "Precio",
        categoria: "Categoría",
        stock: "Stock disponible"
    },

    pedidos: {
        id_pedido: "ID del Pedido",
        total: "Total",
        estado: "Estado del pedido",
        items: "Artículos"
    }

    // Aquí se irán agregando más gestores…
};


// ==============================
// 📘 Función para formateo
// ==============================
function formatearObjetoAuditoria(obj, gestor) {
    if (!obj) return "<i>Sin datos</i>";

    const dicGestor = diccionarioPorGestor[gestor] || {};

    let html = `<ul class="list-disc ml-4">`;

    for (const key in obj) {
        const etiqueta =
            dicGestor[key] ||
            diccionarioGeneral[key] ||
            key; // fallback

        let valor = obj[key];

        // Formateo bonito para objetos y arrays
        if (typeof valor === "object" && valor !== null) {
            valor = JSON.stringify(valor, null, 2)
                .replace(/\n/g, "<br>")
                .replace(/  /g, "&nbsp;&nbsp;");
        }

        html += `
            <li>
                <b>${etiqueta}:</b> ${valor}
            </li>
        `;
    }

    html += "</ul>";

    return html;
}

// ==============================
// 📘 Exportar
// ==============================
export { diccionarioGeneral, diccionarioPorGestor, formatearObjetoAuditoria };
