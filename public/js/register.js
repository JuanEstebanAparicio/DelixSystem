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

      const result = await response.json();

      if (result.status === 'success') {
        Alerts.success(result.message);
        form.reset();
      } else {
        Alerts.error(result.message);
      }
    } catch (err) {
      Alerts.error('No se pudo conectar con el servidor.');
    }
  });
});

