document.addEventListener("DOMContentLoaded", () => {
  /* ========== CLAVE DINÁMICA ========== */
  const codigoEl = document.getElementById("codigoDinamico");
  const copiarBtn = document.getElementById("copiarCodigo");
  const nuevoBtn = document.getElementById("nuevoCodigo");
  const timerText = document.getElementById("timerText");
  const ring = document.querySelector(".ring-progress");
  const estado = document.getElementById("estadoCodigo");

  let tiempo = 60;
  const circunferencia = 213.628;
  let timer;

  const generarCodigo = () => {
    return Array.from(crypto.getRandomValues(new Uint8Array(8)))
      .map(b => (b % 36).toString(36).toUpperCase())
      .join('')
      .match(/.{1,4}/g)
      .join('-');
  };

  const iniciarTimer = () => {
    clearInterval(timer);
    tiempo = 60;
    ring.style.strokeDashoffset = 0;
    estado.textContent = "Código activo";
    estado.style.color = "#10b981";

    timer = setInterval(() => {
      tiempo--;
      const offset = circunferencia * (1 - tiempo / 60);
      ring.style.strokeDashoffset = offset;
      timerText.textContent = `${tiempo}s`;

      if (tiempo <= 0) {
        clearInterval(timer);
        codigoEl.textContent = "••••••••";
        estado.textContent = "Código expirado";
        estado.style.color = "#ef4444";
      }
    }, 1000);
  };

  const nuevoCodigo = () => {
    const codigo = generarCodigo();
    codigoEl.textContent = codigo;
    iniciarTimer();
  };

  copiarBtn.addEventListener("click", () => {
    navigator.clipboard.writeText(codigoEl.textContent);
    copiarBtn.textContent = "✅ Copiado!";
    setTimeout(() => (copiarBtn.textContent = "📋 Copiar"), 1500);
  });

  nuevoBtn.addEventListener("click", nuevoCodigo);

  // Inicializar
  nuevoCodigo();
});
