document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("employeeLoginForm");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const data = new FormData(form);

    try {
      Alerts.loading("Verificando acceso...");

      const res = await fetch(form.action, {
        method: "POST",
        body: data,
      });

      const text = await res.text();
      Alerts.close();

      let result;
      try {
        result = JSON.parse(text);
      } catch {
        console.error("❌ Respuesta inválida:", text);
        Alerts.error("Respuesta del servidor no válida.");
        return;
      }

      if (result.status === "success") {
        Alerts.success(result.message || "Inicio exitoso 🎉");

        setTimeout(() => {
          if (result.redirect) {
            window.location.href = result.redirect;
          }
        }, 1500);
      } else {
        Alerts.error(result.message || "No se pudo iniciar sesión.");
      }
    } catch (err) {
      Alerts.close();
      console.error(err);
      Alerts.error("⚠️ Error del servidor. Intenta nuevamente.");
    }
  });
});
