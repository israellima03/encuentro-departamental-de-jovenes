<?php
require_once('funciones/sesiones.php');
usuario_autentificado();
verificar_acceso(['Administrador','tesorera','Lider departamental']);

header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

require_once('../includes/funciones/bd_conexion.php');

include_once 'templates/header.php';
include_once 'templates/navegacion.php';
include_once 'templates/barra.php';
include_once 'funciones/funciones.php';
?>

<!-- MODAL OPCIONES PDF -->
<div class="modal-overlay" id="modal-pdf-overlay">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-file-pdf"></i> Exportar PDF</h3>
      <button class="modal-close" onclick="document.getElementById('modal-pdf-overlay').classList.remove('open')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body">
      <p style="color:var(--txt-soft);font-size:13px;margin-bottom:16px;">Selecciona qué quieres descargar:</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <button class="btn-primary" style="justify-content:flex-start;gap:12px;" onclick="generarPDF('todos')">
          <i class="fa-solid fa-list"></i> Todos los pedidos completos
        </button>
        <button class="btn-primary" style="justify-content:flex-start;gap:12px;background:var(--blue);" onclick="generarPDF('tallas')">
          <i class="fa-solid fa-ruler"></i> Pedidos agrupados por talla
        </button>
        <button class="btn-primary" style="justify-content:flex-start;gap:12px;background:var(--purple);" onclick="generarPDF('genero')">
          <i class="fa-solid fa-venus-mars"></i> Pedidos separados por género
        </button>
        <button class="btn-primary" style="justify-content:flex-start;gap:12px;background:var(--green);" onclick="generarPDF('dinero')">
          <i class="fa-solid fa-coins"></i> Resumen financiero (total recaudado)
        </button>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="document.getElementById('modal-pdf-overlay').classList.remove('open')">Cerrar</button>
    </div>
  </div>
</div>
<!-- ── MODAL EDITAR PEDIDO ── -->
<div class="modal-overlay" id="modal-editar-ped-overlay">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-pen"></i> Editar Pedido</h3>
      <button class="modal-close" onclick="document.getElementById('modal-editar-ped-overlay').classList.remove('open')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="edit-ped-id">
      <div class="modal-field" style="margin-bottom:14px;">
        <span class="modal-label">Cantidad</span>
        <input type="number" id="edit-ped-cantidad" class="input-filtro" min="1" style="width:100%;">
      </div>
      <div class="modal-field">
        <span class="modal-label">Talla</span>
        <select id="edit-ped-talla" class="select-filtro" style="width:100%;">
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="document.getElementById('modal-editar-ped-overlay').classList.remove('open')">Cancelar</button>
      <button class="btn-primary" id="btn-guardar-editar-ped">
        <i class="fa-solid fa-floppy-disk"></i> Guardar
      </button>
    </div>
  </div>
</div>
<main class="content" id="main-content">
<div class="page active" id="page-productos">

  <div class="page-header">
    <div>
      <h1 class="page-title">Gestión de <span>Productos</span></h1>
      <p class="page-sub">Edita cupos, imágenes, tallas y consulta los pedidos de inscritos.</p>
    </div>
   <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <button class="btn-primary" style="background:var(--green);"
              onclick="document.getElementById('modal-add-ped-overlay').classList.add('open')">
        <i class="fa-solid fa-cart-plus"></i> Añadir Producto a Inscrito
      </button>
      <button class="btn-primary" 
              onclick="document.getElementById('modal-add-prod-overlay').classList.add('open')">
        <i class="fa-solid fa-plus"></i> Nuevo Producto
      </button>
      <button class="btn-primary" id="btn-exportar-pdf-prod" style="background:var(--accent);">
        <i class="fa-solid fa-file-pdf"></i> Exportar PDF
      </button>
    </div>
  </div>

  <!-- ── TARJETAS DE PRODUCTOS ── -->
  <div class="prod-grid" id="prod-grid">
    <div class="prod-card-loading"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</div>
  </div>

  <!-- ── TABLA PEDIDOS ── -->
  <div class="card" style="margin-top:28px;">
    <div class="card-header">
      <h3><i class="fa-solid fa-list-check"></i> Pedidos de Productos por Inscrito</h3>
      <span id="pedidos-total-lbl" style="font-size:12px;color:var(--txt-xsoft);"></span>
    </div>

    <!-- filtros -->
    <!-- filtros -->
    <div class="qr-filtros" style="border-bottom:1px solid var(--border);border-radius:0;">
      <div class="qr-filtro-grupo">
        <i class="fa-solid fa-search"></i>
        <input type="text" id="ped-buscar" placeholder="Buscar por nombre o carnet...">
      </div>
      <select id="ped-producto" class="select-filtro">
        <option value="">Todos los productos</option>
      </select>
      <select id="ped-talla" class="select-filtro">
        <option value="">Todas las tallas</option>
      </select>
      <select id="ped-genero" class="select-filtro">
        <option value="">Todos los géneros</option>
        <option value="hombre">Hombre</option>
        <option value="mujer">Mujer</option>
        <option value="unisex">Unisex</option>
      </select>
    </div>

    <div class="tabla-wrap">
      <table class="tabla-inscritos" id="tabla-pedidos">
        <thead>
          <tr>
            <th>#</th>
            <th>Inscrito</th>
            <th>Carnet</th>
            <th>Estado pago</th>
            <th>Producto</th>
            <th>Talla</th>
            <th>Género</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody id="tbody-pedidos">
          <tr><td colspan="10" class="tabla-loading"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- resumen tallas -->
    <div id="resumen-tallas-wrap" style="padding:18px 20px;border-top:1px solid var(--border);display:none;">
      <h4 style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--txt-soft);margin-bottom:12px;">
        <i class="fa-solid fa-ruler" style="color:var(--accent);margin-right:6px;"></i> Resumen por Talla
      </h4>
      <div id="resumen-tallas"></div>
    </div>
  </div>

