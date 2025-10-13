<?php
echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script>
Swal.fire({
  icon: 'success',
  title: '¡Correo verificado!',
  html: 'Tu cuenta ha sido activada. Ya puedes iniciar sesión en DELIX.',
  confirmButtonText: 'Ir al inicio'
}).then(() => window.location.href = '../index.html');
</script>";
?>
