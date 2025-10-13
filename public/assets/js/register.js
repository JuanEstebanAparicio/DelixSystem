document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#registerModal form");

  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Mostrar animación de carga
    Swal.fire({
      title: "Creando tu cuenta...",
      text: "Enviando correo de verificación, por favor espera.",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    // Preparar datos
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
      const response = await fetch("./src/auth/register.php", {
        method: "POST",
        body: JSON.stringify(data),
        headers: {
          "Content-Type": "application/json",
        },
      });

      const result = await response.json();

      Swal.close();

      if (result.success) {
        Swal.fire({
          icon: "success",
          title: "¡Registro exitoso!",
          text: "Hemos enviado un correo de verificación. Por favor revisa tu bandeja.",
          confirmButtonText: "Entendido",
        });
        form.reset();
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: result.message || "Ocurrió un problema al registrar tu cuenta.",
        });
      }
    } catch (error) {
      Swal.close();
      Swal.fire({
        icon: "error",
        title: "Error del servidor",
        text: error.message,
      });
    }
  });
});
