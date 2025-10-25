// empleadosAccess.js
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("empleadoAccessForm");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const data = new FormData(form);

    try {
      Alerts.loading("Accediendo al sistema...");

      const res = await fetch("/DelixSystem/app/pages/gestor_empleado/php/employee/EmpleadoController.php", {
        method: "POST",
        body: data,
      });

      const text = await res.text();
      Alerts.close();

      let result;
      try {
        result = JSON.parse(text);
      } catch {
        console.error("❌ Respuesta no válida del servidor:", text);
        Alerts.error("Respuesta inválida del servidor.");
        return;
      }

      if (result.status === "success") {
        Alerts.success(result.message || "Ingreso exitoso 🎉");

        setTimeout(() => {
          // Si viene un redirect desde el backend, lo usamos
          if (result.redirect) {
            window.location.href = result.redirect;
          } else {
            Alerts.info("Redirección no especificada. Contacta al administrador.");
          }
        }, 1500);
      } else {
        Alerts.error(result.message || "No se pudo completar el acceso.");
      }
    } catch (err) {
      Alerts.close();
      console.error(err);
      Alerts.error("⚠️ Error del servidor. Intenta nuevamente.");
    }
  });
});
