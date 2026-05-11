<?php 
   require_once('funciones/sesiones.php');
   usuario_autentificado();
   verificar_acceso(['Administrador']);
   include_once 'templates/header.php'; 
   include_once 'templates/navegacion.php'; 
   include_once 'templates/barra.php';
   include_once 'funciones/funciones.php'; 

   $distritos = [];
   $iglesias  = [];
   $roles     = [];

   $res = $conn->query("SELECT id, nombre FROM distritos ORDER BY nombre");
   if($res) while($r = $res->fetch_assoc()) $distritos[] = $r;

   $res = $conn->query("SELECT id, nombre, distrito_id FROM iglesias ORDER BY nombre");
   if($res) while($r = $res->fetch_assoc()) $iglesias[] = $r;

   $res = $conn->query("SELECT id, nombre FROM roles ORDER BY id");
   if($res) while($r = $res->fetch_assoc()) $roles[] = $r;

   $rol_meta = [
      'Administrador'       => ['icono' => 'fa-crown',           'desc' => 'Acceso total al sistema.'],
      'Lider departamental' => ['icono' => 'fa-star',            'desc' => 'Gestión general del encuentro.'],
      'Lider distrital'     => ['icono' => 'fa-map-pin',         'desc' => 'Gestión del distrito asignado.'],
      'Lider local'         => ['icono' => 'fa-church',          'desc' => 'Solo puede ver inscritos.'],
      'tesorera'            => ['icono' => 'fa-coins',           'desc' => 'Confirmación de pagos.'],
      'Equipo departamental'=> ['icono' => 'fa-users',           'desc' => 'Apoyo en la gestión general.'],
   ];
?>

