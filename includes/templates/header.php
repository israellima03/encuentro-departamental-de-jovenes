<?php
/* detecta la pagina actual para marcar el menu */
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html class="no-js" lang="">

<head>
  <meta charset="utf-8">
  <title>Encuentro Departamental de Jovenes</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="manifest" href="site.webmanifest">
  <link rel="apple-touch-icon" href="icon.png">
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=PT+Sans:ital,wght@0,400;0,700;1,400;1,700&family=Open+Sans:wght@400;700&display=swap" rel="stylesheet">
  <meta name="theme-color" content="#fafafa">
</head>

<body>
  <!--[if IE]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
  <![endif]-->

  <header class="site-header">
    <div class="hero">

      <div class="logos-laterales">
        <img src="img/escudoidpo.png" class="logo-izq" alt="logo izquierda">
        <img src="img/escudomjo.png" class="logo-der" alt="logo derecha">
      </div>

      <div class="contenido-header">

        <!-- REDES SOCIALES -->
        <nav class="redes-sociales">
          <a href="https://www.facebook.com/share/1EG8kSZLUZ/" target="_blank" title="Facebook">
            <i class="fa-brands fa-facebook-f"></i>
          </a>
          <a href="https://www.instagram.com/" target="_blank" title="Instagram">
            <i class="fa-brands fa-instagram"></i>
          </a>
          <a href="https://www.tiktok.com/@mj.iddp.oruro" target="_blank" title="TikTok">
            <i class="fa-brands fa-tiktok"></i>
          </a>
          <a href="https://chat.whatsapp.com/DDCPO9QTnhqFyCJnjwSOUC" target="_blank" title="Grupo WhatsApp">
            <i class="fa-brands fa-whatsapp"></i>
          </a>
        </nav>

        <div class="informacion-evento">
          <div class="clearfix">
            <p class="fecha"><i class="fa-solid fa-calendar"></i>10/07/2026</p>
            <p class="ciudad"><i class="fa-solid fa-location-dot"></i>Tarija, Bolivia</p>
          </div>
          <h1 class="nombre-sitio">Encuentro departamental de Jovenes</h1>
          <p class="slogan">lema del encuentro o versiculo <span>GALATAS 2:20</span></p>
        </div>

      </div><!-- fin contenido-header -->

      <!-- FLECHA DESLIZAR -->
      <div class="scroll-down">
        <p class="scroll-texto">Desliza</p>
        <div class="scroll-flechas">
          <i class="fa-solid fa-chevron-down"></i>
          <i class="fa-solid fa-chevron-down"></i>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
      </div>

    </div><!--.hero-->
  </header>

  <div class="barra">
    <div class="contenedor clearfix">
      <div class="logo">
        <a href="index.php">
          <img src="img/logo2.svg" alt="logo mjoruro">
        </a>
      </div>
      <div class="menu-movil">
        <span></span>
        <span></span>
        <span></span>
      </div>
      <nav class="navegacion-principal clearfix">
        <a href="conferencia.php"
           <?php echo $pagina_actual === 'conferencia.php' ? 'class="nav-activo"' : ''; ?>>
          Conferencia
        </a>
        <a href="calendario.php"
           <?php echo $pagina_actual === 'calendario.php' ? 'class="nav-activo"' : ''; ?>>
          Calendario
        </a>
        <a href="invitados.php"
           <?php echo $pagina_actual === 'invitados.php' ? 'class="nav-activo"' : ''; ?>>
          Invitados
        </a>
        <a href="tipo_inscripciones.php"
           <?php echo in_array($pagina_actual, ['tipo_inscripciones.php','registro.php']) ? 'class="nav-activo"' : ''; ?>>
          Inscripciones
        </a>
      </nav>
    </div>
  </div><!--barra-->