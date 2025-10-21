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

  // Ocultar inicialmente
  loader.style.display = 'none';

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
      console.log("Respuesta del servidor:");
      console.log(rawText);

      const result = JSON.parse(rawText);

      if (result.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Registro exitoso',
          text: result.message,
          confirmButtonText: 'OK'
        }).then(() => {
          form.reset();
          modal.style.display = 'none';
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: result.message
        });
      }
    } catch (err) {
      console.error(err);
      Swal.fire('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
    } finally {
      loader.style.display = 'none';
    }
  });
});
