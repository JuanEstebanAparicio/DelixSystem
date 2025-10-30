// ✅ roles_auto_update.js
async function actualizarRoles() {
  try {
    const res = await fetch('/DelixSystem/app/pages/dashboard_empleado/php/get_roles.php');
    if (!res.ok) throw new Error('Error al obtener roles');
    const data = await res.json();

    const container = document.getElementById('rolesContainer');
    if (!container) return;

    container.innerHTML = '';

    if (data.roles && data.roles.length > 0) {
      data.roles.forEach(rol => {
        const span = document.createElement('span');
        span.className =
          "px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-700";
        span.textContent = rol.nombre;
        container.appendChild(span);
      });
    } else {
      container.innerHTML =
        '<span class="text-gray-500 text-sm">Sin roles asignados</span>';
    }
  } catch (err) {
    console.error('❌ Error actualizando roles:', err);
  }
}

// 🔁 Actualiza roles automáticamente cada 10 segundos
setInterval(actualizarRoles, 10000);

// 🔄 Refresca también al volver al foco
window.addEventListener('focus', actualizarRoles);

// 🚀 Ejecuta la primera carga apenas inicia
document.addEventListener('DOMContentLoaded', actualizarRoles);