<main class="content content-crear-admin" id="main-content">

  <div class="ca-header">
    <div class="ca-header-text">
      <h1 class="ca-titulo">
        <i class="fa-solid fa-user-plus ca-titulo-icono"></i>
        Crear Nuevo Administrador
      </h1>
      <p class="ca-subtitulo">Configure los accesos del nuevo miembro del equipo administrativo.</p>
    </div>
    <a href="ver-admin.php" class="ca-btn-volver">
      <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
  </div>

  <form id="form-crear-admin" novalidate>
    <div class="ca-grid">

      <!-- COLUMNA IZQUIERDA -->
      <div class="ca-col-main">

        <!-- Información de Usuario -->
        <div class="ca-card" id="card-usuario">
          <div class="ca-card-head">
            <div class="ca-card-icono"><i class="fa-solid fa-id-badge"></i></div>
            <h2 class="ca-card-titulo">Información de Usuario</h2>
          </div>
          <div class="ca-campos-grid">

            <div class="ca-campo">
              <label class="ca-label">Nombre Completo <span class="ca-req">*</span></label>
              <input type="text" id="nombre" name="nombre" class="ca-input" placeholder="Ej. Juan Pérez" autocomplete="off">
              <span class="ca-error" id="err-nombre"></span>
            </div>

            <div class="ca-campo">
              <label class="ca-label">Nombre de Usuario <span class="ca-req">*</span></label>
              <div class="ca-input-icon-wrap">
                <span class="ca-input-prefix"><i class="fa-solid fa-at"></i></span>
                <input type="text" id="usuario" name="usuario" class="ca-input ca-input-with-prefix" placeholder="jperez" autocomplete="off">
              </div>
              <span class="ca-error" id="err-usuario"></span>
              <span class="ca-hint" id="hint-usuario"></span>
            </div>

            <div class="ca-campo">
              <label class="ca-label">Teléfono</label>
              <input type="text" id="telefono" name="telefono" class="ca-input" placeholder="Ej: 70000000" autocomplete="off">
            </div>

            <div class="ca-campo ca-campo-full">
              <label class="ca-label">Contraseña <span class="ca-req">*</span></label>
              <div class="ca-input-icon-wrap">
                <input type="password" id="password" name="password" class="ca-input ca-input-with-suffix" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                <button type="button" class="ca-toggle-pass" id="btn-ver-pass">
                  <i class="fa-solid fa-eye" id="icono-pass"></i>
                </button>
              </div>
              <div class="ca-pass-strength" id="pass-strength">
                <div class="ca-strength-bar"><div class="ca-strength-fill" id="strength-fill"></div></div>
                <span class="ca-strength-lbl" id="strength-lbl"></span>
              </div>
              <span class="ca-hint">Mínimo 8 caracteres, incluye letras y números.</span>
              <span class="ca-error" id="err-password"></span>
            </div>

            <div class="ca-campo ca-campo-full">
              <label class="ca-label">Confirmar Contraseña <span class="ca-req">*</span></label>
              <div class="ca-input-icon-wrap">
                <input type="password" id="password2" name="password2" class="ca-input ca-input-with-suffix" placeholder="Repite la contraseña" autocomplete="new-password">
                <button type="button" class="ca-toggle-pass" id="btn-ver-pass2">
                  <i class="fa-solid fa-eye" id="icono-pass2"></i>
                </button>
              </div>
              <span class="ca-error" id="err-password2"></span>
            </div>

          </div>
        </div>

        <!-- Afiliación -->
        <div class="ca-card" id="card-afiliacion">
          <div class="ca-card-head">
            <div class="ca-card-icono ca-card-icono-verde"><i class="fa-solid fa-map-location-dot"></i></div>
            <h2 class="ca-card-titulo">Afiliación y Ubicación</h2>
          </div>
          <p class="ca-card-nota">
            <i class="fa-solid fa-circle-info"></i>
            Opcional. Define el distrito e iglesia al que pertenece el administrador.
          </p>
          <div class="ca-campos-grid" style="grid-template-columns:1fr 1fr;">

            <div class="ca-campo">
              <label class="ca-label">Distrito</label>
              <div class="ca-select-wrap">
                <select id="distrito_id" name="distrito_id" class="ca-select">
                  <option value="">Seleccionar Distrito</option>
                  <?php foreach($distritos as $d): ?>
                    <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['nombre']); ?></option>
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
                  <?php foreach($iglesias as $ig): ?>
                    <option value="<?php echo $ig['id']; ?>" data-distrito="<?php echo $ig['distrito_id']; ?>">
                      <?php echo htmlspecialchars($ig['nombre']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <i class="fa-solid fa-chevron-down ca-select-arrow"></i>
              </div>
            </div>

          </div>
        </div>

      </div><!-- col-main -->

      <!-- COLUMNA DERECHA -->
      <div class="ca-col-aside">

        <!-- Roles -->
        <div class="ca-card ca-card-roles" id="card-roles">
          <div class="ca-card-head">
            <div class="ca-card-icono ca-card-icono-rojo"><i class="fa-solid fa-shield-halved"></i></div>
            <h2 class="ca-card-titulo">Asignación de Roles</h2>
          </div>
          <p class="ca-card-nota">
            <i class="fa-solid fa-circle-info"></i>
            Selecciona uno o más roles.
          </p>
          <div class="ca-roles-lista" id="roles-lista">
            <?php foreach($roles as $rol):
              $meta  = $rol_meta[$rol['nombre']] ?? ['icono'=>'fa-user','desc'=>''];
            ?>
              <label class="ca-rol-item" for="rol-<?php echo $rol['id']; ?>">
                <div class="ca-rol-check-wrap">
                  <input type="checkbox" id="rol-<?php echo $rol['id']; ?>" name="roles[]"
                         value="<?php echo $rol['id']; ?>" class="ca-rol-checkbox">
                  <div class="ca-rol-checkbox-custom"><i class="fa-solid fa-check"></i></div>
                </div>
                <div class="ca-rol-icono-wrap">
                  <i class="fa-solid <?php echo $meta['icono']; ?>"></i>
                </div>
                <div class="ca-rol-texto">
                  <span class="ca-rol-nombre"><?php echo htmlspecialchars($rol['nombre']); ?></span>
                  <?php if($meta['desc']): ?>
                    <span class="ca-rol-desc"><?php echo htmlspecialchars($meta['desc']); ?></span>
                  <?php endif; ?>
                </div>
              </label>
            <?php endforeach; ?>
          </div>
          <span class="ca-error" id="err-roles"></span>
          <div class="ca-roles-nota">
            <i class="fa-solid fa-circle-info"></i>
            <p>Los roles definen qué secciones y acciones estarán disponibles para el usuario.</p>
          </div>
        </div>

        <!-- Botones -->
        <div class="ca-acciones">
          <button type="submit" id="btn-crear" class="ca-btn-crear">
            <i class="fa-solid fa-floppy-disk"></i> Crear Usuario
          </button>
          <a href="ver-admin.php" class="ca-btn-cancelar">
            <i class="fa-solid fa-xmark"></i> Cancelar
          </a>
        </div>

        <!-- Preview -->
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

      </div><!-- col-aside -->

    </div>
  </form>

  <div class="ca-toast" id="ca-toast"></div>

</main>

<?php $js_pagina = ['crear-admin.js']; ?>
<?php include_once 'templates/footer.php'; ?>