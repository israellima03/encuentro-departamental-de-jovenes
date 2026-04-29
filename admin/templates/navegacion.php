<?php



/* roles con acceso completo — exactamente como estan en tu BD */
$admin_full = ['Administrador', 'Lider departamental', 'tesorera'];

/* roles con acceso solo a dashboard + preguntas */
$solo_basico = ['Lider distrital', 'Lider local', 'secretario', 'Equipo departamental'];
?>

<body>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon"><i class="fa-solid fa-dove"></i></div>
      <div class="brand-text">
        <span class="brand-name">Encuentro</span>
        <span class="brand-sub">Jóvenes</span>
      </div>
    </div>

    <nav class="sidebar-nav">

      <!-- DASHBOARD — todos los roles -->
      <div class="nav-section-label">Principal</div>
      <a href="admin-encuentro.php" class="nav-item">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Inscritos</span>
      </a>

      <!-- CONFIRMAR QR — solo admin full -->
      <?php if(puede($admin_full)): ?>
      <a href="confirmar-qr.php" class="nav-item"">
        <i class="fa-solid fa-qrcode"></i>
        <span>Confirmar QR</span>
        
      </a>
      <?php endif; ?>

      <!-- GESTION — solo admin full -->
      <?php if(puede($admin_full)): ?>
      <div class="nav-section-label">Gestión</div>
      <a href="#" class="nav-item" ">
        <i class="fa-solid fa-chart-bar"></i>
        <span>Reportes</span>
      </a>
      <a href="#" class="nav-item">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Historial</span>
      </a>
      <a href="#" class="nav-item" >
        <i class="fa-solid fa-shirt"></i>
        <span>Productos</span>
      </a>
      <?php endif; ?>

      <!-- PROGRAMA — solo admin full -->
      <?php if(puede($admin_full)): ?>
      <div class="nav-section-label">Programa</div>
      <div class="nav-item nav-parent" id="nav-programa-toggle">
        <i class="fa-solid fa-calendar-days"></i>
        <span>Programa</span>
        <i class="fa-solid fa-chevron-down nav-arrow"></i>
      </div>
      <div class="nav-submenu" id="submenu-programa">
        <a href="#" class="nav-subitem">
          <i class="fa-solid fa-microphone"></i> Invitados
        </a>
        <a href="#" class="nav-subitem" >
          <i class="fa-solid fa-book-open"></i> Temas
        </a>
        <a href="#" class="nav-subitem" >
          <i class="fa-solid fa-list-check"></i> Agenda
        </a>
      </div>
      <?php endif; ?>

      <!-- SISTEMA — todos los roles -->
      <div class="nav-section-label">Sistema</div>
      <a href="#" class="nav-item">
        <i class="fa-solid fa-circle-question"></i>
        <span>Preguntas</span>
      </a>

      <!-- ADMINISTRADORES — solo Administrador -->
      <?php if(puede(['Administrador'])): ?>
      <div class="nav-section-label">Administradores</div>
      <div class="nav-item nav-parent" id="nav-administradores-toggle">
        <i class="fa-solid fa-user-shield"></i>
        <span>Administradores</span>
        <i class="fa-solid fa-chevron-down nav-arrow"></i>
      </div>
      <div class="nav-submenu" id="submenu-administradores">
        <a href="administradores.php" class="nav-subitem">
          <i class="fa-solid fa-users"></i> Ver Todos
        </a>
        <a href="crear-admin.php" class="nav-subitem">
          <i class="fa-solid fa-user-plus"></i> Agregar
        </a>
      </div>
      <?php endif; ?>

    </nav>

    <div class="sidebar-footer">
      <a href="logout.php" class="btn-cerrar-sesion">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Cerrar Sesión</span>
      </a>
    </div>
  </aside>