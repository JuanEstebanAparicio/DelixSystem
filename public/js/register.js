document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('registerForm');
  const submitButton = form.querySelector('button[type="submit"]');
  const modal = document.getElementById('registerModal');

  // 🔹 Crear overlay global (loader general)
  const loader = document.createElement('div');
  loader.id = 'modalLoader';
  loader.innerHTML = `
    <div class="loader-box">
      <div class="loader"></div>
      <p>Registrando usuario...</p>
    </div>
  `;
  document.body.appendChild(loader);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // 🔸 Cerrar el modal inmediatamente
    modal.classList.add('fadeOut');
    setTimeout(() => {
      modal.style.display = 'none';
      modal.classList.remove('fadeOut');
    }, 300);

    // 🔸 Mostrar loader en pantalla completa
    loader.style.display = 'flex';

    // 🔸 Deshabilitar botón mientras tanto
    submitButton.disabled = true;
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Procesando...';

    const formData = new FormData(form);

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: formData
      });

      const rawText = await response.text();
      console.log("Respuesta del servidor:");
      console.log(rawText);

      const result = JSON.parse(rawText);

      // 🔹 Mostrar resultado según respuesta del backend
      if (result.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: '¡Registro exitoso!',
          text: result.message,
          showConfirmButton: false,
          timer: 2500
        });
        form.reset();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error en el registro',
          text: result.message,
          showConfirmButton: false,
          timer: 2500
        });
      }
    } catch (error) {
      console.error(error);
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.',
        showConfirmButton: false,
        timer: 2500
      });
    } finally {
      // 🔹 Ocultar loader y restaurar botón
      loader.style.display = 'none';
      submitButton.disabled = false;
      submitButton.textContent = originalText;
    }
  });
});
