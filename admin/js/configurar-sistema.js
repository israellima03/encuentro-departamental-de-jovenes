/* ============================================================
   CONFIGURAR-SISTEMA.JS
   ============================================================ */
(function(){
'use strict';

var _eliminarAccion   = null;
var _eliminarId       = null;
var _eliminarCallback = null;

document.addEventListener('DOMContentLoaded', function(){
  initTabs();
  initBotones();
  initCerrarModales();
});

/* ══════════════════════════════════════
   TABS
══════════════════════════════════════ */
function initTabs(){
  document.querySelectorAll('.prog-tab').forEach(function(btn){
    btn.addEventListener('click', function(){
      document.querySelectorAll('.prog-tab').forEach(function(b){ b.classList.remove('active'); });
      document.querySelectorAll('.prog-panel').forEach(function(p){ p.classList.remove('active'); });
      this.classList.add('active');
      var panel = document.getElementById('panel-'+this.dataset.tab);
      if(panel) panel.classList.add('active');
    });
  });
}

/* ══════════════════════════════════════
   GUARDAR CONFIG (evento, footer)
══════════════════════════════════════ */
window.guardarConfig = function(seccion){
  var pares = {};
  if(seccion === 'evento'){
    pares['evento_nombre']    = val('cfg-evento-nombre');
    pares['evento_lema']      = val('cfg-evento-lema');
    pares['evento_versiculo'] = val('cfg-evento-versiculo');
    pares['evento_fecha']     = val('cfg-evento-fecha');
    pares['evento_ciudad']    = val('cfg-evento-ciudad');
  }
  if(seccion === 'footer'){
    pares['footer_titulo']      = val('cfg-footer-titulo');
    pares['footer_descripcion'] = document.getElementById('cfg-footer-descripcion').value.trim();
  }
  var fd = new FormData();
  fd.append('accion','guardar_config');
  Object.keys(pares).forEach(function(k){ fd.append('pares['+k+']', pares[k]); });
  fetch('api_configurar-sistema.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){ data.ok ? toast(data.msg,'ok') : toast(data.msg,'error'); })
  .catch(function(){ toast('Error de conexion','error'); });
};

/* ══════════════════════════════════════
   NOTICIAS
══════════════════════════════════════ */
window.abrirModalNoticia = function(n){
  n = n || {};
  document.getElementById('not-id').value    = n.id    || '';
  document.getElementById('not-texto').value = n.texto || '';
  document.getElementById('not-orden').value = n.orden !== undefined ? n.orden : 0;
  document.getElementById('modal-noticia-titulo').innerHTML =
    '<i class="fa-solid fa-newspaper"></i> '+(n.id?'Editar':'Nueva')+' Noticia';
  abrirModal('modal-noticia');
};

window.recargarNoticias = function(){
  fetch('api_configurar-sistema.php?accion=listar_noticias')
  .then(function(r){ return r.json(); })
  .then(function(data){
    var tbody = document.getElementById('tbody-noticias');
    var lista = data.noticias || [];
    if(!lista.length){
      tbody.innerHTML = '<tr><td colspan="4" class="tabla-vacia">Sin noticias</td></tr>';
      return;
    }
    tbody.innerHTML = lista.map(function(n,i){
      return '<tr id="fila-noticia-'+n.id+'">'+
        '<td>'+(i+1)+'</td>'+
        '<td style="white-space:normal;font-size:13px;">'+esc(n.texto)+'</td>'+
        '<td>'+n.orden+'</td>'+
        '<td style="white-space:nowrap;">'+
          '<button class="btn-accion btn-ver" onclick="abrirModalNoticia('+encDat(n)+')" title="Editar"><i class="fa-solid fa-pen"></i></button> '+
          '<button class="btn-accion" style="color:#dc2626;" onclick="eliminarElemento(\'eliminar_noticia\','+n.id+',\'¿Eliminar esta noticia?\',recargarNoticias)" title="Eliminar"><i class="fa-solid fa-trash"></i></button>'+
        '</td>'+
      '</tr>';
    }).join('');
  });
};

/* ══════════════════════════════════════
   REDES SOCIALES
══════════════════════════════════════ */
window.abrirModalRed = function(r){
  r = r || {};
  document.getElementById('red-id').value     = r.id     || '';
  document.getElementById('red-nombre').value = r.nombre || '';
  document.getElementById('red-icono').value  = r.icono  || '';
  document.getElementById('red-url').value    = r.url    || '';
  document.getElementById('red-orden').value  = r.orden  !== undefined ? r.orden : 0;
  document.getElementById('red-activo').value = r.activo !== undefined ? r.activo : 1;
  var prev = document.getElementById('preview-icono');
  if(prev) prev.className = r.icono || '';
  document.getElementById('modal-red-titulo').innerHTML =
    '<i class="fa-solid fa-share-nodes"></i> '+(r.id?'Editar':'Nueva')+' Red Social';
  abrirModal('modal-red');
};

window.previewIcono = function(valor){
  var el = document.getElementById('preview-icono');
  if(el) el.className = valor.trim();
};

window.recargarRedes = function(){
  fetch('api_configurar-sistema.php?accion=listar_redes')
  .then(function(r){ return r.json(); })
  .then(function(data){
    var tbody = document.getElementById('tbody-redes');
    var lista = data.redes || [];
    if(!lista.length){
      tbody.innerHTML = '<tr><td colspan="5" class="tabla-vacia">Sin redes</td></tr>';
      return;
    }
    tbody.innerHTML = lista.map(function(r,i){
      var badge = r.activo=='1'
        ? '<span class="badge badge-confirmado">Activa</span>'
        : '<span class="badge badge-pendiente">Inactiva</span>';
      return '<tr id="fila-red-'+r.id+'">'+
        '<td>'+(i+1)+'</td>'+
        '<td><i class="'+esc(r.icono)+'" style="margin-right:6px;font-size:1.1em;"></i>'+esc(r.nombre)+'</td>'+
        '<td class="col-url-red" style="font-size:12px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+
          '<a href="'+esc(r.url)+'" target="_blank" style="color:var(--blue);">'+esc(r.url)+'</a></td>'+
        '<td>'+badge+'</td>'+
        '<td style="white-space:nowrap;">'+
          '<button class="btn-accion btn-ver" onclick="abrirModalRed('+encDat(r)+')" title="Editar"><i class="fa-solid fa-pen"></i></button> '+
          '<button class="btn-accion" style="color:#dc2626;" onclick="eliminarElemento(\'eliminar_red\','+r.id+',\'¿Eliminar esta red?\',recargarRedes)" title="Eliminar"><i class="fa-solid fa-trash"></i></button>'+
        '</td>'+
      '</tr>';
    }).join('');
  });
};

/* ══════════════════════════════════════
   UBICACIONES
══════════════════════════════════════ */
window.abrirModalUbicacion = function(u){
  u = u || {};
  document.getElementById('ubic-id').value     = u.id        || '';
  document.getElementById('ubic-nombre').value = u.nombre    || '';
  document.getElementById('ubic-tipo').value   = u.tipo      || 'alojamiento';
  document.getElementById('ubic-link').value   = u.link_maps || '';
  document.getElementById('ubic-embed').value  = u.embed_url || '';
  document.getElementById('ubic-orden').value  = u.orden     !== undefined ? u.orden : 0;
  document.getElementById('ubic-activo').checked = u.activo != '0';
  document.getElementById('modal-ubicacion-titulo').innerHTML =
    '<i class="fa-solid fa-map-location-dot"></i> '+(u.id?'Editar':'Nueva')+' Ubicación';
  abrirModal('modal-ubicacion');
};

window.recargarUbicaciones = function(){
  fetch('api_configurar-sistema.php?accion=listar_ubicaciones')
  .then(function(r){ return r.json(); })
  .then(function(data){
    var tbody = document.getElementById('tbody-ubicaciones');
    var lista = data.ubicaciones || [];
    if(!lista.length){
      tbody.innerHTML = '<tr><td colspan="7" class="tabla-vacia">Sin ubicaciones</td></tr>';
      return;
    }
    tbody.innerHTML = lista.map(function(u,i){
      var tipoCls = u.tipo==='evento' ? 'tipo-conferencia' : 'tipo-actividad';
      var badge = u.activo=='1'
        ? '<span class="badge badge-confirmado">Activa</span>'
        : '<span class="badge badge-pendiente">Inactiva</span>';
      return '<tr id="fila-ubic-'+u.id+'">'+
        '<td>'+(i+1)+'</td>'+
        '<td style="font-weight:600;">'+esc(u.nombre)+'</td>'+
        '<td><span class="badge-tipo '+tipoCls+'">'+esc(u.tipo)+'</span></td>'+
        '<td class="col-url-red" style="font-size:12px;">'+
          '<a href="'+esc(u.link_maps)+'" target="_blank" style="color:var(--blue);">'+
          '<i class="fa-solid fa-map-pin"></i> Ver en Maps</a></td>'+
        '<td>'+u.orden+'</td>'+
        '<td>'+badge+'</td>'+
        '<td style="white-space:nowrap;">'+
          '<button class="btn-accion btn-ver" onclick="abrirModalUbicacion('+encDat(u)+')" title="Editar"><i class="fa-solid fa-pen"></i></button> '+
          '<button class="btn-accion" style="color:#dc2626;" onclick="eliminarElemento(\'eliminar_ubicacion\','+u.id+',\'¿Eliminar esta ubicación?\',recargarUbicaciones)" title="Eliminar"><i class="fa-solid fa-trash"></i></button>'+
        '</td>'+
      '</tr>';
    }).join('');
  });
};

/* ══════════════════════════════════════
   ELIMINAR GENÉRICO CON CONFIRMACIÓN
══════════════════════════════════════ */
window.eliminarElemento = function(accion, id, msg, callback){
  _eliminarAccion   = accion;
  _eliminarId       = id;
  _eliminarCallback = callback;
  var txt = document.getElementById('eliminar-cfg-txt');
  if(txt) txt.textContent = msg || '¿Eliminar este elemento?';
  abrirModal('modal-eliminar-cfg');
};

/* ══════════════════════════════════════
   INIT BOTONES
══════════════════════════════════════ */
function initBotones(){

  /* noticia */
  var btnNot = document.getElementById('btn-guardar-noticia');
  if(btnNot) btnNot.addEventListener('click', function(){
    var fd = new FormData();
    fd.append('accion','guardar_noticia');
    fd.append('id',    val('not-id'));
    fd.append('texto', document.getElementById('not-texto').value.trim());
    fd.append('orden', val('not-orden'));
    enviar(fd,'modal-noticia', recargarNoticias);
  });

  /* red */
  var btnRed = document.getElementById('btn-guardar-red');
  if(btnRed) btnRed.addEventListener('click', function(){
    var fd = new FormData();
    fd.append('accion','guardar_red');
    fd.append('id',     val('red-id'));
    fd.append('nombre', val('red-nombre'));
    fd.append('icono',  val('red-icono'));
    fd.append('url',    val('red-url'));
    fd.append('orden',  val('red-orden'));
    fd.append('activo', document.getElementById('red-activo').value);
    enviar(fd,'modal-red', recargarRedes);
  });

  /* ubicacion */
  var btnUbic = document.getElementById('btn-guardar-ubicacion');
  if(btnUbic) btnUbic.addEventListener('click', function(){
    var fd = new FormData();
    fd.append('accion',    'guardar_ubicacion');
    fd.append('id',        val('ubic-id'));
    fd.append('nombre',    val('ubic-nombre'));
    fd.append('tipo',      val('ubic-tipo'));
    fd.append('link_maps', val('ubic-link'));
    fd.append('embed_url', val('ubic-embed'));
    fd.append('orden',     val('ubic-orden'));
    fd.append('activo',    document.getElementById('ubic-activo').checked ? 1 : 0);
    enviar(fd,'modal-ubicacion', recargarUbicaciones);
  });

  /* confirmar eliminar */
  var btnElim = document.getElementById('btn-confirmar-eliminar-cfg');
  if(btnElim) btnElim.addEventListener('click', function(){
    if(!_eliminarAccion || !_eliminarId) return;
    var fd = new FormData();
    fd.append('accion', _eliminarAccion);
    fd.append('id', _eliminarId);
    btnElim.disabled = true;
    fetch('api_configurar-sistema.php',{method:'POST',body:fd})
    .then(function(r){ return r.json(); })
    .then(function(data){
      btnElim.disabled = false;
      if(data.ok){
        toast(data.msg,'ok');
        cerrarModalSis('modal-eliminar-cfg');
        if(_eliminarCallback) _eliminarCallback();
      } else toast(data.msg,'error');
    }).catch(function(){
      btnElim.disabled = false;
      toast('Error de conexion','error');
    });
  });
}

function enviar(fd, modalId, callback){
  fetch('api_configurar-sistema.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast(data.msg,'ok'); cerrarModalSis(modalId); if(callback) callback(); }
    else toast(data.msg,'error');
  }).catch(function(){ toast('Error de conexion','error'); });
}

/* ══════════════════════════════════════
   MODALES
══════════════════════════════════════ */
function initCerrarModales(){
  document.querySelectorAll('.modal-overlay').forEach(function(el){
    el.addEventListener('click', function(e){ if(e.target===this) this.style.display='none'; });
  });
  document.addEventListener('keydown', function(e){
    if(e.key==='Escape') document.querySelectorAll('.modal-overlay').forEach(function(el){ el.style.display='none'; });
  });
}
function abrirModal(id){ var el=document.getElementById(id); if(el) el.style.display='grid'; }
window.cerrarModalSis = function(id){ var el=document.getElementById(id); if(el) el.style.display='none'; };

/* ══════════════════════════════════════
   HELPERS
══════════════════════════════════════ */
function val(id){ var el=document.getElementById(id); return el?el.value.trim():''; }
function encDat(obj){ return '\''+encodeURIComponent(JSON.stringify(obj))+'\''; }
function toast(msg,tipo){
  var el=document.getElementById('toast-sistema'); if(!el) return;
  el.textContent=msg; el.className='toast show'+(tipo?' toast-'+tipo:'');
  clearTimeout(toast._t); toast._t=setTimeout(function(){ el.classList.remove('show'); },3500);
}
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

})();