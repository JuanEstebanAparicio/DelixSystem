<?php
require_once __DIR__ . '/../app/config/path.php';
echo "<!-- BASE_URL = " . BASE_URL . " -->";
?>


<!-- DelixSystem/public/index.php -->
<?php
require_once __DIR__ . '/../app/middleware/employee_guard.php';
require_once __DIR__ . '/../app/middleware/session_guard.php';

redirectIfEmpleadoLoggedIn();

checkIfLoggedIn(); // Evita que un usuario logueado vuelva al login
?>

<!DOCTYPE html>
<!-- public/index.php -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DELIX | Gestión Inteligente para Restaurantes</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

    <!-- HEADER -->
    <header class="header">
        <div class="container header-content">
            <h1 class="logo">DELIX</h1>
            <nav class="nav">
                <a href="#features">Características</a>
                <a href="#gallery">Galería</a>
                <a href="#testimonials">Opiniones</a>
                <button id="loginBtn" class="btn-outline" data-modal-target="#codeModal">Backdoor Log</button>
                <button class="btn-outline" data-modal-target="#loginModal">Iniciar Sesión</button>
                <button class="btn-primary" data-modal-target="#registerModal">Registrarse</button>
            </nav>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-text">
                <h2>Gestiona tu restaurante con <span>DELIX</span></h2>
                <p>Una plataforma moderna y gratuita que simplifica la administración de tu restaurante. Controla pedidos, empleados e inventario desde un solo lugar.</p>
                <div class="hero-buttons">
                    <button class="btn-primary" data-modal-target="#registerModal">Comenzar Gratis</button>
                    
                </div>
            </div>
            <div class="hero-img">
                <img src="./img/hero.jpg" alt="Restaurante moderno y acogedor">

            </div>
        </div>
    </section>

    <!-- FEATURE SECTION -->
    <section id="features" class="features">
        <div class="container">
            <h2>Lo que hace especial a DELIX</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <img src="https://cdn-icons-png.flaticon.com/512/1026/1026657.png" alt="Icono pedidos">
                    <h3>Gestión de pedidos</h3>
                    <p>Administra y visualiza pedidos en tiempo real desde cualquier dispositivo.</p>
                </div>
                <div class="feature-card">
                    <img src="https://cdn-icons-png.flaticon.com/512/3126/3126647.png" alt="Icono inventario">
                    <h3>Inventario automatizado</h3>
                    <p>Controla tus insumos, evita desperdicios y recibe alertas de stock bajo.</p>
                </div>
                <div class="feature-card">
                    <img src="https://cdn-icons-png.flaticon.com/512/1995/1995574.png" alt="Icono reportes">
                    <h3>Reportes visuales</h3>
                    <p>Obtén estadísticas e informes dinámicos para tomar mejores decisiones.</p>
                </div>
                <div class="feature-card">
                    <img src="https://cdn-icons-png.flaticon.com/512/1161/1161388.png" alt="Icono empleados">
                    <h3>Gestión de empleados</h3>
                    <p>Asigna roles, gestiona horarios y monitorea el rendimiento de tu equipo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- GALLERY -->
    <section id="gallery" class="gallery">
        <div class="container">
            <h2>Así se vive la experiencia DELIX</h2>
            <div class="gallery-grid">
                <img src="./img/RestauranteModerno.png" alt="Restaurante moderno">
                <img src="./img/CocinaProfecional.png" alt="Cocina profesional">
                <img src="./img/BuenServicio.png" alt="Servicio eficiente">
                <img src="./img/BuenAmbiente.png" alt="Ambiente amigable">
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section id="testimonials" class="testimonials">
        <div class="container">
            <h2>Historias de éxito con DELIX</h2>
            <div class="testimonials-grid">
                <div class="testimonial">
                    <p>“Desde que uso DELIX, todo es más rápido. La cocina y el salón trabajan en perfecta sincronía.”</p>
                    <h4>María Gómez — Café Bonito</h4>
                </div>
                <div class="testimonial">
                    <p>“El sistema es tan intuitivo que mis empleados aprendieron en minutos. Lo mejor: no cuesta nada.”</p>
                    <h4>Carlos Ruiz — Pizzería El Sabor</h4>
                </div>
                <div class="testimonial">
                    <p>“Amo los reportes visuales. Ahora entiendo mi negocio con solo mirar los gráficos.”</p>
                    <h4>Ana Torres — Restaurante El Patio</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA FINAL -->
    <section class="cta">
        <div class="container cta-content">
            <h2>Transforma la forma en que gestionas tu restaurante</h2>
            <p>DELIX es 100% gratuito y fácil de usar. Regístrate ahora y empieza a optimizar tu negocio.</p>
            <button class="btn-primary" id="registerBtn"data-modal-target="#registerModal">Comenzar Gratis</button>
        </div>
    </section>

    <!-- MODALES -->
<!-- MODAL DE LOGIN -->
<div id="loginModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeLogin">&times;</span>
    <h3>Iniciar Sesión</h3>

    <form id="loginForm" method="POST" action="../src/auth/login.php">
      <div class="form-group">
        <input type="email" name="email" placeholder="Correo electrónico" required>
      </div>

     <div class="form-group password-container">
  <input type="password" name="password" placeholder="Contraseña" required id="loginPassword">
  <button type="button" class="toggle-password" data-target="loginPassword">👁️</button>
