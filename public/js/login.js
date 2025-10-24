document.addEventListener('DOMContentLoaded', () => {
  console.log("✅ JS Login cargado");

  const form = document.getElementById('loginForm');
  const submitButton = form.querySelector('button[type="submit"]');
  const modal = document.getElementById('loginModal');

  // 🔹 Crear overlay de carga dentro del modal
  const loader = document.getElementById('modalLoaderLogin');
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

      if (result.status === 'success') {
        // 🔹 Cerrar modal
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');

        setTimeout(() => {
          Swal.fire({
            icon: 'success',
            title: 'Bienvenido',
            text: result.message || 'Inicio de sesión exitoso',
            confirmButtonText: 'Continuar'
          }).then(() => {
            window.location.href = result.redirect || '../public/dashboard.php';
          });
        }, 200);
      } else {
        // 🔹 Cerrar modal antes de mostrar error
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');

        setTimeout(() => {
          Swal.fire({
            icon: 'error',
            title: 'Error al iniciar sesión',
            text: result.message || 'Credenciales incorrectas'
          });
        }, 200);
      }
    } catch (err) {
      console.error(err);
      loader.style.display = 'none';
      Swal.fire('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
    }
  });

  // 🔹 Botón para cerrar modal manualmente
  const closeBtn = document.getElementById('closeLogin');
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
