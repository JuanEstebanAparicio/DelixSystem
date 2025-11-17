
document.addEventListener("DOMContentLoaded", () => {
    const navItems = document.querySelectorAll(".nav-item");
    const sections = document.querySelectorAll(".report-section");

    // ==== NAVEGACIÓN ENTRE SECCIONES ====
    navItems.forEach(item => {
        item.addEventListener("click", () => {
            const target = item.dataset.section;

            navItems.forEach(b => b.classList.remove("active"));
            item.classList.add("active");

            sections.forEach(sec => sec.classList.add("hidden"));

            if (target === "exportar") {
                openModal("exportModal");
                return;
            }

            document.getElementById(target)?.classList.remove("hidden");
            document.getElementById(target)?.scrollIntoView({ behavior: "smooth" });
        });
    });

    // ==== MODAL ====
    function openModal(id) {
        document.getElementById(id)?.classList.remove("hidden");
    }
    function closeModal(id) {
        document.getElementById(id)?.classList.add("hidden");
    }

    // ==== FILTRO AJAX (solo una versión, la correcta) ====
    const form = document.getElementById("filtro-form");

    form?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const params = new URLSearchParams(new FormData(form)).toString();

        try {
            const response = await fetch(`filtrar_reportes.php?${params}`);
            const html = await response.text();
            document.querySelector("#rango .reportes-container").innerHTML = html;
        } catch (err) {
            console.error("Error al aplicar filtros:", err);
        }
    });
});


document.addEventListener("DOMContentLoaded", () => {

    // ============================
    // 📌 Ventas últimos 7 días
    // ============================
    if (document.getElementById("chartVentas7Dias")) {
        new Chart(document.getElementById("chartVentas7Dias"), {
            type: 'line',
            data: {
                labels: ventas7Dias.map(v => v.fecha),
                datasets: [{
                    label: "Ventas",
                    data: ventas7Dias.map(v => v.total),
                    borderWidth: 2,
                    fill: false,
                    tension: 0.2
                }]
            }
        });
    }

    // ============================
    // 📌 Pedidos por área
    // ============================
    if (document.getElementById("chartPedidosArea")) {
        new Chart(document.getElementById("chartPedidosArea"), {
            type: 'doughnut',
            data: {
                labels: pedidosArea.map(a => a.area),
                datasets: [{
                    data: pedidosArea.map(a => a.pedidos),
                }]
            }
        });
    }

    // ============================
    // 📌 Ventas del mes actual
    // ============================
    if (document.getElementById("chartVentasMes")) {
        new Chart(document.getElementById("chartVentasMes"), {
            type: 'bar',
            data: {
                labels: ventasMes.map(v => v.fecha),
                datasets: [{
                    label: "Ventas",
                    data: ventasMes.map(v => v.total),
                }]
            }
        });
    }

});
