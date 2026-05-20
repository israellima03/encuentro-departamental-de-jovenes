<?php
require_once('funciones/sesiones.php');
usuario_autentificado();

header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');

require_once('../includes/funciones/bd_conexion.php');
include_once 'templates/header.php';
include_once 'templates/navegacion.php';
include_once 'templates/barra.php';
include_once 'funciones/funciones.php';

$tipo = trim($_GET['tipo'] ?? 'deportivo');
$puede_gestionar = puede(['Administrador','Lider departamental']);
$puede_inscribir = puede(['Administrador','Lider departamental','Lider distrital','tesorera']);
$puede_puntuar   = puede(['Administrador','Lider departamental','Equipo departamental']);
$solo_ver        = !$puede_inscribir;
?>

<main class="content" id="main-content">
<div class="page active">

  <div class="page-header">
    <div>
      <h1 class="page-title">Concursos <span><?php echo $tipo === 'deportivo' ? 'Deportivo' : 'Bíblico'; ?></span></h1>
      <p class="page-sub"><?php echo $tipo === 'deportivo' ? 'Equipos inscritos para la noche deportiva.' : 'Concursantes y puntuaciones del concurso bíblico.'; ?></p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="concursos.php?tipo=deportivo"
         class="btn-<?php echo $tipo==='deportivo'?'primary':'secondary'; ?>">
        <i class="fa-solid fa-futbol"></i> Deportivo
      </a>
      <a href="concursos.php?tipo=biblico"
         class="btn-<?php echo $tipo==='biblico'?'primary':'secondary'; ?>">
        <i class="fa-solid fa-bible"></i> Bíblico
      </a>
      <?php if($puede_inscribir && $tipo === 'biblico'): ?>
      <button class="btn-primary" id="btn-inscribir-biblico">
        <i class="fa-solid fa-plus"></i> <span class="btn-txt-label">Inscribir Concursante</span>
      </button>
      <?php endif; ?>
    </div>
  </div>

  <?php if($tipo === 'deportivo'): ?>
  <!-- ══ SECCIÓN DEPORTIVO ══ -->
  <div class="card">
    <div class="card-header">
      <h3><i class="fa-solid fa-futbol"></i> Equipos Deportivos</h3>
      <span id="dep-total-lbl" class="total-lbl-txt"></span>
    </div>
    <div class="filtros-rapidos">
      <input type="text" id="dep-buscar" placeholder="Buscar equipo..." class="input-filtro">
    </div>
    <div class="tabla-wrap">
      <table class="tabla-inscritos" id="tabla-deportivo">
        <thead>
          <tr>
            <th>#</th>
            <th>Equipo</th>
            <th>Capitán</th>
            <th>Carnet</th>
            <th>Celular</th>
            <th>Iglesia</th>
            <th>Distrito</th>
            <th>Fecha Registro</th>
            <?php if($puede_gestionar): ?><th>Acciones</th><?php endif; ?>
          </tr>
        </thead>
        <tbody id="tbody-deportivo">
          <tr><td colspan="9" class="tabla-loading"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <?php else: ?>
  <!-- ══ SECCIÓN BÍBLICO ══ -->

  <!-- TABLA CONCURSANTES -->
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header">
      <h3><i class="fa-solid fa-bible"></i> Concursantes Inscritos</h3>
      <span id="bib-total-lbl" class="total-lbl-txt"></span>
    </div>
    <div class="filtros-rapidos">
      <input type="text" id="bib-buscar" placeholder="Buscar nombre, carnet..." class="input-filtro">
      <select id="bib-categoria" class="select-filtro">
        <option value="">Todas las categorías</option>
      </select>
      <select id="bib-distrito" class="select-filtro">
        <option value="">Todos los distritos</option>
      </select>
    </div>
    <div class="tabla-wrap">
      <table class="tabla-inscritos" id="tabla-biblico">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Carnet</th>
            <th>Celular</th>
            <th>Iglesia</th>
            <th>Distrito</th>
            <th>Categoría</th>
            <th>Tipo</th>
            <th>Equipo</th>
            <th>Registrado</th>
            <?php if($puede_inscribir): ?><th>Acciones</th><?php endif; ?>
          </tr>
        </thead>
        <tbody id="tbody-biblico">
          <tr><td colspan="11" class="tabla-loading"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</td></tr>
        </tbody>
      </table>
    </div>
    <div class="paginacion" id="paginacion-biblico"></div>
  </div>


  <!-- PUNTUACIONES -->
  <div class="card">
    <div class="card-header">
      <h3><i class="fa-solid fa-ranking-star"></i> Puntuaciones por Fase</h3>
      <div style="display:flex;gap:8px;align-items:center;">
        <select id="punt-categoria" class="select-filtro" style="min-width:160px;">
          <option value="">Selecciona categoría</option>
        </select>
        <select id="punt-fase" class="select-filtro" style="min-width:140px;">
          <option value="">Selecciona fase</option>
        </select>
        <?php if($puede_gestionar): ?>
        <button class="btn-primary" id="btn-nueva-fase" style="white-space:nowrap;">
          <i class="fa-solid fa-plus"></i> Nueva Fase
        </button>
        <?php endif; ?>
      </div>
    </div>
    <div id="punt-contenido" style="padding:20px;">
      <div style="text-align:center;color:var(--txt-xsoft);padding:40px;">
        <i class="fa-solid fa-trophy" style="font-size:2em;display:block;margin-bottom:10px;color:var(--border);"></i>
        Selecciona una categoría y fase para ver las puntuaciones
      </div>
    </div>
  </div>

  <?php endif; ?>

  <!-- ══ SECCIÓN CATEGORÍAS ══ -->
  <?php if($tipo === 'biblico' && $puede_gestionar): ?>
  <div class="card" style="margin-top:20px;">
    <div class="card-header">
      <h3><i class="fa-solid fa-list-check"></i> Categorías del Concurso</h3>
      <button class="btn-primary" id="btn-nueva-categoria">
        <i class="fa-solid fa-plus"></i> <span class="btn-txt-label">Nueva Categoría</span>
      </button>
    </div>
    <div class="tabla-wrap">
      <table class="tabla-inscritos" id="tabla-categorias">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Máx. por Distrito</th>
            <th>Máx. por Equipo</th>
            <th>Orden</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tbody-categorias">
          <tr><td colspan="8" class="tabla-loading"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>


