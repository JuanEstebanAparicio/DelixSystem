// ==============================
// 📘 Diccionario General (solo lo útil, nada técnico)
// ==============================
window.diccionarioGeneral = {
    nombre: "Nombre",
    created_at: "Fecha de creación",
    updated_at: "Última actualización",
    status: "Estado",
    gestor: "Gestor",
    action: "Acción"
};

// ==============================
// 📘 Diccionarios por Gestor
// ==============================
window.diccionarioPorGestor = {
    
    // ======================
    // 📌 MESAS
    // ======================
    mesas: {
        nombre: "Nombre de la Mesa",
        area_nombre: "Área",          // ✔ Mejor que id_area
        orden: "Orden",
        restaurante: "Restaurante"
    },

    // ======================
    // 📌 AREAS
    // ======================
    areas: {
        nombre: "Nombre del Área",
        orden: "Orden",
        propietario_nombre: "Propietario", // ✔ No id_usuario
        restaurante: "Restaurante"
    },

    // ======================
    // 📌 PRODUCTOS
    // ======================
    productos: {
        nombre: "Nombre del Producto",
        precio: "Precio",
        categoria: "Categoría",
        stock: "Stock disponible"
    },

    // ======================
    // 📌 PEDIDOS
    // ======================
    pedidos: {
        total: "Total",
        estado: "Estado del pedido",
        items: "Artículos"
    }
};

// ==============================
// 📘 Formateo de objetos
// ==============================
window.formatearObjetoAuditoria = function (obj, gestor) {
    if (!obj) return "<i>Sin datos</i>";

    const dicGestor = window.diccionarioPorGestor[gestor] || {};

    let html = `<ul class="list-disc ml-4">`;

    for (const key in obj) {

        // Obtener nombre legible
        const etiqueta =
            dicGestor[key] ||
            window.diccionarioGeneral[key] ||
            null;

        // ❌ Si no existe etiqueta, NO mostrar esta clave
        if (!etiqueta) continue;

        let valor = obj[key];

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
};
