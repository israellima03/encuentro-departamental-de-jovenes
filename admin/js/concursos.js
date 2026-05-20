/* ============================================================
   CONCURSOS.JS
   ============================================================ */
(function(){
'use strict';

var todosEquipos      = [];
var todosConcursantes = [];
var filtradosBib      = [];
var paginaBib         = 1;
var porPaginaBib      = 15;
var faseActual        = null;
var categoriaActual   = null;

function toast(msg, tipo){
  var el = document.getElementById('toast-concursos'); if(!el) return;
  el.textContent = msg;
  el.className = 'toast show' + (tipo ? ' toast-'+tipo : '');
  clearTimeout(toast._t);
  toast._t = setTimeout(function(){ el.classList.remove('show'); }, 3500);
}

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function formatFecha(f){
  if(!f) return '—';
  return f.substring(0,10).split('-').reverse().join('/');
}

/* ══════════════════════════════════════
   DEPORTIVO
══════════════════════════════════════ */
function initDeportivo(){
  cargarEquipos();
  var inp = document.getElementById('dep-buscar');
  if(inp) inp.addEventListener('input', function(){
    var q = this.value.toLowerCase();
    var filtrados = todosEquipos.filter(function(e){
      return (e.nombre_equipo+e.nombre+e.apellido+e.carnet).toLowerCase().includes(q);
    });
    renderEquipos(filtrados);
  });

  if(PUEDE_GESTIONAR){
    document.getElementById('btn-dep-guardar').addEventListener('click', guardarEquipo);
  }
}

function cargarEquipos(){
  fetch('api_concursos.php?accion=listar_equipos&q=')
  .then(function(r){ return r.json(); })
  .then(function(data){
    todosEquipos = data.equipos || [];
    renderEquipos(todosEquipos);
  });
}

function renderEquipos(lista){
  var tbody = document.getElementById('tbody-deportivo');
  var lbl   = document.getElementById('dep-total-lbl');
  if(lbl) lbl.textContent = lista.length + ' equipos';
  if(!lista.length){
    tbody.innerHTML = '<tr><td colspan="9" class="tabla-vacia">No hay equipos registrados</td></tr>';
    return;
  }
  tbody.innerHTML = lista.map(function(e, i){
    var acciones = '';
    if(PUEDE_GESTIONAR){
      acciones = '<button class="btn-accion btn-ver" onclick="editarEquipo('+e.id+',\''+esc(e.nombre_equipo)+'\')" title="Editar"><i class="fa-solid fa-pen"></i></button> '+
                 '<button class="btn-accion btn-eliminar" onclick="eliminarEquipo('+e.id+',\''+esc(e.nombre_equipo)+'\')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>';
    }
    return '<tr>'+
      '<td>'+(i+1)+'</td>'+
      '<td><strong>'+esc(e.nombre_equipo)+'</strong></td>'+
      '<td>'+esc(e.nombre)+' '+esc(e.apellido)+'</td>'+
      '<td>'+esc(e.carnet)+'</td>'+
      '<td>'+esc(e.celular||'—')+'</td>'+
      '<td>'+esc(e.iglesia||'—')+'</td>'+
      '<td>'+esc(e.distrito||'—')+'</td>'+
      '<td>'+formatFecha(e.fecha_registro)+'</td>'+
      (PUEDE_GESTIONAR ? '<td>'+acciones+'</td>' : '')+
    '</tr>';
  }).join('');
}

window.editarEquipo = function(id, nombre){
  document.getElementById('dep-edit-id').value    = id;
  document.getElementById('dep-edit-nombre').value = nombre;
  document.getElementById('dep-edit-msg').style.display = 'none';
  document.getElementById('modal-dep-overlay').style.display = 'grid';
};

window.cerrarModalDep = function(){
  document.getElementById('modal-dep-overlay').style.display = 'none';
};

function guardarEquipo(){
  var id     = document.getElementById('dep-edit-id').value;
  var nombre = document.getElementById('dep-edit-nombre').value.trim();
  var msg    = document.getElementById('dep-edit-msg');
  if(nombre.length < 3){ msg.style.display='block'; msg.style.color='#da002b'; msg.textContent='Mínimo 3 caracteres'; return; }
  var fd = new FormData();
  fd.append('accion','editar_equipo'); fd.append('id',id); fd.append('nombre',nombre);
  fetch('api_concursos.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast(data.msg,'ok'); cerrarModalDep(); cargarEquipos(); }
    else { msg.style.display='block'; msg.style.color='#da002b'; msg.textContent=data.msg; }
  });
}

window.eliminarEquipo = function(id, nombre){
  if(!confirm('¿Eliminar el equipo "'+nombre+'"? Esta acción no se puede deshacer.')) return;
  var fd = new FormData();
  fd.append('accion','eliminar_equipo'); fd.append('id',id);
  fetch('api_concursos.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast(data.msg,'ok'); cargarEquipos(); }
    else toast(data.msg,'error');
  });
};

