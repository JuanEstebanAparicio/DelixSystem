// PROJECTDELIX/public/js/alert.js
// Sistema centralizado de SweetAlert2 reutilizable en todo el proyecto

window.Alerts = {
  success(message, title = 'Éxito') {
    Swal.fire({
      icon: 'success',
      title,
      text: message,
      confirmButtonColor: '#3085d6',
      confirmButtonText: 'Aceptar',
      timer: 2500,
      timerProgressBar: true,
    });
  },

  error(message, title = 'Error') {
    Swal.fire({
      icon: 'error',
      title,
      text: message,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Cerrar',
    });
  },

  warning(message, title = 'Advertencia') {
    Swal.fire({
      icon: 'warning',
      title,
      text: message,
      confirmButtonColor: '#f1c40f',
      confirmButtonText: 'Entendido',
    });
  },

  info(message, title = 'Información') {
    Swal.fire({
      icon: 'info',
      title,
      text: message,
      confirmButtonColor: '#3085d6',
      confirmButtonText: 'Ok',
    });
  },

  async confirm(message, title = '¿Estás seguro?', options = {}) {
    const result = await Swal.fire({
      title,
      text: message,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí',
      cancelButtonText: 'Cancelar',
      ...options,
    });
    return result.isConfirmed;
  },

  loading(message = 'Procesando...') {
    Swal.fire({
      title: message,
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });
  },

  close() {
    Swal.close();
  },

  // --- Auto-gestión de botones con confirmación y formularios con loader ---
  handleConfirmables() {
    // Botones o enlaces con data-confirm
    document.querySelectorAll('[data-confirm]').forEach(el => {
      el.addEventListener('click', async (e) => {
        e.preventDefault();
        const url = el.getAttribute('href');
        const msg = el.dataset.confirm || '¿Estás seguro de continuar?';

        const confirmed = await Alerts.confirm(msg);
        if (confirmed) {
          Alerts.loading();
          setTimeout(() => (window.location.href = url), 600);
        }
      });
    });

    // Formularios con data-loader
    document.querySelectorAll('form[data-loader]').forEach(form => {
      form.addEventListener('submit', () => Alerts.loading());
    });
  },
};

// Inicializar automáticamente en cada carga
document.addEventListener('DOMContentLoaded', Alerts.handleConfirmables);
