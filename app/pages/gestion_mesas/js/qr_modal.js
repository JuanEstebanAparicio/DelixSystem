document.addEventListener("DOMContentLoaded", () => {
  const qrButtons = document.querySelectorAll(".btn-icon.qr");
  const qrContent = document.getElementById("qrModalContent");

  qrButtons.forEach(btn => {
    btn.addEventListener("click", async () => {
      const idMesa = btn.dataset.idMesa;
      if (!idMesa) return;

      qrContent.innerHTML = "<p>Cargando QR...</p>";

      try {
        const response = await fetch(`ver_qr.php?id=${idMesa}&ajax=1`);
        const html = await response.text();
        qrContent.innerHTML = html;
      } catch (err) {
        qrContent.innerHTML = "<p style='color:red;'>Error al cargar el QR.</p>";
      }
    });
  });
});