/* ══════════════════════════════════════
   BÍBLICO
══════════════════════════════════════ */
function initBiblico(){
  cargarCategorias();
  cargarDistritos();
  cargarConcursantes();

  /* filtros */
  ['bib-buscar','bib-categoria','bib-distrito'].forEach(function(id){
    var el = document.getElementById(id);
    if(el){ el.addEventListener('input', aplicarFiltrosBib); el.addEventListener('change', aplicarFiltrosBib); }
  });

  /* cambio categoría → cargar fases */
  var selCat = document.getElementById('punt-categoria');
  if(selCat) selCat.addEventListener('change', function(){
    categoriaActual = this.value;
    cargarFases(this.value);
    document.getElementById('punt-contenido').innerHTML = '<div style="text-align:center;color:var(--txt-xsoft);padding:40px;">Selecciona una fase</div>';
  });

  var selFase = document.getElementById('punt-fase');
  if(selFase) selFase.addEventListener('change', function(){
    faseActual = this.value;
    if(faseActual) cargarPuntuaciones(faseActual);
  });

  if(PUEDE_GESTIONAR){
    var btnFase = document.getElementById('btn-nueva-fase');
    if(btnFase) btnFase.addEventListener('click', function(){
      var cat = document.getElementById('punt-categoria').value;
      if(!cat){ toast('Selecciona una categoría primero','warn'); return; }
      document.getElementById('fase-categoria-id').value = cat;
      document.getElementById('fase-nombre').value = '';
      document.getElementById('modal-fase-overlay').style.display = 'grid';
    });
    document.getElementById('btn-fase-guardar').addEventListener('click', crearFase);
  }

  if(PUEDE_INSCRIBIR){
    var btnIns = document.getElementById('btn-inscribir-biblico');
    if(btnIns) btnIns.addEventListener('click', function(){
      abrirModalBib();
    });
    document.getElementById('btn-bib-guardar').addEventListener('click', inscribirConcursante);
    document.getElementById('btn-bib-edit-guardar').addEventListener('click', editarConcursante);

    /* buscador inscrito en modal */
    var inpBus = document.getElementById('bib-ins-buscar');
    var timer;
    if(inpBus) inpBus.addEventListener('input', function(){
      clearTimeout(timer);
      var q = this.value.trim();
      document.getElementById('bib-ins-id').value = '';
      document.getElementById('bib-ins-sel').style.display = 'none';
      if(q.length < 2){ document.getElementById('bib-ins-resultados').innerHTML = ''; return; }
      timer = setTimeout(function(){
        fetch('api_concursos.php?accion=buscar_inscrito_bib&q='+encodeURIComponent(q))
        .then(function(r){ return r.json(); })
        .then(function(data){
          var res = document.getElementById('bib-ins-resultados');
          if(!data.inscritos || !data.inscritos.length){
            res.innerHTML = '<p style="font-size:12px;color:#9ca3af;margin-top:4px;">No encontrado o sin pago confirmado</p>';
            return;
          }
          res.innerHTML = data.inscritos.map(function(ins){
            return '<div onclick="selIns('+ins.id+',\''+esc(ins.nombre+' '+ins.apellido)+'\')" '+
              'style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;margin-bottom:4px;cursor:pointer;font-size:13px;" '+
              'onmouseover="this.style.background=\'var(--bg)\'" onmouseout="this.style.background=\'\'">'+
              '<i class="fa-solid fa-user" style="color:var(--accent);margin-right:6px;"></i>'+
              esc(ins.nombre)+' '+esc(ins.apellido)+' — <small style="color:#9ca3af;">'+esc(ins.carnet)+'</small>'+
              '<small style="display:block;color:#9ca3af;">'+esc(ins.iglesia||'')+(ins.distrito?' · '+esc(ins.distrito):'')+'</small>'+
            '</div>';
          }).join('');
        });
      }, 350);
    });

    /* cambio categoría en modal → mostrar campo equipo si es grupal */
    var selCatIns = document.getElementById('bib-ins-categoria');
    if(selCatIns) selCatIns.addEventListener('change', function(){
      var opt = this.options[this.selectedIndex];
      var tipo = opt.dataset.tipo || '';
      document.getElementById('bib-ins-equipo-wrap').style.display = tipo === 'grupal' ? 'block' : 'none';
    });
  }
  /* cargar categorías admin si tiene permiso */
  if(PUEDE_GESTIONAR) initCategorias();
}

