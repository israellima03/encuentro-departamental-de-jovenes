<?php include_once 'includes/templates/header.php'; ?>

<section class="seccion contenedor">
  <h2>Tipo de Inscripción</h2>

  <p class="subtitulo-inscripcion">
    Elige cómo deseas realizar tu inscripción al Encuentro Departamental
  </p>

  <div class="grid-inscripcion">

    <!-- OPCION QR / BANCA MOVIL -->
    <div class="card-inscripcion card-qr">
      <div class="card-ins-icono">
        <i class="fa-solid fa-qrcode"></i>
      </div>
      <h3 class="card-ins-titulo">Pago por QR o Banca Móvil</h3>
      <div class="card-ins-separador"></div>
      <p class="card-ins-descripcion">
        Si cuentas con <strong>banca móvil</strong> o cualquier aplicación de pago QR,
        puedes inscribirte tú mismo de forma rápida y segura.
      </p>
      <ul class="card-ins-pasos">
        <li><i class="fa-solid fa-circle-check"></i> Escanea el QR o paga por banca móvil</li>
        <li><i class="fa-solid fa-circle-check"></i> Completa el formulario de registro</li>
        <li><i class="fa-solid fa-circle-check"></i> Guarda tu comprobante</li>
      </ul>
      <a href="registro.php" class="button card-ins-boton">
        <i class="fa-solid fa-arrow-right"></i> Ir al Registro
      </a>
    </div>

    <!-- OPCION EFECTIVO -->
    <div class="card-inscripcion card-efectivo">
      <div class="card-ins-icono">
        <i class="fa-solid fa-money-bills"></i>
      </div>
      <h3 class="card-ins-titulo">Pago en Efectivo</h3>
      <div class="card-ins-separador"></div>
      <p class="card-ins-descripcion">
        Si pagas en <strong>efectivo</strong>, el registro debe ser realizado
        por tu líder local o distrital. Contáctalos por WhatsApp:
      </p>

      <!-- LIDERES LOCALES -->
      <div class="lideres-grupo">
        <h4 class="lideres-titulo">
          <i class="fa-solid fa-users"></i> Líderes Locales
        </h4>
        <ul class="lista-lideres">
          <li class="lider-item">
            <div class="lider-info">
              <span class="lider-nombre">Hno. Joel Paredez</span>
              <span class="lider-zona">Iglesia Vinto</span>
            </div>
            <a href="https://wa.me/59168319277?text=Hola%20soy%20un%20joven%20interesado%20en%20el%20encuentro%20y%20quiero%20más%20información" target="_blank" class="boton-whatsapp">
              <i class="fa-brands fa-whatsapp"></i> 68319277
            </a>
          </li>
          <li class="lider-item">
            <div class="lider-info">
              <span class="lider-nombre">Gabriel Mariscal</span>
              <span class="lider-zona">Iglesia San Isidro</span>
            </div>
            <a href="https://wa.me/59168319277?text=Hola%20soy%20un%20joven%20interesado%20en%20el%20encuentro%20y%20quiero%20más%20información" target="_blank" class="boton-whatsapp">
              <i class="fa-brands fa-whatsapp"></i> 68319277
            </a>
          </li>
          <li class="lider-item">
            <div class="lider-info">
              <span class="lider-nombre">Nombre los demas lideres</span>
              <span class="lider-zona">Iglesia Sur Oruro</span>
            </div>
            <a href="https://wa.me/59168319277?text=Hola%20soy%20un%20joven%20interesado%20en%20el%20encuentro%20y%20quiero%20más%20información" target="_blank" class="boton-whatsapp">
              <i class="fa-brands fa-whatsapp"></i> 68319277
            </a>
          </li>
        </ul>
      </div>

      <!-- LIDERES DISTRITALES -->
      <div class="lideres-grupo">
        <h4 class="lideres-titulo">
          <i class="fa-solid fa-building-columns"></i> Líderes Distritales
        </h4>
        <ul class="lista-lideres">
          <li class="lider-item">
            <div class="lider-info">
              <span class="lider-nombre">Hrn Iddy Acno</span>
              <span class="lider-zona">Distrito 1</span>
            </div>
            <a href="https://wa.me/59168319277?text=Hola%20soy%20un%20joven%20interesado%20en%20el%20encuentro%20y%20quiero%20más%20información" target="_blank" class="boton-whatsapp">
              <i class="fa-brands fa-whatsapp"></i> 68319277
            </a>
          </li>
          <li class="lider-item">
            <div class="lider-info">
              <span class="lider-nombre">Hrn Josue Beltran</span>
              <span class="lider-zona">Distrito 2</span>
            </div>
            <a href="https://wa.me/59168319277?text=Hola%20soy%20un%20joven%20interesado%20en%20el%20encuentro%20y%20quiero%20más%20información" target="_blank" class="boton-whatsapp">
              <i class="fa-brands fa-whatsapp"></i> 68319277
            </a>
          </li>
          <li class="lider-item">
            <div class="lider-info">
              <span class="lider-nombre">Hrn Jhuliza Espinoza</span>
              <span class="lider-zona">Distrito 2 (lider de adolencentes)</span>
            </div>
            <a href="https://wa.me/59168319277?text=Hola%20soy%20un%20joven%20interesado%20en%20el%20encuentro%20y%20quiero%20más%20información" target="_blank" class="boton-whatsapp">
              <i class="fa-brands fa-whatsapp"></i> 68319277
            </a>
          </li>
        </ul>
      </div>

      <div class="aviso-efectivo">
        <i class="fa-solid fa-triangle-exclamation"></i>
        Solo tu líder puede completar el registro en efectivo.
      </div>
      <!-- BOTON LOGIN LIDERES -->
      <div class="bloque-login-lider">
        <div class="separador-login"></div>
        <p class="texto-login-lider">
          <i class="fa-solid fa-lock"></i>
          ¿Eres lider? Ingresa con tu cuenta para registrar jovenes en efectivo
        </p>
        <a href="login.php" class="button button-login-lider">
          <i class="fa-solid fa-right-to-bracket"></i> Acceso Lideres
        </a>
        <p class="aviso-solo-lider">
          <i class="fa-solid fa-triangle-exclamation"></i>
          Solo el lider autorizado puede ingresar al sistema de registro
        </p>
      </div>

    </div>

  </div>
</section>

<?php include_once 'includes/templates/footer.php'; ?>