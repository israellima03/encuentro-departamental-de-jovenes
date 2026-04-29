<?php
/*
 * barra.php — Topbar del panel de administración
 *
 * CORRECCIONES:
 *  1. Eliminada la redeclaración de puede() — ya está en sesiones.php
 *  2. Se inyectan los datos de sesión en window.__ADMIN para que
 *     admin.js los use sin depender de sessionStorage
 */
$nombre_admin  = $_SESSION['nombre'] ?? 'Administrador';
$rol_principal = $_SESSION['rol']    ?? 'Admin';
$inicial       = strtoupper(substr($nombre_admin, 0, 1));
?>

  <div class="main-wrapper">

    <!-- Script puente: pasa datos PHP → JavaScript de forma segura -->
    <script>
      window.__ADMIN = {
        nombre: <?php echo json_encode($nombre_admin); ?>,
        rol:    <?php echo json_encode(strtoupper($rol_principal)); ?>,
        roles:  <?php echo json_encode($_SESSION['roles'] ?? []); ?>
      };
    </script>

    <header class="topbar">
      <div class="topbar-left">
        <button class="btn-menu-toggle" id="btn-toggle-sidebar">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>

      <div class="topbar-right">
        <button class="topbar-btn" id="btn-notif" title="Notificaciones">
          <i class="fa-solid fa-bell"></i>
          <span class="notif-dot" id="notif-dot"></span>
        </button>
        <button class="topbar-btn" title="Ayuda">
          <i class="fa-solid fa-circle-question"></i>
        </button>
        <div class="user-chip">
          <div class="user-avatar">
            <?php echo htmlspecialchars($inicial); ?>
          </div>
          <div class="user-info">
            <span class="user-name">
              <?php echo htmlspecialchars($nombre_admin); ?>
            </span>
            <span class="user-role">
              <?php echo strtoupper(htmlspecialchars($rol_principal)); ?>
            </span>
          </div>
        </div>
      </div>
    </header>