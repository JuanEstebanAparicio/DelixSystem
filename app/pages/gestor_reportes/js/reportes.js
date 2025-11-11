
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