</div>

      <button type="submit" class="btn-primary">Ingresar</button>
      <div id="loginMessage" style="margin-top:10px; font-weight:bold;"></div>
    </form>

    <!-- Loader dentro del modal -->
    <div id="modalLoaderLogin">
      <div class="loader-backdrop">
        <div class="loader-box">
          <div class="loader"></div>
          <p>Verificando credenciales...</p>
        </div>
      </div>
    </div>
  </div>
</div>


     <!-- MODAL DE REGISTRO -->
  <div id="registerModal" class="modal">
    <div class="modal-content">
      <span class="close" id="closeRegister">&times;</span>
      <h3>Crear una cuenta</h3>
      <form id="registerForm" method="POST" action="../src/auth/register.php">
        <div class="form-group">
          <input type="text" name="first_name" placeholder="Nombre" required>
        </div>

        <div class="form-group">
          <input type="text" name="last_name" placeholder="Apellido" required>
        </div>

        <div class="form-group">
          <input type="email" name="email" placeholder="Correo electrónico" required>
        </div>

        <div class="form-group">
          <input type="text" name="restaurant_name" placeholder="Nombre del restaurante" required>
        </div>

        <div class="form-group password-container">
  <input type="password" name="password" placeholder="Contraseña" required minlength="8" id="registerPassword">
  <button type="button" class="toggle-password" data-target="registerPassword">👁️</button>
</div>

<div class="form-group password-container">
  <input type="password" name="confirm_password" placeholder="Confirmar contraseña" required minlength="8" id="confirmPassword">
  <button type="button" class="toggle-password" data-target="confirmPassword">👁️</button>
</div>

        <div class="form-group">
          <label>
            <input type="checkbox" name="accept_terms" required>
            Acepto los <a href="#">términos</a> y la <a href="#">política de privacidad</a>
          </label>
        </div>

        <button type="submit" class="btn-primary">Registrarse</button>
        <div id="registerMessage" style="margin-top:10px; font-weight:bold;"></div>
      </form>

          <!-- Loader aquí dentro -->
    <div id="modalLoader">
      <div class="loader-box">
        <div class="loader"></div>
        <p>Registrando usuario...</p>
      </div>
    </div>

    </div>
  </div>

<!-- MODAL DE INGRESO DE EMPLEADO -->
<div id="codeModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeLogin">&times;</span>
    <h3>Acceso de Empleado</h3>
    <form method="POST" action="/DelixSystem/app/pages/gestor_empleado/php/employee/EmpleadoController.php"  id="empleadoAccessForm">
      <div class="form-group">
        <input 
          type="text" 
          name="codigo_dinamico" 
          placeholder="Código dinámico de acceso" 
          required 
          maxlength="20"
        >
      </div>
       <br>
      <div class="form-group">
        <input 
          type="text" 
          name="nombre_completo" 
          placeholder="Nombre completo" 
          required
        >
      </div>
      <br>
      <div class="form-group">
        <input 
          type="email" 
          name="correo" 
          placeholder="Correo electrónico" 
          required
        >
      </div>
      <br>
      <div class="form-group">
        <input 
          type="text" 
          name="documento" 
          placeholder="Número de documento o cédula" 
          required
        >
      </div>

      <br>

      <button type="submit" class="btn-primary">Unirme al Restaurante</button>

        <p style="margin-top:15px; font-size:0.9rem; color:#555;">
  ¿Ya estás en un restaurante? 
  <a 
    href="#" 
    id="switchToEmployeeLogin" 
    data-modal-target="#employeeLoginModal"
    style="color:#FF6B35; font-weight:600;"
  >
    Entra desde aquí
  </a>
</p>
    </form>
  </div>
</div>

<!-- 🔸 MODAL DE LOGIN DE EMPLEADO -->
<div id="employeeLoginModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeEmployeeLogin">&times;</span>
    <h3>Inicio de Sesión de Empleado</h3>

    <form 
      id="employeeLoginForm" 
      method="POST" 
      action="/DelixSystem/src/auth/login_empleado.php"
    >
      <div class="form-group">
        <input 
          type="text" 
          name="restaurant_name" 
          placeholder="Nombre del restaurante"
          required
        >
      </div>

      <br>

      <div class="form-group">
        <input 
          type="email" 
          name="email" 
          placeholder="Correo electrónico" 
          required
        >
      </div>

      <br>

      <div class="form-group">
        <input 
          type="text" 
          name="document" 
          placeholder="Número de documento o cédula" 
          required
        >
      </div>

      <br>

      <button type="submit" class="btn-primary">Ingresar</button>
    </form>
  </div>
</div>



    <!-- FOOTER -->
    <footer class="footer">
        <div class="container footer-content">
            <p>© 2025 DELIX — Sistema gratuito de gestión de restaurantes</p>
            <p>Hecho con ❤️ para ayudar a los emprendedores gastronómicos.</p>
        </div>
    </footer>




<!-- Otros scripts -->
 <!-- Bootstrap JS (necesario para manejar el modal con JS) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<script src="./js/alert.js" defer></script>
<script src="./js/show_password.js" defer></script>
<script src="./js/modal.js" defer></script>
<script src="./js/register.js" defer></script>
<script src="./js/login.js" defer></script>
<script src="/DelixSystem/app/pages/gestor_empleado/js/empleadoAccess.js" defer></script>




</body>
</html>