window.selIns = function(id, nombre){
  document.getElementById('bib-ins-id').value = id;
  document.getElementById('bib-ins-buscar').value = nombre;
  document.getElementById('bib-ins-nombre').textContent = nombre;
  document.getElementById('bib-ins-sel').style.display = 'block';
  document.getElementById('bib-ins-resultados').innerHTML = '';
};

function cargarCategorias(){
  fetch('api_concursos.php?accion=listar_categorias')
  .then(function(r){ return r.json(); })
  .then(function(data){
    var cats = data.categorias || [];

    /* filtro tabla */
    var sel = document.getElementById('bib-categoria');
    if(sel) cats.forEach(function(c){
      sel.innerHTML += '<option value="'+c.id+'">'+esc(c.nombre)+'</option>';
    });

    /* selector puntuaciones */
    var selP = document.getElementById('punt-categoria');
    if(selP) cats.forEach(function(c){
      selP.innerHTML += '<option value="'+c.id+'">'+esc(c.nombre)+'</option>';
    });

    /* modal inscribir */
    var selI = document.getElementById('bib-ins-categoria');
    if(selI) cats.forEach(function(c){
      selI.innerHTML += '<option value="'+c.id+'" data-tipo="'+c.tipo+'">'+esc(c.nombre)+' ('+c.tipo+')</option>';
    });

    /* modal editar */
    var selE = document.getElementById('bib-edit-categoria');
    if(selE) cats.forEach(function(c){
      selE.innerHTML += '<option value="'+c.id+'">'+esc(c.nombre)+'</option>';
    });
  });
}

function cargarDistritos(){
  fetch('api_concursos.php?accion=listar_distritos')
  .then(function(r){ return r.json(); })
  .then(function(data){
    var sel = document.getElementById('bib-distrito');
    if(sel)(data.distritos||[]).forEach(function(d){
      sel.innerHTML += '<option value="'+d.id+'">'+esc(d.nombre)+'</option>';
    });
  });
}

function cargarConcursantes(){
  fetch('api_concursos.php?accion=listar_concursantes')
  .then(function(r){ return r.json(); })
  .then(function(data){
    todosConcursantes = data.concursantes || [];
    filtradosBib = todosConcursantes.slice();
    renderBiblico();
  });
}

function aplicarFiltrosBib(){
  var q    = (document.getElementById('bib-buscar').value || '').toLowerCase();
  var cat  = document.getElementById('bib-categoria').value;
  var dist = document.getElementById('bib-distrito').value;
  filtradosBib = todosConcursantes.filter(function(c){
    return (!q || (c.nombre+' '+c.apellido+' '+c.carnet).toLowerCase().includes(q))
      && (!cat  || String(c.categoria_id) === cat  || c.categoria === cat)
      && (!dist || String(c.distrito_id)  === dist);
  });
  paginaBib = 1;
  renderBiblico();
}

