document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ Dashboard propietario cargado");

  // ============================
  // 🪑 Mostrar resumen de áreas y mesas
  // ============================
  const mesasItem = document.querySelector('.sidebar ul li[data-section="mesas"]');

  if (mesasItem) {
    mesasItem.addEventListener('click', async () => {
      console.log("📊 Cargando resumen de áreas y mesas...");

      Swal.fire({
        title: "Consultando información...",
        text: "Por favor espera un momento.",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      try {
        const response = await fetch("../php/resumen_mesas.php");
        if (!response.ok) throw new Error("Error al obtener los datos");

        const data = await response.json();
        Swal.close();

        // Crear contenido resumen
        const resumenHTML = `
          <div class="resumen-container">
            <h2>Resumen de tus áreas y mesas</h2>
            <div class="resumen-cards">
              <div class="card">
                <h3>Áreas</h3>
                <p>${data.total_areas}</p>
              </div>
              <div class="card">
                <h3>Mesas</h3>
                <p>${data.total_mesas}</p>
              </div>
            </div>
            <button id="goToMesas" class="btn-primary">
              Ir al Gestor Completo
            </button>
          </div>
        `;

        // Reemplazar contenido principal
        const main = document.querySelector(".main");
        main.innerHTML = resumenHTML;

        // Agregar acción al botón
        document.getElementById("goToMesas").addEventListener("click", () => {
          Swal.fire({
            title: "Cargando gestor de mesas...",
            text: "Por favor espera un momento",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
              setTimeout(() => {
                window.location.href = "/DelixSystem/app/pages/gestion_mesas/view/gestion_mesas.php";
              }, 1000);
            }
          });
        });

      } catch (err) {
        console.error("❌ Error al cargar resumen:", err);
        Swal.fire("Error", "No se pudo cargar el resumen del usuario.", "error");
      }
    });
  }
});
