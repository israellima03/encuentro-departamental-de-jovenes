<?php
require_once('funciones/sesiones.php');
usuario_autentificado();
verificar_acceso(['Administrador','Lider departamental','tesorera']);

header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

require_once('../includes/funciones/bd_conexion.php');

include_once 'templates/header.php';
include_once 'templates/navegacion.php';
include_once 'templates/barra.php';
include_once 'funciones/funciones.php';
?>


<main class="content" id="main-content">
<div class="page active" id="page-editar-cupos">

  <div class="page-header">
    <div>
      <h1 class="page-title">Gestión <span>General</span></h1>
      <p class="page-sub">Administra paquetes, iglesias, tipos de inscrito y el estado del sistema.</p>
    </div>
  </div>

  <!-- ══ BOTÓN DE EMERGENCIA ══ -->
  <div class="emergencia-wrap" id="emergencia-wrap">
    <div class="emergencia-estado" id="emergencia-estado">
      <div class="emergencia-icono" id="emergencia-icono">
        <i class="fa-solid fa-shield-halved"></i>
      </div>
      <div class="emergencia-info">
        <h3 id="emergencia-titulo">Sistema de Inscripciones: ACTIVO</h3>
        <p id="emergencia-desc">Las inscripciones están habilitadas. Presiona el botón para pausarlas.</p>
      </div>
    </div>
    <button class="btn-emergencia" id="btn-emergencia">
      <i class="fa-solid fa-circle-stop" id="emergencia-btn-icon"></i>
      <span id="emergencia-btn-txt">Pausar Inscripciones</span>
    </button>
  </div>

  <!-- ══ GRID PRINCIPAL ══ -->
  <div class="gestion-grid">

    <!-- ── PAQUETES ── -->
    <div class="card gestion-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-box-open"></i> Paquetes</h3>
        <button class="btn-secondary btn-sm" id="btn-nuevo-paquete">
          <i class="fa-solid fa-plus"></i> Nuevo
        </button>
      </div>
      <div class="tabla-wrap">
        <table class="tabla-inscritos" id="tabla-paquetes">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Precio</th>
              <th>Cupos</th>
              <th>Disp.</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody id="tbody-paquetes">
            <tr><td colspan="5" class="tabla-loading"><i class="fa-solid fa-spinner fa-spin"></i></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── DESCUENTOS / PROMOCIONES ── -->
    <div class="card gestion-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-tag"></i> Promociones</h3>
        <button class="btn-secondary btn-sm" id="btn-nueva-promo">
          <i class="fa-solid fa-plus"></i> Nueva
        </button>
      </div>
      <div class="tabla-wrap">
        <table class="tabla-inscritos" id="tabla-promos">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>%</th>
              <th>Hasta</th>
              <th>Estado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody id="tbody-promos">
            <tr><td colspan="5" class="tabla-loading"><i class="fa-solid fa-spinner fa-spin"></i></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── TIPOS DE INSCRITO ── -->
    <div class="card gestion-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-id-badge"></i> Tipos de Inscrito</h3>
        <button class="btn-secondary btn-sm" id="btn-nuevo-tipo">
          <i class="fa-solid fa-plus"></i> Nuevo
        </button>
      </div>
      <div class="tabla-wrap">
        <table class="tabla-inscritos" id="tabla-tipos">
          <thead>
            <tr><th>Nombre</th><th>Acción</th></tr>
          </thead>
          <tbody id="tbody-tipos">
            <tr><td colspan="2" class="tabla-loading"><i class="fa-solid fa-spinner fa-spin"></i></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── IGLESIAS ── -->
    <div class="card gestion-card iglesias-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-church"></i> Iglesias</h3>
        <button class="btn-secondary btn-sm" id="btn-nueva-iglesia">
          <i class="fa-solid fa-plus"></i> Nueva
        </button>
      </div>
      <!-- filtro iglesias -->
      <div style="padding:10px 16px;border-bottom:1px solid var(--border);">
        <input type="text" id="buscar-iglesia" class="input-filtro" placeholder="Buscar iglesia o distrito..." style="width:100%;">
      </div>
      <div class="tabla-wrap">
        <table class="tabla-inscritos" id="tabla-iglesias">
          <thead>
            <tr>
              <th>Iglesia</th>
              <th>Distrito</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody id="tbody-iglesias">
            <tr><td colspan="3" class="tabla-loading"><i class="fa-solid fa-spinner fa-spin"></i></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── QR DE PAGO ── -->
    <div class="card gestion-card iglesias-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-qrcode"></i> Imagen QR de Pago</h3>
      </div>
      <div style="padding:20px;display:flex;align-items:flex-start;gap:30px;flex-wrap:wrap;">
        <div>
          <p style="font-size:13px;color:var(--txt-soft);margin-bottom:12px;">QR actual que ven los inscritos al pagar:</p>
          <img id="qr-preview" src="../img/<?php
            $qr_img = 'comprobante1.jpeg';
            $rqr = $conn->query("SELECT valor FROM config_sistema WHERE clave='qr_imagen' LIMIT 1");
            if($rqr && $rwqr = $rqr->fetch_assoc()) $qr_img = $rwqr['valor'];
            echo htmlspecialchars($qr_img);
          ?>" alt="QR actual"
            style="width:180px;height:180px;object-fit:contain;border:2px solid var(--border);border-radius:8px;display:block;">
        </div>
        <div style="flex:1;min-width:220px;">
          <div class="modal-field" style="margin-bottom:14px;">
            <span class="modal-label">Subir nuevo QR (JPG, PNG)</span>
            <input type="file" id="nuevo-qr-file" accept="image/*" style="margin-top:6px;">
          </div>
          <button class="btn-primary" id="btn-guardar-qr">
            <i class="fa-solid fa-floppy-disk"></i> Guardar nuevo QR
          </button>
          <div id="qr-msg" style="margin-top:10px;font-size:13px;"></div>
        </div>
      </div>
    </div>

  </div><!-- /gestion-grid -->

