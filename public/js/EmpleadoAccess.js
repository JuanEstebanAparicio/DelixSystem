document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("employeeLoginForm");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const data = new FormData(form);

    try {
      Alerts.loading("Verificando credenciales...");

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
        console.error("Respuesta no válida del servidor:", text);
        Alerts.error("Error inesperado del servidor.");
        return;
      }

      if (result.status === "success") {
        Alerts.success(result.message || "Ingreso exitoso 🎉");
        setTimeout(() => {
          window.location.href = result.redirect;
        }, 1500);
      } else {
        Alerts.error(result.message || "Credenciales incorrectas.");
      }
    } catch (err) {
      Alerts.close();
      console.error(err);
      Alerts.error("⚠️ Error de conexión con el servidor.");
    }
  });
});
