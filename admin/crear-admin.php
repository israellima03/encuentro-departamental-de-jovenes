<?php 
   require_once('funciones/sesiones.php');
   usuario_autentificado();
   verificar_acceso(['Administrador']);
   include_once 'templates/header.php'; 
   include_once 'templates/navegacion.php'; 
   include_once 'templates/barra.php';
   

   include_once 'funciones/funciones.php'; 

   /* ============================================================
      DATOS PARA LOS SELECTS — se rellenan desde la BD
      Cuando integres PHP real, reemplaza estos arrays con queries
      ============================================================ */
  

   $distritos   = [];
   $iglesias    = [];
   $ministerios = [];
   $roles       = [];

   $res = $conn->query("SELECT id, nombre FROM distritos ORDER BY nombre");
   if ($res) while ($r = $res->fetch_assoc()) $distritos[] = $r;

   $res = $conn->query("SELECT id, nombre, distrito_id FROM iglesias ORDER BY nombre");
   if ($res) while ($r = $res->fetch_assoc()) $iglesias[] = $r;

   $res = $conn->query("SELECT id, nombre, iglesia_id FROM ministerios ORDER BY nombre");
   if ($res) while ($r = $res->fetch_assoc()) $ministerios[] = $r;

   $res = $conn->query("SELECT id, nombre FROM roles ORDER BY id");
   if ($res) while ($r = $res->fetch_assoc()) $roles[] = $r;

   /* Íconos y descripciones por nombre de rol */
   $rol_meta = [
      'Super Admin' => ['icono' => 'fa-crown',      'desc' => 'Acceso total a todas las funciones del sistema.'],
      'Tesorera'    => ['icono' => 'fa-coins',       'desc' => 'Confirmación de pagos y gestión financiera.'],
      'Registro'    => ['icono' => 'fa-clipboard-user', 'desc' => 'Gestión de inscripciones y datos personales.'],
      'Logística'   => ['icono' => 'fa-boxes-stacked', 'desc' => 'Control de inventario, QR y programa.'],
      'Líder Local' => ['icono' => 'fa-church',      'desc' => 'Inscribir jóvenes de su iglesia en efectivo.'],
   ];
