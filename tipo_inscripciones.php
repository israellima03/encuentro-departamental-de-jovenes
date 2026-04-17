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
              <span class="lider-nombre">Hno. Carlos Mamani</span>
              <span class="lider-zona">Iglesia Central Oruro</span>
            </div>
            <a href="https://wa.me/59171234567" target="_blank" class="boton-whatsapp">
              <i class="fa-brands fa-whatsapp"></i> 71234567
            </a>
          </li>
          <li class="lider-item">
            <div class="lider-info">
              <span class="lider-nombre">Hna. Maria Quispe</span>
              <span class="lider-zona">Iglesia Norte Oruro</span>
            </div>
            <a href="https://wa.me/59172345678" target="_blank" class="boton-whatsapp">
              <i class="fa-brands fa-whatsapp"></i> 72345678
            </a>
          </li>
          <li class="lider-item">
            <div class="lider-info">
              <span class="lider-nombre">Ptr. Roberto Flores</span>
              <span class="lider-zona">Iglesia Sur Oruro</span>
            </div>
            <a href="https://wa.me/59173456789" target="_blank" class="boton-whatsapp">
              <i class="fa-brands fa-whatsapp"></i> 73456789
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
              <span class="lider-nombre">Obispo Juan Condori</span>
              <span class="lider-zona">Distrito Oruro Norte</span>
            </div>
            <a href="https://wa.me/59174567890" target="_blank" class="boton-whatsapp">
              <i class="fa-brands fa-whatsapp"></i> 74567890
            </a>
          </li>
          <li class="lider-item">
            <div class="lider-info">
              <span class="lider-nombre">Ptr. Ana Gutierrez</span>
              <span class="lider-zona">Distrito Oruro Sur</span>
            </div>
            <a href="https://wa.me/59175678901" target="_blank" class="boton-whatsapp">
              <i class="fa-brands fa-whatsapp"></i> 75678901
            </a>
          </li>
        </ul>
      </div>

      <div class="aviso-efectivo">
        <i class="fa-solid fa-triangle-exclamation"></i>
        Solo tu líder puede completar el registro en efectivo.
      </div>

    </div>

  </div>
</section>

<?php include_once 'includes/templates/footer.php'; ?>