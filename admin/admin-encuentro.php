<?php
require_once('funciones/sesiones.php');
usuario_autentificado();

header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

include_once 'templates/header.php';
include_once 'templates/navegacion.php';
include_once 'templates/barra.php';
include_once 'funciones/funciones.php';
?>




 

    <!-- CONTENT -->
    <main class="content" id="main-content">

      <!-- ================= PAGE: DASHBOARD ================= -->
      <div class="page active" id="page-dashboard">

        <div class="page-header">
          <div>
            <h1 class="page-title">¡Bienvenido de nuevo, <span id="welcome-name">Administrador</span>!</h1>
            <p class="page-sub">Aquí tienes el resumen logístico del Encuentro Departamental.</p>
          </div>
          <button class="btn-primary">
            <i class="fa-solid fa-plus"></i> Nueva Inscripción
          </button>
        </div>

        <!-- STATS CARDS -->
        <div class="stats-grid">
          <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="stat-body">
              <div class="stat-num" id="stat-total">—</div>
              <div class="stat-lbl">Total Inscritos</div>
              <div class="stat-sub" id="stat-hoy">Cargando...</div>
            </div>
          </div>
          <div class="stat-card stat-orange">
            <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-body">
              <div class="stat-num" id="stat-pendientes">—</div>
              <div class="stat-lbl">Pagos por Confirmar</div>
              <div class="stat-sub warn" id="stat-pendientes-sub">Requiere atención</div>
            </div>
          </div>
          <div class="stat-card stat-green">
            <div class="stat-icon"><i class="fa-solid fa-ticket"></i></div>
            <div class="stat-body">
              <div class="stat-num" id="stat-cupos">—</div>
              <div class="stat-lbl">Cupos Disponibles</div>
              <div class="stat-progress-wrap">
                <div class="stat-progress-bar">
                  <div class="stat-progress-fill" id="stat-cupos-bar"></div>
                </div>
                <span class="stat-progress-pct" id="stat-cupos-pct">0%</span>
              </div>
            </div>
          </div>
          <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-body">
              <div class="stat-num" id="stat-confirmados">—</div>
              <div class="stat-lbl">Confirmados</div>
              <div class="stat-sub" id="stat-conf-sub">pagos verificados</div>
            </div>
          </div>
        </div>

        <!-- GRID INFERIOR -->
        <div class="dash-grid">

          <!-- INSCRIPCIONES RECIENTES -->
          <div class="card card-recientes">
            <div class="card-header">
              <h3><i class="fa-solid fa-bolt"></i> Inscripciones Recientes</h3>
            </div>

            <!-- Filtros rápidos -->
            <div class="filtros-rapidos">
              <input type="text" id="filtro-nombre" placeholder="Buscar nombre o carnet..." class="input-filtro">
              <select id="filtro-iglesia" class="select-filtro">
                <option value="">Todas las iglesias</option>
              </select>
              <select id="filtro-distrito" class="select-filtro">
                <option value="">Todos los distritos</option>
              </select>
              <select id="filtro-estado" class="select-filtro">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="confirmado">Confirmado</option>
              </select>
            </div>

            <!-- Tabla inscritos -->
            <div class="tabla-wrap">
              <table class="tabla-inscritos" id="tabla-inscritos">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Participante</th>
                    <th>Carnet</th>
                    <th>Celular</th>
                    <th>Iglesia</th>
                    <th>Distrito</th>
                    <th>Paquete</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="tbody-inscritos">
                  <tr>
                    <td colspan="11" class="tabla-loading">
                      <i class="fa-solid fa-spinner fa-spin"></i> Cargando...
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Paginación -->
            <div class="paginacion" id="paginacion"></div>

            <!-- Herramientas -->
            <div class="herramientas">
              <h4><i class="fa-solid fa-wrench"></i> Herramientas</h4>
              <div class="herramientas-btns">
                <button class="btn-tool" id="btn-exportar-pdf">
                  <i class="fa-solid fa-file-pdf"></i> Exportar PDF
                </button>
                <button class="btn-tool" id="btn-exportar-csv">
                  <i class="fa-solid fa-file-csv"></i> Exportar CSV
                </button>
                <button class="btn-tool" id="btn-imprimir">
                  <i class="fa-solid fa-print"></i> Imprimir
                </button>
              </div>
            </div>
          </div>

       

        </div><!-- dash-grid -->

      </div><!-- /page-dashboard -->

      <!-- ================= PAGE: CONFIRMAR QR ================= -->
     

      <!-- ================= PAGE: REPORTES ================= -->
     




    </main><!-- /content -->
  </div><!-- /main-wrapper -->

<?php 
   include_once 'templates/footer.php';  
?>