function renderBiblico(){
  var tbody = document.getElementById('tbody-biblico');
  var lbl   = document.getElementById('bib-total-lbl');
  if(lbl) lbl.textContent = filtradosBib.length + ' concursantes';
  if(!filtradosBib.length){
    tbody.innerHTML = '<tr><td colspan="11" class="tabla-vacia">No hay concursantes inscritos</td></tr>';
    renderPagBib(); return;
  }
  var inicio = (paginaBib-1)*porPaginaBib;
  var pagina = filtradosBib.slice(inicio, inicio+porPaginaBib);
  tbody.innerHTML = pagina.map(function(c, i){
    var acciones = '';
    if(PUEDE_INSCRIBIR){
      acciones = '<button class="btn-accion btn-ver" onclick="abrirEditBib(\''+encodeURIComponent(JSON.stringify(c))+'\')" title="Editar"><i class="fa-solid fa-pen"></i></button> '+
                 '<button class="btn-accion btn-eliminar" onclick="eliminarConcursante('+c.id+',\''+esc(c.nombre)+'\')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>';
    }
    return '<tr>'+
      '<td>'+(inicio+i+1)+'</td>'+
      '<td><strong>'+esc(c.nombre)+' '+esc(c.apellido)+'</strong></td>'+
      '<td>'+esc(c.carnet)+'</td>'+
      '<td>'+esc(c.celular||'—')+'</td>'+
      '<td>'+esc(c.iglesia||'—')+'</td>'+
      '<td>'+esc(c.distrito||'—')+'</td>'+
      '<td><span class="badge badge-confirmado">'+esc(c.categoria)+'</span></td>'+
      '<td><span class="badge '+(c.cat_tipo==='grupal'?'badge-pendiente':'badge-confirmado')+'">'+esc(c.cat_tipo)+'</span></td>'+
      '<td>'+esc(c.equipo_nombre||'—')+'</td>'+
      '<td>'+formatFecha(c.fecha_registro)+'</td>'+
      (PUEDE_INSCRIBIR ? '<td>'+acciones+'</td>' : '')+
    '</tr>';
  }).join('');
  renderPagBib();
}

function renderPagBib(){
  var el = document.getElementById('paginacion-biblico'); if(!el) return;
  var total = Math.ceil(filtradosBib.length / porPaginaBib);
  if(total <= 1){ el.innerHTML=''; return; }
  var html = '';
  for(var i=1;i<=total;i++){
    html += '<button class="pag-btn '+(i===paginaBib?'active':'')+'" onclick="cambiarPagBib('+i+')">'+i+'</button>';
  }
  el.innerHTML = html;
}

window.cambiarPagBib = function(p){ paginaBib=p; renderBiblico(); };

function abrirModalBib(){
  document.getElementById('bib-ins-buscar').value = '';
  document.getElementById('bib-ins-id').value = '';
  document.getElementById('bib-ins-sel').style.display = 'none';
  document.getElementById('bib-ins-resultados').innerHTML = '';
  document.getElementById('bib-ins-categoria').value = '';
  document.getElementById('bib-ins-equipo').value = '';
  document.getElementById('bib-ins-equipo-wrap').style.display = 'none';
  document.getElementById('bib-ins-aviso').style.display = 'none';
  document.getElementById('modal-bib-overlay').style.display = 'grid';
}

window.cerrarModalBib = function(){
  document.getElementById('modal-bib-overlay').style.display = 'none';
};

function inscribirConcursante(){
  var insId = document.getElementById('bib-ins-id').value;
  var catId = document.getElementById('bib-ins-categoria').value;
  var equipo = document.getElementById('bib-ins-equipo').value.trim();
  var aviso = document.getElementById('bib-ins-aviso');

  if(!insId){ aviso.style.display='block'; aviso.textContent='Selecciona un inscrito del buscador'; return; }
  if(!catId){ aviso.style.display='block'; aviso.textContent='Selecciona una categoría'; return; }
  aviso.style.display='none';

  var fd = new FormData();
  fd.append('accion','inscribir_concursante');
  fd.append('inscrito_id', insId);
  fd.append('categoria_id', catId);
  fd.append('equipo_nombre', equipo);

  var btn = document.getElementById('btn-bib-guardar');
  btn.disabled=true; btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i>';
  fetch('api_concursos.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-check"></i> Inscribir';
    if(data.ok){ toast(data.msg,'ok'); cerrarModalBib(); cargarConcursantes(); }
    else { aviso.style.display='block'; aviso.textContent=data.msg; }
  });
}

