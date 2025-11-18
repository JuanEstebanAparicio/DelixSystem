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
    // 📌 EMPLEADOS
    // ======================
    empleados: {
        full_name: "Nombre Completo",
        email: "Correo Electrónico",
        document: "Documento",
        is_online: "Estado en Línea",
        user_id: "Propietario",
        // Campos de auditoría de registro / eliminación
        empleado_id: "ID del Empleado",
        // Para asignación de roles
        roles: "Roles Asignados"
    },
    roles: {
    roles: "Roles Asignados",
    empleado_id: "ID del Empleado"
},

// ======================
// 📦 INVENTARIO (STORAGE)
// ======================
inventario: {
    name: "Nombre del Ingrediente",
    category: "Categoría",
    amount: "Cantidad",
    minimum_quantity: "Cantidad Mínima",
    unit: "Unidad de Medida",
    unit_cost: "Costo por Unidad",
    entrance_date: "Fecha de Ingreso",
    expiration_date: "Fecha de Vencimiento",
    batch: "Lote",
    description: "Descripción",
    location: "Ubicación en Almacén",
    state: "Estado",
    supplier: "Proveedor",
    photo: "Fotografía",
    
    // Identificación
    id: "ID del Ingrediente",
    target_id: "ID del Ingrediente",

    // Auditoría
    old: "Datos Anteriores",
    new: "Datos Nuevos"
}



};

// ===========================================
// 🔍 Detectar solo los campos modificados
// ===========================================
window.detectarCambios = function (oldObj, newObj) {
    const cambios = {};

    for (const key in newObj) {
        if (!oldObj || oldObj[key] !== newObj[key]) {
            cambios[key] = newObj[key];
        }
    }

    return cambios;
};
// ==========================================================
// 🎯 Filtra SOLO los cambios del inventario antes de mostrar
// ==========================================================
window.prepararDatosInventario = function (oldObj, newObj) {
    // Caso crear → no hay OLD
    if (!oldObj) return newObj;

    // Detectar solo cambios reales
    return window.detectarCambios(oldObj, newObj);
};


// ==============================
// 📘 Formateo de objetos
// ==============================
window.formatearObjetoAuditoria = function (obj, gestor) {
    if (!obj) return "<i>Sin datos</i>";

    const dicGestor = window.diccionarioPorGestor[gestor] || {};
    let html = `<ul class="list-disc ml-4">`;

    for (const key in obj) {
        const etiqueta =
            dicGestor[key] ||
            window.diccionarioGeneral[key] ||
            null;

        if (!etiqueta) continue;

        let valor = obj[key];

        // ============================================
        // 🎯 FORMATO ESPECIAL PARA ROLES
        // ============================================
        if (key === "roles" && Array.isArray(valor)) {
            if (valor.length === 0) {
                html += `<li><b>${etiqueta}:</b> <i>Sin roles asignados</i></li>`;
            } else {
                let listaRoles = "<ul style='margin-left:15px'>";
                valor.forEach(r => {
                    // Si viene como {id, nombre}
                    if (typeof r === "object" && r.nombre) {
                        listaRoles += `<li>${r.nombre}</li>`;
                    }
                    // Si solo es un número ID (caso viejo)
                    else {
                        listaRoles += `<li>Rol #${r}</li>`;
                    }
                });
                listaRoles += "</ul>";

                html += `
                    <li>
                        <b>${etiqueta}:</b><br>
                        ${listaRoles}
                    </li>
                `;
            }

            continue;
        }

        // ============================================
        // 📌 Formato estándar
        // ============================================
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

