// Delixsystem/public/js/loign_empleado.js

// ✅ DelixSystem/public/js/login_empleado.js
document.addEventListener('DOMContentLoaded', () => {
  console.log("✅ JS Login Empleado cargado");

  const form = document.getElementById('employeeLoginForm');
  const modal = document.getElementById('employeeLoginModal');
  const closeBtn = document.getElementById('closeEmployeeLogin');
  if (!form) return console.error("❌ No se encontró el formulario de login de empleado");

  const submitButton = form.querySelector('button[type="submit"]');

  // 🔹 Crear overlay de carga dentro del modal
  const loader = document.createElement('div');
  loader.id = 'modalLoaderEmployee';
  loader.innerHTML = `<div class="loader"></div><p>Verificando...</p>`;
  loader.style.cssText = `
    display: none;
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(255,255,255,0.8);
    justify-content: center;
    align-items: center;
    flex-direction: column;
    font-weight: bold;
    font-size: 1.1em;
    color: #333;
  `;
  modal.appendChild(loader);

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
      console.log("📩 Respuesta del servidor (Empleado):");
      console.log(rawText);

      let result;
      try {
        result = JSON.parse(rawText);
      } catch (err) {
        throw new Error("Respuesta del servidor no válida");
      }

      loader.style.display = 'none'; // Quitar loader

      if (result.status === 'success') {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');

        setTimeout(() => {
          Swal.fire({
            icon: 'success',
            title: 'Bienvenido',
            text: result.message || 'Inicio de sesión exitoso',
            confirmButtonText: 'Continuar'
          }).then(() => {
            window.location.href = result.redirect || '/DelixSystem/app/pages/dashboard_empleado/view/index.php';
          });
        }, 200);
      } else {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');

        setTimeout(() => {
          Swal.fire({
            icon: 'error',
            title: 'Error al iniciar sesión',
            text: result.message || 'Credenciales incorrectas o no perteneces al restaurante'
          });
        }, 200);
      }

    } catch (err) {
      console.error(err);
      loader.style.display = 'none';
      Swal.fire('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
    }
  });

  // 🔹 Botón para cerrar el modal
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
