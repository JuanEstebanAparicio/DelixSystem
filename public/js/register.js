document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('registerForm');
  const submitButton = form.querySelector('button[type="submit"]');
  const modal = document.getElementById('registerModal');

  // 🔹 Crear overlay de carga dentro del modal
  const loader = document.createElement('div');
  loader.id = 'modalLoader';
  loader.innerHTML = `
    <div class="loader-backdrop">
      <div class="loader-box">
        <div class="loader"></div>
        <p>Registrando usuario...</p>
      </div>
    </div>
  `;
  modal.appendChild(loader);
  loader.style.display = 'none'; // Oculto inicialmente

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // 🔸 Cooldown de 3 segundos
    submitButton.disabled = true;
    let cooldown = 3;
    const originalText = submitButton.textContent;
    const countdown = setInterval(() => {
      submitButton.textContent = `Espere ${cooldown}s...`;
      cooldown--;
      if (cooldown < 0) {
        clearInterval(countdown);
        submitButton.textContent = originalText;
        submitButton.disabled = false;
      }
    }, 1000);

    const formData = new FormData(form);

    try {
      loader.style.display = 'flex'; // Mostrar overlay

      const response = await fetch(form.action, {
        method: 'POST',
        body: formData
      });

      const rawText = await response.text();
      console.log("📩 Respuesta del servidor:");
      console.log(rawText);

      let result;
      try {
        result = JSON.parse(rawText);
      } catch (err) {
        throw new Error("Respuesta del servidor no válida");
      }

      loader.style.display = 'none'; // Quitar loader antes de cerrar modal

      // 🔹 Cerrar modal tanto en éxito como en error
      modal.style.display = 'none';
      document.body.classList.remove('modal-open');

      if (result.status === 'success') {
        form.reset();

        setTimeout(() => {
          Swal.fire({
            icon: 'success',
            title: 'Registro exitoso',
            text: result.message,
            confirmButtonText: 'OK'
          });
        }, 200);
      } else {
        setTimeout(() => {
          Swal.fire({
            icon: 'error',
            title: 'Error en el registro',
            text: result.message
          });
        }, 200);
      }
    } catch (err) {
      console.error(err);
      loader.style.display = 'none';
      modal.style.display = 'none'; // También cerramos modal si hay error de conexión
      document.body.classList.remove('modal-open');

      Swal.fire('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
    }
  });

  // 🔹 Cerrar modal manualmente
  const closeBtn = document.getElementById('closeRegister');
  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      modal.style.display = 'none';
    });
  }

  // 🔹 Cerrar modal al hacer clic fuera
  window.addEventListener('click', (event) => {
    if (event.target === modal) {
      modal.style.display = 'none';
    }
  });
});