</div>
</main>

<!-- ══ MODAL PAQUETE ══ -->
<div class="modal-overlay" id="modal-paquete-overlay">
  <div class="modal" style="max-width:500px;">
    <div class="modal-header">
      <h3 id="modal-paquete-titulo"><i class="fa-solid fa-box-open"></i> Paquete</h3>
      <button class="modal-close" onclick="cerrarModal('modal-paquete-overlay')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="paq-id">
      <div class="modal-grid" style="grid-template-columns:1fr 1fr;">
        <div class="modal-field full">
          <span class="modal-label">Nombre del paquete</span>
          <input type="text" id="paq-nombre" class="input-filtro" style="width:100%;">
        </div>
        <div class="modal-field">
          <span class="modal-label">Precio (Bs.)</span>
          <input type="number" id="paq-precio" class="input-filtro" min="0" step="0.01" style="width:100%;">
        </div>
        <div class="modal-field">
          <span class="modal-label">Cupos totales</span>
          <input type="number" id="paq-cupos" class="input-filtro" min="0" style="width:100%;">
        </div>
        <div class="modal-field">
          <span class="modal-label">Cupos disponibles</span>
          <input type="number" id="paq-cupos-disp" class="input-filtro" min="0" style="width:100%;">
        </div>
        <div class="modal-field full">
          <span class="modal-label">Promoción asignada</span>
          <select id="paq-descuento" class="select-filtro" style="width:100%;">
            <option value="">Sin promoción</option>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="cerrarModal('modal-paquete-overlay')">Cancelar</button>
      <button class="btn-primary" id="btn-guardar-paquete"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL PROMOCIÓN ══ -->
