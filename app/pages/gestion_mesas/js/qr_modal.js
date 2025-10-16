document.addEventListener("DOMContentLoaded", () => {
  const qrContent = document.getElementById("qrModalContent");

  // 🔹 Cargar el QR dentro del modal
  async function cargarQR(idMesa) {
    if (!idMesa) return;
    qrContent.innerHTML = "<p>Cargando QR...</p>";
    try {
      const response = await fetch(`ver_qr.php?id=${encodeURIComponent(idMesa)}&ajax=1`);
      const html = await response.text();
      qrContent.innerHTML = html;
    } catch (err) {
      qrContent.innerHTML = "<p style='color:red;'>Error al cargar el QR.</p>";
      console.error("Error al cargar QR:", err);
    }
  }

  // 🔹 Delegación de eventos (funciona con botones creados dinámicamente)
  document.body.addEventListener("click", async (e) => {
    const btn = e.target.closest(".btn-icon.qr");
    if (!btn) return;

    const idMesa = btn.dataset.idMesa || btn.getAttribute("data-id-mesa");
    if (!idMesa) return;

    await cargarQR(idMesa);

    const modalSelector = btn.dataset.modalTarget || "#qrModal";
    const modal = document.querySelector(modalSelector);
    if (modal) {
      modal.style.display = "flex";
    }
  });
});
