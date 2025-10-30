// ✅ DelixSystem/app/pages/gestor_empleado/js/employeeLogin.js
document.addEventListener('DOMContentLoaded', () => {
  console.log("✅ JS Login Empleado cargado");

  const form = document.getElementById('employeeLoginForm');
  if (!form) {
    console.error("❌ No se encontró el formulario de login de empleado.");
    return;
  }

  const submitButton = form.querySelector('button[type="submit"]');
  const modal = document.getElementById('employeeLoginModal');

  // Loader interno
  let loader = document.createElement('div');
  loader.id = 'modalLoaderEmployee';
  loader.innerHTML = `
    <div class="loader-backdrop">
      <div class="loader-box">
        <div class="loader"></div>
        <p>Verificando credenciales...</p>
      </div>
    </div>`;
  loader.style.display = 'none';
  modal.appendChild(loader);

  form.addEventListener('submit', async (e) => {
    e.preventDefault(); // 🔥 Detiene el envío tradicional

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
      loader.style.display = 'flex';
      const response = await fetch(form.action, {
        method: 'POST',
        body: formData
      });

      const rawText = await response.text();
      console.log("📩 Respuesta del servidor:", rawText);

      let result;
      try {
        result = JSON.parse(rawText);
      } catch {
        throw new Error("Respuesta del servidor no válida");
      }

      loader.style.display = 'none';
      modal.style.display = 'none';
      document.body.classList.remove('modal-open');

      if (result.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Bienvenido',
          text: result.message || 'Inicio de sesión exitoso',
          confirmButtonText: 'Continuar'
        }).then(() => {
          window.location.href = result.redirect || '/DelixSystem/app/pages/dashboard_empleado/view/index.php';
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error al iniciar sesión',
          text: result.message || 'Credenciales incorrectas'
        });
      }

    } catch (err) {
      loader.style.display = 'none';
      console.error("❌ Error en el login:", err);
      Swal.fire('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
    }
  });
});
