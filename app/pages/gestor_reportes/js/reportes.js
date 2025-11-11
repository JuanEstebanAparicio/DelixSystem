document.addEventListener("DOMContentLoaded", () => {
    const navItems = document.querySelectorAll(".nav-item");
    const sections = document.querySelectorAll(".report-section");

    navItems.forEach(item => {
        item.addEventListener("click", () => {
            const target = item.dataset.section;

            // Activar botón actual
            navItems.forEach(b => b.classList.remove("active"));
            item.classList.add("active");

            // Ocultar todas las secciones
            sections.forEach(sec => sec.classList.add("hidden"));

            // Mostrar la sección seleccionada
            if (target === "exportar") {
                openModal("exportModal");
                return;
            }

            document.getElementById(target)?.classList.remove("hidden");
            document.getElementById(target)?.scrollIntoView({ behavior: "smooth" });
        });
    });
});

// ==== MODAL ====
function openModal(id) {
    document.getElementById(id)?.classList.remove("hidden");
}
function closeModal(id) {
    document.getElementById(id)?.classList.add("hidden");
}

// ==== FILTRO AJAX ====
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("filtro-form");

    form?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const params = new URLSearchParams(new FormData(form)).toString();

        try {
            const response = await fetch(`index.php?${params}`, { method: "GET" });
            const html = await response.text();
            document.querySelector(".reportes-container").innerHTML =
                new DOMParser().parseFromString(html, "text/html")
                .querySelector(".reportes-container").innerHTML;
        } catch (err) {
            console.error("Error al aplicar filtros:", err);
        }
    });
});
