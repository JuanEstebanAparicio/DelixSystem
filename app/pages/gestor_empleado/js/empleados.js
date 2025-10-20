// 📂 app/pages/gestor_empleado/js/empleados.js
document.addEventListener("DOMContentLoaded", () => {
  const codigoEl = document.getElementById("codigoDinamico");
  const copiarBtn = document.getElementById("copiarCodigo");
  const nuevoBtn = document.getElementById("nuevoCodigo");
  const timerText = document.getElementById("timerText");
  const ring = document.querySelector(".progress");
  const estado = document.getElementById("estadoCodigo");

  // 🔹 Variables de control
  const circ = 283;
  let timer = null;
  let tiempo = 60;
  const ADMIN_ID = 5; // ⚠️ Cambiar dinámicamente según la sesión del admin

  /**
   * 🔄 Inicia el temporizador circular del código dinámico
   * @param {string} expiracion - Fecha/hora ISO de expiración
   */
  const iniciarTimer = (expiracion) => {
    clearInterval(timer);

    const expireTime = new Date(expiracion).getTime();

    ring.style.strokeDashoffset = 0;
    estado.textContent = "Código activo";
    estado.classList.remove("expirado");
    estado.classList.add("activo");

    timer = setInterval(() => {
      const now = new Date().getTime();
      const diff = Math.max(0, Math.floor((expireTime - now) / 1000));
      tiempo = diff;

      const offset = circ * (1 - tiempo / 60);
      ring.style.strokeDashoffset = offset;
      timerText.textContent = `${tiempo}s`;

      if (tiempo <= 0) {
        clearInterval(timer);
        codigoEl.textContent = "••••••••";
        estado.textContent = "Código expirado";
        estado.classList.remove("activo");
        estado.classList.add("expirado");
      }
    }, 1000);
  };

  /**
   * 🧩 Llama al backend para generar un nuevo código
   */
  const generarNuevoCodigo = async () => {
    try {
      const response = await fetch("../php/key/DynamicKeyController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          accion: "generar",
          admin_id: ADMIN_ID,
        }),
      });

      // 🧠 Evita error de JSON: analiza manualmente
      const text = await response.text();
      console.log("📦 Respuesta al generar código:", text);

      let data;
      try {
        data = JSON.parse(text);
      } catch {
        throw new Error("El servidor devolvió una respuesta no válida.");
      }

      if (data.status === "ok") {
        codigoEl.textContent = data.code;
        iniciarTimer(data.expires_at);
      } else {
        throw new Error(data.message || "Error generando código");
      }
    } catch (err) {
      console.error("❌ Error al generar código:", err);
      codigoEl.textContent = "Error";
      estado.textContent = "Error al generar código";
      estado.classList.add("expirado");
    }
  };

  /**
   * 🔍 Consulta si hay un código activo en el backend
   */
  const consultarCodigoActivo = async () => {
    try {
      const response = await fetch("../php/key/DynamicKeyController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          accion: "consultar",
          admin_id: ADMIN_ID,
        }),
      });

      // 🧠 Parseo seguro
      const text = await response.text();
      console.log("📦 Respuesta al consultar código:", text);

      let data;
      try {
        data = JSON.parse(text);
      } catch {
        throw new Error("El servidor devolvió una respuesta no válida.");
      }

      if (data && data.status === "ok" && data.code) {
        codigoEl.textContent = data.code;
        iniciarTimer(data.expires_at);
      } else {
        codigoEl.textContent = "••••••••";
        estado.textContent = "Código expirado";
        estado.classList.remove("activo");
        estado.classList.add("expirado");
      }
    } catch (err) {
      console.error("❌ Error al consultar código activo:", err);
      codigoEl.textContent = "Error";
      estado.textContent = "Error al conectar con el servidor";
      estado.classList.add("expirado");
    }
  };

  /**
   * 📋 Copia el código dinámico al portapapeles
   */
  copiarBtn.addEventListener("click", () => {
    const codigo = codigoEl.textContent;
    if (codigo && codigo !== "••••••••" && codigo !== "Error") {
      navigator.clipboard.writeText(codigo);
      copiarBtn.textContent = "✅ Copiado!";
      setTimeout(() => (copiarBtn.textContent = "📋 Copiar"), 1500);
    }
  });

  /**
   * ⚡ Regenerar nuevo código manualmente
   */
  nuevoBtn.addEventListener("click", generarNuevoCodigo);

  /**
   * 🚀 Inicializar: consultar código existente al cargar la página
   */
  consultarCodigoActivo();
});
