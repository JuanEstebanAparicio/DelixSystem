document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#registerModal form");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    Swal.fire({
      title: "Creando tu cuenta...",
      text: "Por favor espera.",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
      const response = await fetch("./src/auth/registerPrueba.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      });

      const result = await response.text(); // 👈 ahora es texto plano

      Swal.close();

      if (result.trim() === "ok") {
        Swal.fire({
          icon: "success",
          title: "¡Registro exitoso!",
          text: "Tu cuenta fue creada correctamente.",
          confirmButtonText: "Entendido",
        });
        form.reset();
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: result || "Ocurrió un problema al registrar la cuenta.",
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

