<?php
/**
 * helpers/alerts.php
 * 
 * Muestra alertas SweetAlert2 desde PHP (enviadas al navegador)
 * sin usar target ni dependencias adicionales.
 */

/**
 * Muestra una alerta SweetAlert2
 *
 * @param string $title  Título del mensaje
 * @param string $text   Texto descriptivo
 * @param string $icon   Tipo de ícono: success | error | warning | info | question
 * @param string|null $redirect  (Opcional) URL a la que redirigir después de cerrar la alerta
 */
function showAlert($title, $text, $icon = 'info', $redirect = null) {
    // Escapar cadenas para prevenir errores de comillas
    $title_esc = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $text_esc  = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $icon_esc  = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');
    
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          title: '{$title_esc}',
          text: '{$text_esc}',
          icon: '{$icon_esc}',
          confirmButtonText: 'Aceptar',
          confirmButtonColor: '#3085d6'
        }).then((result) => {
          if (result.isConfirmed) {
            " . ($redirect ? "window.location.href = '{$redirect}';" : "") . "
          }
        });
      });
    </script>
    ";
}