?>

    <main class="content content-crear-admin" id="main-content">

      <!-- ===== ENCABEZADO ===== -->
      <div class="ca-header">
        <div class="ca-header-text">
          <h1 class="ca-titulo">
            <i class="fa-solid fa-user-plus ca-titulo-icono"></i>
            Crear Nuevo Administrador
          </h1>
          <p class="ca-subtitulo">Configure los accesos y la identidad del nuevo miembro del equipo administrativo.</p>
        </div>
        <a href="admin-encuentro.php" class="ca-btn-volver">
          <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
      </div>

      <!-- ===== FORMULARIO ===== -->
      <form id="form-crear-admin" novalidate>

        <div class="ca-grid">

          <!-- =========== COLUMNA IZQUIERDA =========== -->
          <div class="ca-col-main">

            <!-- BLOQUE: Información de Usuario -->
            <div class="ca-card" id="card-usuario">
              <div class="ca-card-head">
                <div class="ca-card-icono">
                  <i class="fa-solid fa-id-badge"></i>
                </div>
                <h2 class="ca-card-titulo">Información de Usuario</h2>
              </div>

              <div class="ca-campos-grid">

                <div class="ca-campo">
                  <label class="ca-label">Nombre Completo <span class="ca-req">*</span></label>
                  <input type="text" id="nombre" name="nombre"
                         class="ca-input" placeholder="Ej. Juan Pérez" autocomplete="off">
                  <span class="ca-error" id="err-nombre"></span>
                </div>

                <div class="ca-campo">
                  <label class="ca-label">Nombre de Usuario <span class="ca-req">*</span></label>
                  <div class="ca-input-icon-wrap">
                    <span class="ca-input-prefix"><i class="fa-solid fa-at"></i></span>
                    <input type="text" id="usuario" name="usuario"
                           class="ca-input ca-input-with-prefix" placeholder="jperez" autocomplete="off">
                  </div>
                  <span class="ca-error" id="err-usuario"></span>
                  <span class="ca-hint" id="hint-usuario"></span>
                </div>

                <div class="ca-campo ca-campo-full">
                  <label class="ca-label">Contraseña <span class="ca-req">*</span></label>
                  <div class="ca-input-icon-wrap">
                    <input type="password" id="password" name="password"
                           class="ca-input ca-input-with-suffix"
                           placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                    <button type="button" class="ca-toggle-pass" id="btn-ver-pass" title="Mostrar/ocultar contraseña">
                      <i class="fa-solid fa-eye" id="icono-pass"></i>
                    </button>
                  </div>
                  <div class="ca-pass-strength" id="pass-strength">
                    <div class="ca-strength-bar">
                      <div class="ca-strength-fill" id="strength-fill"></div>
                    </div>
                    <span class="ca-strength-lbl" id="strength-lbl"></span>
                  </div>
                  <span class="ca-hint">Mínimo 8 caracteres, incluye letras y números.</span>
                  <span class="ca-error" id="err-password"></span>
                </div>

                <div class="ca-campo ca-campo-full">
                  <label class="ca-label">Confirmar Contraseña <span class="ca-req">*</span></label>
                  <div class="ca-input-icon-wrap">
                    <input type="password" id="password2" name="password2"
                           class="ca-input ca-input-with-suffix"
                           placeholder="Repite la contraseña" autocomplete="new-password">
                    <button type="button" class="ca-toggle-pass" id="btn-ver-pass2">
                      <i class="fa-solid fa-eye" id="icono-pass2"></i>
                    </button>
                  </div>
                  <span class="ca-error" id="err-password2"></span>
                </div>

              </div><!-- ca-campos-grid -->
            </div><!-- card-usuario -->

            <!-- BLOQUE: Afiliación y Ubicación -->
            <div class="ca-card" id="card-afiliacion">
              <div class="ca-card-head">
                <div class="ca-card-icono ca-card-icono-verde">
                  <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <h2 class="ca-card-titulo">Afiliación y Ubicación</h2>
              </div>

              <p class="ca-card-nota">
                <i class="fa-solid fa-circle-info"></i>
                Estos datos son opcionales. Definen la iglesia o distrito al que pertenece el administrador.
              </p>

              <div class="ca-campos-grid ca-campos-3col">

                <div class="ca-campo">
                  <label class="ca-label">Distrito</label>
                  <div class="ca-select-wrap">
                    <select id="distrito_id" name="distrito_id" class="ca-select">
                      <option value="">Seleccionar Distrito</option>
                      <?php foreach ($distritos as $d): ?>
                        <option value="<?php echo $d['id']; ?>">
                          <?php echo htmlspecialchars($d['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <i class="fa-solid fa-chevron-down ca-select-arrow"></i>
                  </div>
                </div>

                <div class="ca-campo">
                  <label class="ca-label">Iglesia</label>
                  <div class="ca-select-wrap">
                    <select id="iglesia_id" name="iglesia_id" class="ca-select">
                      <option value="">Seleccionar Iglesia</option>
                      <?php foreach ($iglesias as $i): ?>
                        <option value="<?php echo $i['id']; ?>"
                                data-distrito="<?php echo $i['distrito_id']; ?>">
                          <?php echo htmlspecialchars($i['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <i class="fa-solid fa-chevron-down ca-select-arrow"></i>
                  </div>
                </div>

                <div class="ca-campo">
                  <label class="ca-label">Ministerio</label>
                  <div class="ca-select-wrap">
                    <select id="ministerio_id" name="ministerio_id" class="ca-select">
                      <option value="">Seleccionar Ministerio</option>
                      <?php foreach ($ministerios as $m): ?>
                        <option value="<?php echo $m['id']; ?>"
                                data-iglesia="<?php echo $m['iglesia_id']; ?>">
                          <?php echo htmlspecialchars($m['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <i class="fa-solid fa-chevron-down ca-select-arrow"></i>
                  </div>
                </div>

              </div><!-- ca-campos-3col -->
            </div><!-- card-afiliacion -->

          </div><!-- ca-col-main -->

          <!-- =========== COLUMNA DERECHA =========== -->
          <div class="ca-col-aside">

            <!-- BLOQUE: Asignación de Roles -->
            <div class="ca-card ca-card-roles" id="card-roles">
              <div class="ca-card-head">
                <div class="ca-card-icono ca-card-icono-rojo">
                  <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h2 class="ca-card-titulo">Asignación de Roles</h2>
              </div>

              <p class="ca-card-nota">
                <i class="fa-solid fa-circle-info"></i>
                Selecciona uno o más roles. Los roles definen qué pestañas y acciones estarán disponibles.
              </p>

              <div class="ca-roles-lista" id="roles-lista">
                <?php if (empty($roles)): ?>
                  <!-- Roles por defecto si la BD está vacía -->
                  <?php
                    $roles_default = [
                      ['id'=>1,'nombre'=>'Super Admin'],
                      ['id'=>2,'nombre'=>'Tesorera'],
                      ['id'=>3,'nombre'=>'Registro'],
                      ['id'=>4,'nombre'=>'Logística'],
                      ['id'=>5,'nombre'=>'Líder Local'],
                    ];
                    $roles = $roles_default;
                  ?>
                <?php endif; ?>

                <?php foreach ($roles as $rol):
                  $meta  = $rol_meta[$rol['nombre']] ?? ['icono' => 'fa-user', 'desc' => ''];
                  $icono = $meta['icono'];
                  $desc  = $meta['desc'];
                ?>
                  <label class="ca-rol-item" for="rol-<?php echo $rol['id']; ?>">
                    <div class="ca-rol-check-wrap">
                      <input type="checkbox"
                             id="rol-<?php echo $rol['id']; ?>"
                             name="roles[]"
                             value="<?php echo $rol['id']; ?>"
                             class="ca-rol-checkbox">
                      <div class="ca-rol-checkbox-custom">
                        <i class="fa-solid fa-check"></i>
                      </div>
                    </div>
                    <div class="ca-rol-icono-wrap">
                      <i class="fa-solid <?php echo $icono; ?>"></i>
                    </div>
                    <div class="ca-rol-texto">
                      <span class="ca-rol-nombre"><?php echo htmlspecialchars($rol['nombre']); ?></span>
                      <?php if ($desc): ?>
                        <span class="ca-rol-desc"><?php echo htmlspecialchars($desc); ?></span>
                      <?php endif; ?>
                    </div>
                  </label>
                <?php endforeach; ?>
              </div>

              <span class="ca-error" id="err-roles"></span>

              <!-- Nota informativa -->
              <div class="ca-roles-nota">
                <i class="fa-solid fa-circle-info"></i>
                <p>Los roles definen qué pestañas y acciones estarán disponibles para el usuario en su portal de administración.</p>
              </div>
            </div><!-- card-roles -->

            <!-- BOTONES DE ACCIÓN -->
            <div class="ca-acciones">
              <button type="submit" id="btn-crear" class="ca-btn-crear">
                <i class="fa-solid fa-floppy-disk"></i>
                Crear Usuario
              </button>
              <a href="crear-admin.php" class="ca-btn-cancelar">
                <i class="fa-solid fa-xmark"></i> Cancelar
              </a>
            </div>

            <!-- Resumen en tiempo real -->
            <div class="ca-preview" id="ca-preview" style="display:none;">
              <div class="ca-preview-head">
                <div class="ca-preview-avatar" id="prev-avatar">?</div>
                <div>
                  <div class="ca-preview-nombre" id="prev-nombre">Nuevo Usuario</div>
                  <div class="ca-preview-usuario" id="prev-usuario">@usuario</div>
                </div>
              </div>
              <div class="ca-preview-roles" id="prev-roles"></div>
            </div>

          </div><!-- ca-col-aside -->

        </div><!-- ca-grid -->

      </form><!-- form-crear-admin -->

      <!-- Toast -->
      <div class="ca-toast" id="ca-toast"></div>

    </main>

    <script src="js/crear-admin.js"></script>

<?php include_once 'templates/footer.php'; ?>