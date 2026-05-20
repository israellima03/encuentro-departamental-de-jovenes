/* ============================================================
   COMISIONES.JS — adaptado a tablas: comisiones, comision_encargados
   ============================================================ */
(function(){
'use strict';

var todasComisiones = [];

function toast(msg, tipo){
  var el = document.getElementById('toast-comisiones'); if(!el) return;
  el.textContent = msg;
  el.className = 'toast show' + (tipo ? ' toast-'+tipo : '');
  clearTimeout(toast._t);
  toast._t = setTimeout(function(){ el.classList.remove('show'); }, 3500);
}

function esc(s){
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ══════════════════════════════════════
   CARGAR Y RENDERIZAR COMISIONES
══════════════════════════════════════ */
function cargar(){
  var q      = document.getElementById('com-buscar').value.trim();
  var estado = document.getElementById('com-estado').value;

  fetch('api_comisiones.php?accion=listar&q='+encodeURIComponent(q)+'&estado='+encodeURIComponent(estado))
  .then(function(r){ return r.json(); })
  .then(function(data){
    todasComisiones = data.comisiones || [];
    renderTabla(todasComisiones);
  })
  .catch(function(){ toast('Error de conexión','error'); });
}

function renderTabla(lista){
  var tbody = document.getElementById('tbody-comisiones');
  var lbl   = document.getElementById('com-total-lbl');
  if(lbl) lbl.textContent = lista.length + ' comisiones';

  if(!lista.length){
    tbody.innerHTML = '<tr><td colspan="7" class="tabla-vacia">'+
      '<i class="fa-solid fa-inbox com-vacia-icono"></i>No hay comisiones</td></tr>';
    return;
  }

  tbody.innerHTML = lista.map(function(c, i){
    var badgeEstado = c.activo == 1
      ? '<span class="badge badge-confirmado">Activa</span>'
      : '<span class="badge badge-pendiente">Inactiva</span>';

    var iconoClass = c.icono || 'fa-solid fa-users';

    return '<tr>'+
      '<td class="com-td-num">'+(i+1)+'</td>'+
      '<td class="com-td-icono">'+
        '<div class="com-icono-box">'+
          '<i class="'+esc(iconoClass)+'"></i>'+
        '</div>'+
      '</td>'+
      '<td><strong>'+esc(c.nombre)+'</strong></td>'+
      '<td class="com-td-enc">'+
        '<button class="btn-accion btn-ver com-btn-enc" '+
          'onclick="abrirEncargados('+c.id+',\''+esc(c.nombre)+'\')">'+
          '<i class="fa-solid fa-users"></i> '+c.total_encargados+
        '</button>'+
      '</td>'+
      '<td class="com-td-estado">'+badgeEstado+'</td>'+
      '<td class="com-td-acc">'+
        '<button class="btn-accion btn-ver" '+
          'onclick="editarComision(\''+encodeURIComponent(JSON.stringify(c))+'\')" title="Editar">'+
          '<i class="fa-solid fa-pen"></i>'+
        '</button> '+
        '<button class="btn-accion btn-eliminar" '+
          'onclick="eliminarComision('+c.id+',\''+esc(c.nombre)+'\')" title="Eliminar">'+
          '<i class="fa-solid fa-trash"></i>'+
        '</button>'+
      '</td>'+
    '</tr>';
  }).join('');
}

/* ══════════════════════════════════════
   MODAL COMISIÓN — CRUD
══════════════════════════════════════ */
function abrirModalCom(com){
  document.getElementById('com-modal-titulo').textContent = com ? 'Editar Comisión' : 'Nueva Comisión';
  document.getElementById('com-id').value          = com ? com.id : '';
  document.getElementById('com-nombre').value      = com ? com.nombre : '';
  document.getElementById('com-icono').value       = com ? (com.icono||'fa-solid fa-users') : 'fa-solid fa-users';
  document.getElementById('com-orden').value       = com ? com.orden : 0;
  document.getElementById('com-activo').value      = com ? com.activo : 1;
  document.getElementById('com-nombre-err').style.display = 'none';
  actualizarIconoPreview();
  document.getElementById('modal-com-overlay').style.display = 'grid';
}

function actualizarIconoPreview(){
  var icono = document.getElementById('com-icono').value.trim() || 'fa-solid fa-users';
  var prev  = document.getElementById('com-icono-preview');
  if(prev) prev.innerHTML = '<i class="'+esc(icono)+'"></i>';
}

window.editarComision = function(json){
  abrirModalCom(JSON.parse(decodeURIComponent(json)));
};

window.cerrarModalCom = function(){
  document.getElementById('modal-com-overlay').style.display = 'none';
};

window.eliminarComision = function(id, nombre){
  if(!confirm('¿Eliminar la comisión "'+nombre+'"?\n\nTambién se eliminarán todos sus encargados.')) return;
  var fd = new FormData();
  fd.append('accion','eliminar'); fd.append('id',id);
  fetch('api_comisiones.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast(data.msg,'ok'); cargar(); }
    else toast(data.msg,'error');
  });
};

function guardarComision(){
  var nombre = document.getElementById('com-nombre').value.trim();
  var errEl  = document.getElementById('com-nombre-err');

  if(!nombre){
    errEl.textContent = 'El nombre es obligatorio';
    errEl.style.display = 'block';
    return;
  }
  errEl.style.display = 'none';

  var fd = new FormData();
  fd.append('accion',  'guardar');
  fd.append('id',      document.getElementById('com-id').value);
  fd.append('nombre',  nombre);
  fd.append('icono',   document.getElementById('com-icono').value.trim() || 'fa-solid fa-users');
  fd.append('orden',   document.getElementById('com-orden').value);
  fd.append('activo',  document.getElementById('com-activo').value);

  var btn = document.getElementById('btn-com-guardar');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

  fetch('api_comisiones.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar';
    if(data.ok){ toast(data.msg,'ok'); cerrarModalCom(); cargar(); }
    else toast(data.msg,'error');
  });
}

/* ══════════════════════════════════════
   MODAL ENCARGADOS
══════════════════════════════════════ */
window.abrirEncargados = function(comisionId, nombre){
  document.getElementById('enc-comision-id').value = comisionId;
  document.getElementById('enc-comision-nombre').textContent = nombre;
  limpiarFormEnc();
  cargarEncargados(comisionId);
  document.getElementById('modal-enc-overlay').style.display = 'grid';
};

window.cerrarModalEnc = function(){
  document.getElementById('modal-enc-overlay').style.display = 'none';
};

function cargarEncargados(comisionId){
  var lista = document.getElementById('enc-lista');
  lista.innerHTML = '<p class="enc-cargando"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</p>';

  fetch('api_comisiones.php?accion=listar_encargados&comision_id='+comisionId)
  .then(function(r){ return r.json(); })
  .then(function(data){
    var enc = data.encargados || [];
    if(!enc.length){
      lista.innerHTML = '<p class="enc-vacio">'+
        '<i class="fa-solid fa-user-slash enc-vacio-icono"></i>Sin encargados aún</p>';
      return;
    }
    lista.innerHTML = enc.map(function(e){
      return '<div class="enc-item">'+
        '<div class="enc-avatar"><i class="fa-solid fa-user"></i></div>'+
        '<div class="enc-info">'+
          '<span class="enc-nombre">'+esc(e.nombre)+'</span>'+
          '<span class="enc-celular">'+
            '<i class="fa-solid fa-phone"></i> '+esc(e.celular||'—')+
          '</span>'+
        '</div>'+
        '<button class="btn-accion btn-eliminar" '+
          'onclick="eliminarEncargado('+e.id+','+comisionId+')" title="Eliminar">'+
          '<i class="fa-solid fa-trash"></i>'+
        '</button>'+
      '</div>';
    }).join('');
  })
  .catch(function(){
    document.getElementById('enc-lista').innerHTML =
      '<p class="enc-error"><i class="fa-solid fa-circle-xmark"></i> Error al cargar</p>';
  });
}

window.eliminarEncargado = function(id, comisionId){
  if(!confirm('¿Eliminar este encargado?')) return;
  var fd = new FormData();
  fd.append('accion','eliminar_encargado'); fd.append('id',id);
  fetch('api_comisiones.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast(data.msg,'ok'); cargarEncargados(comisionId); cargar(); }
    else toast(data.msg,'error');
  });
};