</div>
</main>

<!-- ── MODAL EDITAR PRODUCTO ── -->
<div class="modal-overlay" id="modal-prod-overlay">
  <div class="modal" style="max-width:640px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-pen-to-square"></i> Editar Producto</h3>
      <button class="modal-close" onclick="document.getElementById('modal-prod-overlay').classList.remove('open')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body" id="modal-prod-body"></div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="document.getElementById('modal-prod-overlay').classList.remove('open')">Cancelar</button>
      <button class="btn-primary" id="btn-guardar-prod"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>

</div>

<!-- ── MODAL TALLAS ── -->
<div class="modal-overlay" id="modal-tallas-overlay">
  <div class="modal" style="max-width:700px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-ruler"></i> Tallas del Producto</h3>
      <button class="modal-close" onclick="document.getElementById('modal-tallas-overlay').classList.remove('open')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body" id="modal-tallas-body"></div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="document.getElementById('modal-tallas-overlay').classList.remove('open')">Cerrar</button>
    </div>
  </div>
</div>

<!-- ── MODAL AÑADIR PRODUCTO A INSCRITO ── -->
<div class="modal-overlay" id="modal-add-ped-overlay">
  <div class="modal" style="max-width:520px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-cart-plus"></i> Añadir Producto a Inscrito</h3>
      <button class="modal-close" onclick="document.getElementById('modal-add-ped-overlay').classList.remove('open')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body">
      <div class="modal-grid" style="grid-template-columns:1fr;">
        <div class="modal-field">
          <span class="modal-label">Buscar Inscrito (nombre o carnet)</span>
          <input type="text" id="add-ped-buscar" class="input-filtro" placeholder="Escribe para buscar...">
          <div id="add-ped-resultados" style="margin-top:6px;"></div>
        </div>
        <div id="add-ped-form" style="display:none;">
          <div class="modal-field" style="margin-top:12px;">
            <span class="modal-label">Inscrito seleccionado</span>
            <span class="modal-value" id="add-ped-inscrito-nombre">—</span>
            <input type="hidden" id="add-ped-inscrito-id">
          </div>
          <div class="modal-field" style="margin-top:12px;">
            <span class="modal-label">Producto</span>
            <select id="add-ped-producto" class="select-filtro" style="width:100%;">
              <option value="">-- Selecciona --</option>
            </select>
          </div>
          <div class="modal-field" style="margin-top:12px;" id="add-ped-genero-wrap">
            <span class="modal-label">Género</span>
            <select id="add-ped-genero" class="select-filtro" style="width:100%;">
              <option value="hombre">Hombre</option>
              <option value="mujer">Mujer</option>
            </select>
          </div>
          <div class="modal-field" style="margin-top:12px;">
            <span class="modal-label">Talla</span>
            <select id="add-ped-talla" class="select-filtro" style="width:100%;">
              <option value="">-- Selecciona producto primero --</option>
            </select>
          </div>
          <div class="modal-field" style="margin-top:12px;">
            <span class="modal-label">Cantidad</span>
            <input type="number" id="add-ped-cantidad" class="input-filtro" value="1" min="1" style="width:100px;">
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="document.getElementById('modal-add-ped-overlay').classList.remove('open')">Cancelar</button>
      <button class="btn-success" id="btn-guardar-pedido" style="display:none;">
        <i class="fa-solid fa-check"></i> Guardar Pedido
      </button>
    </div>
  </div>
</div>

<!-- ── MODAL AÑADIR NUEVO PRODUCTO ── -->
<div class="modal-overlay" id="modal-add-prod-overlay">
  <div class="modal" style="max-width:500px;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-plus"></i> Nuevo Producto</h3>
      <button class="modal-close" onclick="document.getElementById('modal-add-prod-overlay').classList.remove('open')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body">
      <div class="modal-grid" style="grid-template-columns:1fr;">
        <div class="modal-field">
          <span class="modal-label">Nombre</span>
          <input type="text" id="new-prod-nombre" class="input-filtro" style="width:100%;">
        </div>
        <div class="modal-field" style="margin-top:10px;">
          <span class="modal-label">Precio (Bs.)</span>
          <input type="number" id="new-prod-precio" class="input-filtro" min="0" step="0.01" style="width:100%;">
        </div>
        <div class="modal-field" style="margin-top:10px;">
          <span class="modal-label">Tipo (polera / gorra / otro)</span>
          <input type="text" id="new-prod-tipo" class="input-filtro" style="width:100%;">
        </div>
        <div class="modal-field" style="margin-top:10px;">
          <span class="modal-label">Cupos</span>
          <input type="number" id="new-prod-cupos" class="input-filtro" min="0" style="width:100%;">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="document.getElementById('modal-add-prod-overlay').classList.remove('open')">Cancelar</button>
      <button class="btn-success" id="btn-crear-producto"><i class="fa-solid fa-plus"></i> Crear</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast-prod"></div>

<script src="js/productos.js"></script>
<?php include_once 'templates/footer.php'; ?>