</div>
</main>

<?php if($tipo === 'deportivo'): ?>
<!-- MODAL EDITAR EQUIPO -->
<?php if($puede_gestionar): ?>
<div class="modal-overlay" id="modal-dep-overlay" style="display:none;">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-pen"></i> Editar Equipo</h3>
      <button class="modal-close" onclick="cerrarModalDep()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="dep-edit-id">
      <div class="modal-field">
        <span class="modal-label">Nombre del Equipo *</span>
        <input type="text" id="dep-edit-nombre" class="input-filtro" style="width:100%;margin-top:6px;">
        <span id="dep-edit-msg" style="font-size:12px;margin-top:4px;display:none;"></span>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="cerrarModalDep()">Cancelar</button>
      <button class="btn-primary" id="btn-dep-guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>
<?php endif; ?>

<?php else: ?>
<!-- MODAL INSCRIBIR BÍBLICO -->
<?php if($puede_inscribir): ?>
<div class="modal-overlay" id="modal-bib-overlay" style="display:none;">
  <div class="modal" style="max-width:520px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-user-plus"></i> Inscribir Concursante</h3>
      <button class="modal-close" onclick="cerrarModalBib()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="modal-grid" style="grid-template-columns:1fr;">

        <div class="modal-field">
          <span class="modal-label">Buscar Inscrito (carnet o nombre) *</span>
          <input type="text" id="bib-ins-buscar" class="input-filtro" style="width:100%;margin-top:6px;"
                 placeholder="Escribe el carnet o nombre...">
          <div id="bib-ins-resultados" style="margin-top:4px;"></div>
          <input type="hidden" id="bib-ins-id">
          <div id="bib-ins-sel" style="display:none;margin-top:8px;background:#d1fae5;border-radius:8px;padding:10px 14px;font-size:13px;color:#065f46;font-weight:600;">
            <i class="fa-solid fa-circle-check"></i> <span id="bib-ins-nombre"></span>
          </div>
        </div>

        <div class="modal-field">
          <span class="modal-label">Categoría *</span>
          <select id="bib-ins-categoria" class="select-filtro" style="width:100%;margin-top:6px;">
            <option value="">-- Selecciona --</option>
          </select>
        </div>

        <div id="bib-ins-equipo-wrap" style="display:none;">
          <div class="modal-field">
            <span class="modal-label">Nombre del Equipo</span>
            <input type="text" id="bib-ins-equipo" class="input-filtro" style="width:100%;margin-top:6px;"
                   placeholder="Nombre del equipo o grupo">
          </div>
        </div>

        <div id="bib-ins-aviso" style="display:none;background:#fef9c3;border-left:4px solid #f59e0b;border-radius:6px;padding:10px 14px;font-size:13px;color:#92400e;"></div>

      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="cerrarModalBib()">Cancelar</button>
      <button class="btn-primary" id="btn-bib-guardar"><i class="fa-solid fa-check"></i> Inscribir</button>
    </div>
  </div>