<div class="modal-overlay" id="modal-promo-overlay">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header">
      <h3 id="modal-promo-titulo"><i class="fa-solid fa-tag"></i> Promoción</h3>
      <button class="modal-close" onclick="cerrarModal('modal-promo-overlay')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="promo-id">
      <div class="modal-grid" style="grid-template-columns:1fr 1fr;">
        <div class="modal-field full">
          <span class="modal-label">Nombre</span>
          <input type="text" id="promo-nombre" class="input-filtro" style="width:100%;">
        </div>
        <div class="modal-field">
          <span class="modal-label">Porcentaje (%)</span>
          <input type="number" id="promo-porcentaje" class="input-filtro" min="0" max="100" step="0.01" style="width:100%;">
        </div>
        <div class="modal-field">
          <span class="modal-label">Estado</span>
          <select id="promo-activo" class="select-filtro" style="width:100%;">
            <option value="1">Activa</option>
            <option value="0">Inactiva</option>
          </select>
        </div>
        <div class="modal-field">
          <span class="modal-label">Fecha inicio</span>
          <input type="datetime-local" id="promo-inicio" class="input-filtro" style="width:100%;">
        </div>
        <div class="modal-field">
          <span class="modal-label">Fecha fin</span>
          <input type="datetime-local" id="promo-fin" class="input-filtro" style="width:100%;">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="cerrarModal('modal-promo-overlay')">Cancelar</button>
      <button class="btn-primary" id="btn-guardar-promo"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL TIPO INSCRITO ══ -->
<div class="modal-overlay" id="modal-tipo-overlay">
  <div class="modal" style="max-width:380px;">
    <div class="modal-header">
      <h3 id="modal-tipo-titulo"><i class="fa-solid fa-id-badge"></i> Tipo de Inscrito</h3>
      <button class="modal-close" onclick="cerrarModal('modal-tipo-overlay')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="tipo-id">
      <div class="modal-field">
        <span class="modal-label">Nombre</span>
        <input type="text" id="tipo-nombre" class="input-filtro" style="width:100%;">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="cerrarModal('modal-tipo-overlay')">Cancelar</button>
      <button class="btn-primary" id="btn-guardar-tipo"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL IGLESIA ══ -->
<div class="modal-overlay" id="modal-iglesia-overlay">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <h3 id="modal-iglesia-titulo"><i class="fa-solid fa-church"></i> Iglesia</h3>
      <button class="modal-close" onclick="cerrarModal('modal-iglesia-overlay')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="igl-id">
      <div class="modal-field" style="margin-bottom:12px;">
        <span class="modal-label">Nombre de la Iglesia</span>
        <input type="text" id="igl-nombre" class="input-filtro" style="width:100%;">
      </div>
      <div class="modal-field">
        <span class="modal-label">Distrito</span>
        <select id="igl-distrito" class="select-filtro" style="width:100%;">
          <option value="">-- Selecciona un distrito --</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="cerrarModal('modal-iglesia-overlay')">Cancelar</button>
      <button class="btn-primary" id="btn-guardar-iglesia"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL EMERGENCIA ══ -->
<div class="modal-overlay" id="modal-emergencia-overlay">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header" style="background:#dc2626;">
      <h3><i class="fa-solid fa-triangle-exclamation"></i> Confirmar acción</h3>
      <button class="modal-close" onclick="cerrarModal('modal-emergencia-overlay')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" style="text-align:center;padding:30px 22px;">
      <i class="fa-solid fa-circle-stop" style="font-size:3em;color:#dc2626;margin-bottom:16px;display:block;"></i>
      <p id="modal-emergencia-txt" style="font-size:15px;color:var(--txt);font-weight:600;margin-bottom:8px;">
        ¿Estás seguro de pausar las inscripciones?
      </p>
      <p style="font-size:13px;color:var(--txt-soft);">
        Nadie podrá registrarse hasta que vuelvas a activarlas.
      </p>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="cerrarModal('modal-emergencia-overlay')">Cancelar</button>
      <button class="btn-primary" id="btn-confirmar-emergencia" style="background:#dc2626;">
        <i class="fa-solid fa-circle-stop"></i> Confirmar
      </button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast-cupos"></div>

<?php $js_pagina = ['editar-cupos.js']; ?>
<?php include_once 'templates/footer.php'; ?>