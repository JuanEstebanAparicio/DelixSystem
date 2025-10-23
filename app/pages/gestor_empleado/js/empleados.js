// empleados.js
document.addEventListener("DOMContentLoaded", () => {
  const codigoEl = document.getElementById("codigoDinamico");
  const copiarBtn = document.getElementById("copiarCodigo");
  const nuevoBtn = document.getElementById("nuevoCodigo");
  const timerText = document.getElementById("timerText");
  const ring = document.querySelector(".progress");
  const estado = document.getElementById("estadoCodigo");

  const circ = 283;
  let timer = null;
  let tiempo = 60;

  // ⚠️ Verificamos que el userId esté disponible
  if (typeof userId === "undefined" || !userId) {
    console.error("❌ No se encontró userId. El usuario no está logueado correctamente.");
    estado.textContent = "Error: sesión inválida.";
    estado.classList.add("expirado");
    return;
  }

  // 🔄 Timer circular
  const startTimer = (exp) => {
    clearInterval(timer);
    const expireTime = new Date(exp + " UTC").getTime();

    timer = setInterval(() => {
      const now = Date.now();
      const diff = Math.max(0, Math.floor((expireTime - now) / 1000));
      tiempo = diff;

      ring.style.strokeDashoffset = circ * (1 - diff / 60);
      timerText.textContent = `${tiempo}s`;

      if (diff <= 0) {
        clearInterval(timer);
        estado.textContent = "Código expirado";
        estado.classList.remove("activo");
        estado.classList.add("expirado");
        codigoEl.textContent = "••••••••";
        autoRegenerate();
      }
    }, 1000);
  };

  // 📤 Request con user_id incluido
  const request = async (action) => {
    try {
      const res = await fetch("../php/key/DynamicKeyController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action,
          user_id: userId, // 👈 se envía el ID real del usuario logueado
        }),
      });

      const text = await res.text();
      try {
        return JSON.parse(text);
      } catch {
        throw new Error("Invalid JSON: " + text);
      }
    } catch (err) {
      console.error("🚨 Error en request:", err);
      return { status: "error", message: "Error de red" };
    }
  };

  // 🧩 Obtener código
  const getCode = async () => {
    try {
      const data = await request("get");
      if (data.status === "success") {
        codigoEl.textContent = data.code;
        estado.textContent = "Código activo";
        estado.classList.add("activo");
        startTimer(data.expires_at);
      } else {
        codigoEl.textContent = "••••••••";
        estado.textContent = "Código expirado";
      }
    } catch (e) {
      console.error(e);
    }
  };

  // 🆕 Generar nuevo código
  const generateCode = async () => {
    try {
      const data = await request("generate");
      if (data.status === "success") {
        codigoEl.textContent = data.code;
        startTimer(data.expires_at);
        estado.textContent = "Código activo";
      }
    } catch (e) {
      console.error(e);
    }
  };

  // ♻️ Auto regenerar
  const autoRegenerate = () => {
    setTimeout(generateCode, 2000);
  };

  copiarBtn.addEventListener("click", () => {
    navigator.clipboard.writeText(codigoEl.textContent);
    copiarBtn.textContent = "✅ Copiado!";
    setTimeout(() => (copiarBtn.textContent = "Copiar"), 1500);
  });

  nuevoBtn.addEventListener("click", generateCode);

  // 🚀 Init
  getCode();
});