window.abrirEditBib = function(json){
  var c = JSON.parse(decodeURIComponent(json));
  document.getElementById('bib-edit-id').value = c.id;
  document.getElementById('bib-edit-categoria').value = c.categoria_id || '';
  document.getElementById('bib-edit-equipo').value = c.equipo_nombre || '';
  document.getElementById('modal-bib-edit-overlay').style.display = 'grid';
};

window.cerrarModalBibEdit = function(){
  document.getElementById('modal-bib-edit-overlay').style.display = 'none';
};

function editarConcursante(){
  var fd = new FormData();
  fd.append('accion','editar_concursante');
  fd.append('id', document.getElementById('bib-edit-id').value);
  fd.append('categoria_id', document.getElementById('bib-edit-categoria').value);
  fd.append('equipo_nombre', document.getElementById('bib-edit-equipo').value.trim());
  fetch('api_concursos.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast(data.msg,'ok'); cerrarModalBibEdit(); cargarConcursantes(); }
    else toast(data.msg,'error');
  });
}

window.eliminarConcursante = function(id, nombre){
  if(!confirm('¿Eliminar a "'+nombre+'" del concurso?')) return;
  var fd = new FormData();
  fd.append('accion','eliminar_concursante'); fd.append('id',id);
  fetch('api_concursos.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast(data.msg,'ok'); cargarConcursantes(); }
    else toast(data.msg,'error');
  });
};

/* ── FASES ── */
function cargarFases(catId){
  var sel = document.getElementById('punt-fase');
  sel.innerHTML = '<option value="">Selecciona fase</option>';
  if(!catId) return;
  fetch('api_concursos.php?accion=listar_fases&categoria_id='+catId)
  .then(function(r){ return r.json(); })
  .then(function(data){
    (data.fases||[]).forEach(function(f){
      sel.innerHTML += '<option value="'+f.id+'">'+esc(f.nombre)+'</option>';
    });
  });
}

window.cerrarModalFase = function(){
  document.getElementById('modal-fase-overlay').style.display = 'none';
};

function crearFase(){
  var catId  = document.getElementById('fase-categoria-id').value;
  var nombre = document.getElementById('fase-nombre').value.trim();
  if(!nombre){ toast('Escribe el nombre de la fase','warn'); return; }
  var fd = new FormData();
  fd.append('accion','crear_fase'); fd.append('categoria_id',catId); fd.append('nombre',nombre);
  fetch('api_concursos.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast(data.msg,'ok'); cerrarModalFase(); cargarFases(catId); }
    else toast(data.msg,'error');
  });
}

