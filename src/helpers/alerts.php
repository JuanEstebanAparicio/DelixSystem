<?php
function showAlert($title, $message, $icon = 'info', $redirect = null) {
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>DELIX | Notificación</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: " . json_encode($title) . ",
                    html: " . json_encode($message) . ",
                    icon: " . json_encode($icon) . ",
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    " . ($redirect ? "window.location.href = '$redirect';" : "") . "
                });
            });
        </script>
    </body>
    </html>
    ";
    exit;
}
?>
