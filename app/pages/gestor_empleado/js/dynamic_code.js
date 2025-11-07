// ============================================================
// 🧩 Módulo: Código Dinámico de Acceso (dynamic_code.js)
// ------------------------------------------------------------
// Controla la generación, expiración y visualización del código
// dinámico para acceso de empleados. Incluye:
//  - Timer circular animado
//  - Botones de regenerar y copiar
//  - Estado visual ("activo" / "expirado")
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  console.log("[dynamic_code.js] inicializado ✅");

  // === Elementos base ===
  const codigoEl = document.getElementById("codigoDinamico");
  const copiarBtn = document.getElementById("copiarCodigo");
  const nuevoBtn = document.getElementById("nuevoCodigo");
  const timerText = document.getElementById("timerText");
  const ring = document.querySelector(".progress");
  const estado = document.getElementById("estadoCodigo");

  // Si el HTML no tiene el bloque del código dinámico, salimos
  if (!codigoEl || !copiarBtn || !nuevoBtn) {
    console.warn("[dynamic_code.js] Bloque de código dinámico no encontrado.");
    return;
  }

  // === Variables del timer circular ===
  const circ = 283;
  let timer = null;
  let tiempo = 60;

  // ⚠️ Verificamos sesión
  if (typeof userId === "undefined" || !userId) {
    console.error("❌ No se encontró userId. El usuario no está logueado correctamente.");
    estado.textContent = "Error: sesión inválida.";
    estado.classList.add("expirado");
    return;
  }

  // ============================================================
  // ⏱️ TIMER VISUAL
  // ============================================================
  const startTimer = (exp) => {
    clearInterval(timer);
    const expireTime = new Date(exp + " UTC").getTime();

    timer = setInterval(() => {
      const now = Date.now();
      const diff = Math.max(0, Math.floor((expireTime - now) / 1000));
      tiempo = diff;

      // Animación circular
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

  // ============================================================
  // 📤 REQUEST al servidor
  // ============================================================
  const request = async (action) => {
    try {
      const res = await fetch("../php/key/DynamicKeyController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ action, user_id: userId }),
      });

      // 🔒 Manejo de errores de permisos
      if (res.status === 403) {
        const data = await res.json().catch(() => ({
          message: "No tienes permisos suficientes para realizar esta acción.",
        }));
        Alerts.warning(data.message, "Acceso denegado");
        return { status: "error", message: data.message };
      }

      const text = await res.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch {
        throw new Error("Respuesta inválida del servidor: " + text);
      }
      return data;
    } catch (err) {
      console.error("🚨 Error en request:", err);
      Alerts.error("Error de conexión con el servidor.", "Error de red");
      return { status: "error", message: "Error de red" };
    }
  };

  // ============================================================
  // 🔄 Obtener código
  // ============================================================
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
      console.error("[dynamic_code.js] getCode error:", e);
    }
  };

  // ============================================================
  // 🆕 Generar nuevo código
  // ============================================================
  const generateCode = async () => {
    try {
      const data = await request("generate");
      if (data.status === "success") {
        codigoEl.textContent = data.code;
        startTimer(data.expires_at);
        estado.textContent = "Código activo";
        estado.classList.remove("expirado");
        estado.classList.add("activo");
      }
    } catch (e) {
      console.error("[dynamic_code.js] generateCode error:", e);
    }
  };

  // ============================================================
  // ♻️ Auto regenerar
  // ============================================================
  const autoRegenerate = () => {
    setTimeout(generateCode, 2000);
  };

  // ============================================================
  // 📋 Eventos UI
  // ============================================================
  copiarBtn.addEventListener("click", () => {
    navigator.clipboard.writeText(codigoEl.textContent);
    copiarBtn.textContent = "✅ Copiado!";
    setTimeout(() => (copiarBtn.textContent = "Copiar"), 1500);
  });

  nuevoBtn.addEventListener("click", generateCode);

  // ============================================================
  // 🚀 Init
  // ============================================================
  getCode();
});
