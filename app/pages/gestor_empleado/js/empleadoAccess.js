// PROJECTDELIX/app/pages/gestor_empleado/js/empleadoAccess.js

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("empleadoAccessForm");

  if (!form) {
    console.error("⚠️ Formulario de acceso de empleado no encontrado.");
    return;
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Muestra loader mientras se procesa
    Alerts.loading("Verificando código y registrando empleado...");

    const formData = new FormData(form);

    try {
      const response = await fetch(
        "/DelixSystem/app/pages/gestor_empleado/php/employee/EmpleadoController.php",
        {
          method: "POST",
          body: formData,
        }
      );

      const text = await response.text();
      let result;

      try {
        result = JSON.parse(text);
      } catch (err) {
        console.error("❌ Respuesta no válida del servidor:", text);
        Alerts.close();
        Alerts.error("El servidor devolvió una respuesta inválida.");
        return;
      }

      Alerts.close();

      if (result.status === "success") {
        Alerts.success(result.message || "Empleado vinculado correctamente.");
        form.reset();
      } else {
        Alerts.error(result.message || "No se pudo registrar el empleado.");
      }
    } catch (err) {
      console.error("⚠️ Error en la solicitud:", err);
      Alerts.close();
      Alerts.error("Error de conexión con el servidor. Inténtalo nuevamente.");
    }
  });
});
