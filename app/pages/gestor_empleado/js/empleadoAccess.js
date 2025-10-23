// empleadosAccess.js
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("empleadoAccessForm");

  if (!form) return; // defensivo: si no hay formulario, salir

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const data = new FormData(form);

    try {
      Alerts.loading("Registrando empleado...");

      const res = await fetch("/DelixSystem/app/pages/gestor_empleado/php/employee/EmpleadoController.php", {
        method: "POST",
        body: data,
      });

      // Capturar texto antes de intentar parsear
      const text = await res.text();
      Alerts.close();

      let result;
      try {
        result = JSON.parse(text);
      } catch (parseErr) {
        console.error("Respuesta no válida del servidor:", text);
        Alerts.error("Respuesta del servidor no válida. Intenta nuevamente.");
        return;
      }

      if (result.status === "success") {
        // ✅ Mostrar alerta de éxito
        Alerts.success("El empleado fue registrado correctamente 🎉");

        // 🔒 Cerrar el modal después de un breve delay
        setTimeout(() => {
          const modal = document.querySelector("#codeModal");
          if (modal) modal.style.display = "none";
          form.reset();
        }, 1000);
      } else {
        Alerts.error(result.message || "Error al registrar el empleado.");
      }
    } catch (err) {
      Alerts.close();
      console.error(err);
      Alerts.error("⚠️ Error del servidor. Intenta nuevamente.");
    }
  });
});