</div>

<!-- MODAL EDITAR CONCURSANTE -->
<div class="modal-overlay" id="modal-bib-edit-overlay" style="display:none;">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-pen"></i> Editar Concursante</h3>
      <button class="modal-close" onclick="cerrarModalBibEdit()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="bib-edit-id">
      <div class="modal-field" style="margin-bottom:12px;">
        <span class="modal-label">Categoría</span>
        <select id="bib-edit-categoria" class="select-filtro" style="width:100%;margin-top:6px;">
          <option value="">-- Selecciona --</option>
        </select>
      </div>
      <div class="modal-field">
        <span class="modal-label">Nombre de Equipo (si aplica)</span>
        <input type="text" id="bib-edit-equipo" class="input-filtro" style="width:100%;margin-top:6px;">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="cerrarModalBibEdit()">Cancelar</button>
      <button class="btn-primary" id="btn-bib-edit-guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- MODAL NUEVA FASE -->
<?php if($puede_gestionar): ?>
<div class="modal-overlay" id="modal-fase-overlay" style="display:none;">
  <div class="modal" style="max-width:380px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-flag"></i> Nueva Fase</h3>
      <button class="modal-close" onclick="cerrarModalFase()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="fase-categoria-id">
      <div class="modal-field">
        <span class="modal-label">Nombre de la Fase *</span>
        <input type="text" id="fase-nombre" class="input-filtro" style="width:100%;margin-top:6px;"
               placeholder="Ej: Fase 1, Semifinal, Final">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="cerrarModalFase()">Cancelar</button>
      <button class="btn-primary" id="btn-fase-guardar"><i class="fa-solid fa-plus"></i> Crear Fase</button>
    </div>
  </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php if($tipo === 'biblico' && $puede_gestionar): ?>
<!-- MODAL CATEGORÍA -->
<div class="modal-overlay" id="modal-cat-overlay" style="display:none;">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-list-check"></i> <span id="cat-modal-titulo">Nueva Categoría</span></h3>
      <button class="modal-close" onclick="cerrarModalCat()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="cat-id">
      <div class="modal-grid" style="grid-template-columns:1fr 1fr;gap:14px;">

        <div class="modal-field full">
          <span class="modal-label">Nombre *</span>
          <input type="text" id="cat-nombre" class="input-filtro" style="width:100%;margin-top:6px;" placeholder="Ej: Declamación">
        </div>

        <div class="modal-field">
          <span class="modal-label">Tipo *</span>
          <select id="cat-tipo" class="select-filtro" style="width:100%;margin-top:6px;">
            <option value="individual">Individual</option>
            <option value="grupal">Grupal</option>
          </select>
        </div>

        <div class="modal-field">
          <span class="modal-label">Orden</span>
          <input type="number" id="cat-orden" class="input-filtro" style="width:100%;margin-top:6px;" min="0" value="0">
        </div>

        <div class="modal-field">
          <span class="modal-label">Máx. por Distrito</span>
          <input type="number" id="cat-max-distrito" class="input-filtro" style="width:100%;margin-top:6px;" min="1" value="1" placeholder="1">
        </div>

        <div class="modal-field" id="cat-max-equipo-wrap">
          <span class="modal-label">Máx. por Equipo</span>
          <input type="number" id="cat-max-equipo" class="input-filtro" style="width:100%;margin-top:6px;" min="1" placeholder="Dejar vacío si no aplica">
        </div>

        <div class="modal-field full">
          <span class="modal-label">Estado</span>
          <select id="cat-activo" class="select-filtro" style="width:100%;margin-top:6px;">
            <option value="1">Activa</option>
            <option value="0">Inactiva</option>
          </select>
        </div>

      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="cerrarModalCat()">Cancelar</button>
      <button class="btn-primary" id="btn-cat-guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- TOAST -->
<div class="toast" id="toast-concursos"></div>

<script>
var PUEDE_GESTIONAR = <?php echo $puede_gestionar ? 'true' : 'false'; ?>;
var PUEDE_INSCRIBIR = <?php echo $puede_inscribir ? 'true' : 'false'; ?>;
var PUEDE_PUNTUAR   = <?php echo $puede_puntuar   ? 'true' : 'false'; ?>;
var TIPO_CONCURSO   = '<?php echo $tipo; ?>';
</script>
<?php
$css_extra = '<link rel="stylesheet" href="css/concursos.css">';
?>
<?php $js_pagina = ['concursos.js']; ?>
<?php include_once 'templates/footer.php'; ?>