/* ── PUNTUACIONES ── */
function cargarPuntuaciones(faseId){
  var cont = document.getElementById('punt-contenido');
  cont.innerHTML = '<div style="text-align:center;padding:30px;color:var(--txt-xsoft);"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</div>';
  fetch('api_concursos.php?accion=listar_puntuaciones&fase_id='+faseId)
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(!data.ok){ cont.innerHTML='<div style="padding:20px;color:var(--accent);">Error al cargar</div>'; return; }
    var lista = data.puntuaciones || [];
    if(!lista.length){ cont.innerHTML='<div style="text-align:center;padding:30px;color:var(--txt-xsoft);">No hay concursantes en esta categoría</div>'; return; }

    var html = '<div style="overflow-x:auto;"><table class="tabla-inscritos" style="min-width:600px;">'+
      '<thead><tr>'+
      '<th>#</th><th>Lugar</th><th>Nombre</th><th>Iglesia</th><th>Distrito</th><th>Equipo</th><th>Puntuación</th>'+
      (PUEDE_PUNTUAR && !window._soloVer ? '<th>Acción</th>' : '')+
      '</tr></thead><tbody>';

    lista.forEach(function(p, i){
      var lugar = i===0 ? '🥇' : i===1 ? '🥈' : i===2 ? '🥉' : (i+1)+'°';
      var inputPunt = PUEDE_PUNTUAR
        ? '<input type="number" min="0" step="0.5" value="'+p.puntuacion+'" '+
          'style="width:80px;padding:5px;border:1px solid var(--border);border-radius:6px;text-align:center;" '+
          'id="punt-inp-'+p.concursante_id+'">'
        : '<strong>'+p.puntuacion+'</strong>';
      var btnGuardar = PUEDE_PUNTUAR
        ? '<button class="btn-accion btn-confirmar" onclick="guardarPunt('+faseId+','+p.concursante_id+')" title="Guardar"><i class="fa-solid fa-floppy-disk"></i></button>'
        : '';
      html += '<tr>'+
        '<td>'+(i+1)+'</td>'+
        '<td style="font-size:18px;text-align:center;">'+lugar+'</td>'+
        '<td><strong>'+esc(p.nombre)+' '+esc(p.apellido)+'</strong></td>'+
        '<td>'+esc(p.iglesia||'—')+'</td>'+
        '<td>'+esc(p.distrito||'—')+'</td>'+
        '<td>'+esc(p.equipo_nombre||'—')+'</td>'+
        '<td>'+inputPunt+'</td>'+
        (PUEDE_PUNTUAR ? '<td>'+btnGuardar+'</td>' : '')+
      '</tr>';
    });
    html += '</tbody></table></div>';
    cont.innerHTML = html;
  });
}

window.guardarPunt = function(faseId, concursanteId){
  var inp = document.getElementById('punt-inp-'+concursanteId);
  if(!inp) return;
  var fd = new FormData();
  fd.append('accion','guardar_puntuacion');
  fd.append('fase_id', faseId);
  fd.append('concursante_id', concursanteId);
  fd.append('puntuacion', inp.value);
  fetch('api_concursos.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast('Puntuación guardada','ok'); cargarPuntuaciones(faseId); }
    else toast(data.msg,'error');
  });
};

/* ══ INIT ══ */
document.addEventListener('DOMContentLoaded', function(){
  if(typeof TIPO_CONCURSO === 'undefined') return;
  if(TIPO_CONCURSO === 'deportivo') initDeportivo();
  else initBiblico();

  /* cerrar modales con Escape */
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
      ['modal-dep-overlay','modal-bib-overlay','modal-bib-edit-overlay','modal-fase-overlay','modal-cat-overlay'].forEach(function(id){
        var el = document.getElementById(id); if(el) el.style.display='none';
      });
    }
  });
});

/* ══════════════════════════════════════
   CATEGORÍAS
══════════════════════════════════════ */
function initCategorias(){
  cargarCategoriasAdmin();

  var btnNueva = document.getElementById('btn-nueva-categoria');
  if(btnNueva) btnNueva.addEventListener('click', function(){
    abrirModalCat();
  });

  var btnGuardar = document.getElementById('btn-cat-guardar');
  if(btnGuardar) btnGuardar.addEventListener('click', guardarCategoria);

  /* mostrar/ocultar max equipo según tipo */
  var selTipo = document.getElementById('cat-tipo');
  if(selTipo) selTipo.addEventListener('change', function(){
    var wrap = document.getElementById('cat-max-equipo-wrap');
    if(wrap) wrap.style.display = this.value === 'grupal' ? 'block' : 'none';
  });
}

