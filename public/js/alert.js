// PROJECTDELIX/public/js/alert.js
// Archivo central de notificaciones con SweetAlert2
// Se importa una sola vez y puede usarse en cualquier script

// Asegúrate de incluir SweetAlert2 antes de este script en tu HTML:
// <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
// <script src="./js/alert.js"></script>

window.Alerts = {
  success: (message, title = 'Éxito') => {
    Swal.fire({
      icon: 'success',
      title: title,
      text: message,
      confirmButtonColor: '#3085d6',
      confirmButtonText: 'Aceptar',
      timer: 3000,
      timerProgressBar: true
    });
  },

  error: (message, title = 'Error') => {
    Swal.fire({
      icon: 'error',
      title: title,
      text: message,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Cerrar'
    });
  },

  warning: (message, title = 'Advertencia') => {
    Swal.fire({
      icon: 'warning',
      title: title,
      text: message,
      confirmButtonColor: '#f1c40f',
      confirmButtonText: 'Entendido'
    });
  },

  info: (message, title = 'Información') => {
    Swal.fire({
      icon: 'info',
      title: title,
      text: message,
      confirmButtonColor: '#3085d6',
      confirmButtonText: 'Ok'
    });
  },

  confirm: async (message, title = '¿Estás seguro?') => {
    const result = await Swal.fire({
      title: title,
      text: message,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí',
      cancelButtonText: 'Cancelar'
    });
    return result.isConfirmed;
  }
};