function limpiarFormEnc(){
  document.getElementById('enc-nombre').value  = '';
  document.getElementById('enc-celular').value = '';
  document.getElementById('enc-aviso').style.display = 'none';
}

function agregarEncargado(){
  var comisionId = document.getElementById('enc-comision-id').value;
  var nombre     = document.getElementById('enc-nombre').value.trim();
  var celular    = document.getElementById('enc-celular').value.trim();
  var aviso      = document.getElementById('enc-aviso');

  if(!nombre){
    aviso.style.display = 'block';
    aviso.textContent   = 'El nombre es obligatorio';
    return;
  }
  aviso.style.display = 'none';

  var fd = new FormData();
  fd.append('accion',      'agregar_encargado');
  fd.append('comision_id', comisionId);
  fd.append('nombre',      nombre);
  fd.append('celular',     celular);

  var btn = document.getElementById('btn-enc-agregar');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

  fetch('api_comisiones.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-plus"></i> Agregar Encargado';
    if(data.ok){
      toast(data.msg,'ok');
      limpiarFormEnc();
      cargarEncargados(comisionId);
      cargar();
    } else {
      aviso.style.display = 'block';
      aviso.textContent   = data.msg;
    }
  });
}

/* ══ INIT ══ */
document.addEventListener('DOMContentLoaded', function(){

  cargar();

  /* filtros */
  var timer;
  var buscar = document.getElementById('com-buscar');
  if(buscar) buscar.addEventListener('input', function(){
    clearTimeout(timer);
    timer = setTimeout(cargar, 350);
  });
  var estado = document.getElementById('com-estado');
  if(estado) estado.addEventListener('change', cargar);

  /* nueva comisión */
  var btnNueva = document.getElementById('btn-nueva-comision');
  if(btnNueva) btnNueva.addEventListener('click', function(){ abrirModalCom(null); });

  /* guardar comisión */
  var btnGuardar = document.getElementById('btn-com-guardar');
  if(btnGuardar) btnGuardar.addEventListener('click', guardarComision);

  /* preview ícono */
  var inpIcono = document.getElementById('com-icono');
  if(inpIcono) inpIcono.addEventListener('input', actualizarIconoPreview);

  /* agregar encargado */
  var btnEnc = document.getElementById('btn-enc-agregar');
  if(btnEnc) btnEnc.addEventListener('click', agregarEncargado);

  /* Escape cierra modales */
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
      ['modal-com-overlay','modal-enc-overlay'].forEach(function(id){
        var el = document.getElementById(id);
        if(el) el.style.display = 'none';
      });
    }
  });

  /* click fuera cierra */
  ['modal-com-overlay','modal-enc-overlay'].forEach(function(id){
    var el = document.getElementById(id);
    if(el) el.addEventListener('click', function(e){
      if(e.target === this) this.style.display = 'none';
    });
  });

});

})();