function cargarCategoriasAdmin(){
  fetch('api_concursos.php?accion=listar_categorias_admin')
  .then(function(r){ return r.json(); })
  .then(function(data){
    var tbody = document.getElementById('tbody-categorias');
    if(!tbody) return;
    var cats = data.categorias || [];
    if(!cats.length){
      tbody.innerHTML = '<tr><td colspan="8" class="tabla-vacia">No hay categorías</td></tr>';
      return;
    }
    tbody.innerHTML = cats.map(function(c, i){
      return '<tr>'+
        '<td>'+(i+1)+'</td>'+
        '<td><strong>'+esc(c.nombre)+'</strong></td>'+
        '<td><span class="badge '+(c.tipo==='grupal'?'badge-pendiente':'badge-confirmado')+'">'+c.tipo+'</span></td>'+
        '<td style="text-align:center;">'+c.max_por_distrito+'</td>'+
        '<td style="text-align:center;">'+(c.max_por_equipo||'—')+'</td>'+
        '<td style="text-align:center;">'+c.orden+'</td>'+
        '<td><span class="badge '+(c.activo=='1'?'badge-confirmado':'badge-pendiente')+'">'+(c.activo=='1'?'Activa':'Inactiva')+'</span></td>'+
        '<td>'+
          '<button class="btn-accion btn-ver" onclick="editarCategoria(\''+encodeURIComponent(JSON.stringify(c))+'\')" title="Editar"><i class="fa-solid fa-pen"></i></button> '+
          '<button class="btn-accion btn-eliminar" onclick="eliminarCategoria('+c.id+',\''+esc(c.nombre)+'\')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>'+
        '</td>'+
      '</tr>';
    }).join('');
  });
}

function abrirModalCat(cat){
  document.getElementById('cat-modal-titulo').textContent = cat ? 'Editar Categoría' : 'Nueva Categoría';
  document.getElementById('cat-id').value           = cat ? cat.id : '';
  document.getElementById('cat-nombre').value       = cat ? cat.nombre : '';
  document.getElementById('cat-tipo').value         = cat ? cat.tipo : 'individual';
  document.getElementById('cat-max-distrito').value = cat ? cat.max_por_distrito : 1;
  document.getElementById('cat-max-equipo').value   = cat ? (cat.max_por_equipo||'') : '';
  document.getElementById('cat-orden').value        = cat ? cat.orden : 0;
  document.getElementById('cat-activo').value       = cat ? cat.activo : 1;
  var wrap = document.getElementById('cat-max-equipo-wrap');
  if(wrap) wrap.style.display = (cat&&cat.tipo==='grupal')||(!cat) ? 'block' : 'none';
  document.getElementById('modal-cat-overlay').style.display = 'grid';
}

window.editarCategoria = function(json){
  var c = JSON.parse(decodeURIComponent(json));
  abrirModalCat(c);
};

window.cerrarModalCat = function(){
  document.getElementById('modal-cat-overlay').style.display = 'none';
};

function guardarCategoria(){
  var fd = new FormData();
  fd.append('accion','guardar_categoria');
  fd.append('id',           document.getElementById('cat-id').value);
  fd.append('nombre',       document.getElementById('cat-nombre').value.trim());
  fd.append('tipo',         document.getElementById('cat-tipo').value);
  fd.append('max_por_distrito', document.getElementById('cat-max-distrito').value);
  fd.append('max_por_equipo',   document.getElementById('cat-max-equipo').value);
  fd.append('orden',        document.getElementById('cat-orden').value);
  fd.append('activo',       document.getElementById('cat-activo').value);

  var btn = document.getElementById('btn-cat-guardar');
  btn.disabled=true; btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i>';
  fetch('api_concursos.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-floppy-disk"></i> Guardar';
    if(data.ok){
      toast(data.msg,'ok');
      cerrarModalCat();
      cargarCategoriasAdmin();
      /* recargar también el selector de categorías */
      var sel = document.getElementById('bib-ins-categoria');
      var selF = document.getElementById('punt-categoria');
      var selBib = document.getElementById('bib-categoria');
      if(sel) sel.innerHTML = '<option value="">-- Selecciona --</option>';
      if(selF) selF.innerHTML = '<option value="">Selecciona categoría</option>';
      if(selBib) selBib.innerHTML = '<option value="">Todas las categorías</option>';
      cargarCategorias();
    } else toast(data.msg,'error');
  });
}

window.eliminarCategoria = function(id, nombre){
  if(!confirm('¿Eliminar la categoría "'+nombre+'"?')) return;
  var fd = new FormData();
  fd.append('accion','eliminar_categoria'); fd.append('id',id);
  fetch('api_concursos.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast(data.msg,'ok'); cargarCategoriasAdmin(); }
    else toast(data.msg,'error');
  });
};



})();