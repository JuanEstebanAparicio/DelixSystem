document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('registerForm');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: formData
      });

      // Para depuración
      const rawText = await response.text();
      console.log("Respuesta del servidor:");
      console.log(rawText);

      const result = JSON.parse(rawText); // Intentamos convertir a JSON

      if (result.status === 'success') {
        Swal.fire('Registro exitoso', result.message, 'success');
        form.reset();
      } else {
        Swal.fire('Error', result.message, 'error');
      }
    } catch (err) {
      Swal.fire('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
      console.error(err);
    }
  });
});
