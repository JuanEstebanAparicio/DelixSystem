document.addEventListener("DOMContentLoaded", () => {
  const qrContent = document.getElementById("qrIngredientModalContent");
  const openQRButton = document.getElementById("openQRIngredientModal");

  // --- Función global para cargar QR general ---
  async function cargarQRGeneral() {
    qrContent.innerHTML = "<p>Cargando QR...</p>";

    try {
      const resp = await fetch(`ver_qr_ingrediente.php?modo=general&ajax=1`);
      const html = await resp.text();
      qrContent.innerHTML = html;
    } catch (err) {
      qrContent.innerHTML = "<p style='color:red;'>Error al cargar el QR.</p>";
      console.error("Error:", err);
    }
  }

  // 👉 CLICK EN EL BOTÓN GLOBAL
  openQRButton.addEventListener("click", async () => {
    await cargarQRGeneral();

    const modal = document.querySelector("#qrIngredientModal");
    if (modal) {
      modal.classList.remove("hidden");
      modal.style.display = "flex";
    }
  });

  // --- QR de las cards ---
  document.body.addEventListener("click", async (e) => {
    const btn = e.target.closest(".btn-icon.qr");
    if (!btn) return;

    const idIng = btn.dataset.idIngredient || btn.getAttribute("data-id-ingredient");
    if (!idIng) return;

    await cargarQRIngrediente(idIng);

    const modalSelector = btn.dataset.modalTarget || "#qrIngredientModal";
    const modal = document.querySelector(modalSelector);

    if (modal) {
      modal.classList.remove("hidden");
      modal.style.display = "flex";
    }
  });
});
