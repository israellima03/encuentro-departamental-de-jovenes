/* ============================================================
   ADMIN-ENCUENTRO.JS — Solo lógica del dashboard
   ============================================================ */
(function(){
'use strict';

var todosInscritos     = [];
var inscritosFiltrados = [];
var paginaTabla        = 1;
var porPagina          = 15;
var inscritoCredencial = null;

/* ══════════════════════════════════════
   HELPERS GLOBALES (definidos primero)
══════════════════════════════════════ */
function pad(n){ return n<10?'0'+n:String(n); }
function setText(id,v){ var el=document.getElementById(id); if(el) el.textContent=v; }
function val(id){ var el=document.getElementById(id); return el?el.value.trim():''; }
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function toast(msg,tipo){
  var el=document.getElementById('toast-dashboard'); if(!el) return;
  el.textContent=msg;
  el.className='toast show'+(tipo?' toast-'+tipo:'');
  clearTimeout(toast._t);
  toast._t=setTimeout(function(){ el.classList.remove('show'); },3500);
}
function calcularEdad(fechaStr,edadId){
  if(!fechaStr) return;
  var hoy=new Date(), nac=new Date(fechaStr);
  var e=hoy.getFullYear()-nac.getFullYear();
  var m=hoy.getMonth()-nac.getMonth();
  if(m<0||(m===0&&hoy.getDate()<nac.getDate())) e--;
  var el=document.getElementById(edadId);
  if(el) el.value=e>0?e:'';
}

/* ══════════════════════════════════════
   DATE PICKER
══════════════════════════════════════ */
function crearDatePicker(inputId, displayId, btnId){
  var input   = document.getElementById(inputId);
  var display = document.getElementById(displayId);
  var btn     = document.getElementById(btnId);
  if(!input||!display||!btn) return;

  var hoy = new Date();
  var selY = hoy.getFullYear()-18, selM = hoy.getMonth()+1, selD = hoy.getDate();

  if(input.value){
    var parts = input.value.split('-');
    if(parts.length===3){
      selY=parseInt(parts[0]); selM=parseInt(parts[1]); selD=parseInt(parts[2]);
    }
  }

  /* eliminar picker anterior si existe */
  var pickerAnterior = document.getElementById(inputId+'-picker');
  if(pickerAnterior) pickerAnterior.parentNode.removeChild(pickerAnterior);

  var picker = document.createElement('div');
  picker.id = inputId+'-picker';
  picker.className = 'date-picker-popup';
  picker.style.cssText = 'display:none;position:fixed;z-index:99999;';
  document.body.appendChild(picker);

  function posicionarPicker(){
    var rect = btn.getBoundingClientRect();
    var pickerH = 160;
    var spaceAbajo = window.innerHeight - rect.bottom;
    if(spaceAbajo < pickerH && rect.top > pickerH){
      picker.style.top = (rect.top - pickerH - 6) + 'px';
    } else {
      picker.style.top = (rect.bottom + 6) + 'px';
    }
    var left = rect.left;
    if(left + 300 > window.innerWidth) left = window.innerWidth - 310;
    picker.style.left = Math.max(4, left) + 'px';
  }

  var meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
               'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

  function diasEnMes(y,m){ return new Date(y,m,0).getDate(); }

  function render(){
    var dMax = diasEnMes(selY, selM);
    if(selD > dMax) selD = dMax;

    /* opciones día */
    var optDias = '';
    for(var d=1; d<=dMax; d++){
      optDias += '<option value="'+d+'"'+(d===selD?' selected':'')+'>'+pad(d)+'</option>';
    }

    /* opciones mes */
    var optMeses = '';
    for(var m=1; m<=12; m++){
      optMeses += '<option value="'+m+'"'+(m===selM?' selected':'')+'>'+meses[m-1]+'</option>';
    }

    /* opciones año */
    var optAnios = '';
    var anioActual = hoy.getFullYear();
    for(var y=anioActual; y>=anioActual-100; y--){
      optAnios += '<option value="'+y+'"'+(y===selY?' selected':'')+'>'+y+'</option>';
    }

    picker.innerHTML =
      '<div class="dp-header">Fecha de Nacimiento</div>'+
      '<div style="display:flex;gap:8px;margin-bottom:12px;">'+
        '<div style="display:flex;flex-direction:column;gap:4px;flex:0 0 70px;">'+
          '<label style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--txt-xsoft);">Día</label>'+
          '<select id="'+inputId+'-sel-d" style="padding:7px 6px;border:1.5px solid var(--border);border-radius:8px;font-family:var(--font-body);font-size:13px;color:var(--txt);background:var(--card-bg);outline:none;cursor:pointer;">'+optDias+'</select>'+
        '</div>'+
        '<div style="display:flex;flex-direction:column;gap:4px;flex:1;">'+
          '<label style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--txt-xsoft);">Mes</label>'+
          '<select id="'+inputId+'-sel-m" style="padding:7px 6px;border:1.5px solid var(--border);border-radius:8px;font-family:var(--font-body);font-size:13px;color:var(--txt);background:var(--card-bg);outline:none;cursor:pointer;">'+optMeses+'</select>'+
        '</div>'+
        '<div style="display:flex;flex-direction:column;gap:4px;flex:0 0 80px;">'+
          '<label style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--txt-xsoft);">Año</label>'+
          '<select id="'+inputId+'-sel-y" style="padding:7px 6px;border:1.5px solid var(--border);border-radius:8px;font-family:var(--font-body);font-size:13px;color:var(--txt);background:var(--card-bg);outline:none;cursor:pointer;">'+optAnios+'</select>'+
        '</div>'+
      '</div>'+
      '<button type="button" class="dp-ok" id="'+inputId+'-dp-ok">Confirmar</button>';

    /* eventos selects */
    document.getElementById(inputId+'-sel-d').addEventListener('change', function(){
      selD = parseInt(this.value);
    });
    document.getElementById(inputId+'-sel-m').addEventListener('change', function(){
      selM = parseInt(this.value);
      /* re-renderizar para ajustar días del mes */
      render();
    });
    document.getElementById(inputId+'-sel-y').addEventListener('change', function(){
      selY = parseInt(this.value);
      /* re-renderizar para ajustar días (año bisiesto) */
      render();
    });

    /* confirmar */
    document.getElementById(inputId+'-dp-ok').addEventListener('click', function(e){
      e.stopPropagation();
      e.preventDefault();
      var v = selY+'-'+pad(selM)+'-'+pad(selD);
      input.value = v;
      display.textContent = pad(selD)+'/'+pad(selM)+'/'+selY;
      picker.style.display = 'none';
      input.dispatchEvent(new Event('change'));
    });
  }

  /* abrir/cerrar */
  btn.addEventListener('click', function(e){
    e.stopPropagation();
    e.preventDefault();
    document.querySelectorAll('.date-picker-popup').forEach(function(p){
      if(p.id !== picker.id) p.style.display='none';
    });
    if(picker.style.display==='none'||picker.style.display===''){
      if(input.value){
        var parts = input.value.split('-');
        if(parts.length===3){
          selY=parseInt(parts[0]); selM=parseInt(parts[1]); selD=parseInt(parts[2]);
        }
      }
      render();
      posicionarPicker();
      picker.style.display='block';
    } else {
      picker.style.display='none';
    }
  });

  /* cerrar al click fuera */
  document.addEventListener('click', function(e){
    if(picker.style.display==='none') return;
    if(!picker.contains(e.target) && e.target!==btn && !btn.contains(e.target)){
      picker.style.display='none';
    }
  });

  picker.addEventListener('click', function(e){ e.stopPropagation(); });
}

/* ══════════════════════════════════════
   INIT
══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function(){

  ['modal-inscripcion-overlay','modal-editar-overlay','modal-entrega-overlay'].forEach(function(id){
    var el=document.getElementById(id);
    if(el) el.addEventListener('click',function(e){ if(e.target===this) this.style.display='none'; });
  });

  document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){
      ['modal-inscripcion-overlay','modal-editar-overlay','modal-entrega-overlay'].forEach(function(id){
        var el=document.getElementById(id); if(el) el.style.display='none';
      });
      document.querySelectorAll('.date-picker-popup').forEach(function(p){ p.style.display='none'; });
    }
  });

  cargarStats();
  cargarInscritos();
  initFiltros();
  initNuevaInscripcion();
  initEditarInscrito();
  initCredencial();
  initExportarPDF();
  initModalEntrega();
});

/* ══════════════════════════════════════
   STATS
══════════════════════════════════════ */
function cargarStats(){
  fetch('api_dashboard.php?accion=stats')
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(!data.ok) return;
    animarNum('stat-total',data.total);
    animarNum('stat-pendientes',data.pendientes);
    animarNum('stat-confirmados',data.confirmados);
    animarNum('stat-cupos',data.cupos_disp);
    setText('stat-hoy','+'+data.hoy+' hoy');
    setText('stat-conf-sub',data.confirmados+' pagos verificados');
    var pct=data.cupos_total>0?Math.round(((data.cupos_total-data.cupos_disp)/data.cupos_total)*100):0;
    var bar=document.getElementById('stat-cupos-bar');
    var pctEl=document.getElementById('stat-cupos-pct');
    if(bar) setTimeout(function(){ bar.style.width=pct+'%'; },300);
    if(pctEl) pctEl.textContent=pct+'% ocupado';
  }).catch(function(e){ console.error('stats:',e); });
}

/* ══════════════════════════════════════
   INSCRITOS
══════════════════════════════════════ */
function cargarInscritos(){
  var tbody=document.getElementById('tbody-inscritos');
  if(tbody) tbody.innerHTML='<tr><td colspan="15" class="tabla-loading"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</td></tr>';
  fetch('api_dashboard.php?accion=listar_inscritos')
  .then(function(r){ return r.json(); })
  .then(function(data){
    todosInscritos=data.inscritos||[];
    inscritosFiltrados=todosInscritos.slice();
    renderTabla();
  }).catch(function(e){ console.error('inscritos:',e); });
}

function renderTabla(){
  var tbody=document.getElementById('tbody-inscritos');
  var lbl=document.getElementById('total-lbl');
  if(!tbody) return;

  if(!inscritosFiltrados.length){
    tbody.innerHTML='<tr><td colspan="15" class="tabla-vacia"><i class="fa-solid fa-inbox" style="font-size:24px;display:block;margin-bottom:8px;color:var(--border);"></i>Sin resultados</td></tr>';
    if(lbl) lbl.textContent='0 registros';
    var pag=document.getElementById('paginacion'); if(pag) pag.innerHTML='';
    return;
  }

  if(lbl) lbl.textContent=inscritosFiltrados.length+' registros';
  var inicio=(paginaTabla-1)*porPagina;
  var pagina=inscritosFiltrados.slice(inicio,inicio+porPagina);

  tbody.innerHTML=pagina.map(function(ins,i){
    var badge=ins.estado_pago==='confirmado'
      ?'<span class="badge badge-confirmado"><i class="fa-solid fa-circle-check"></i> Confirmado</span>'
      :'<span class="badge badge-pendiente"><i class="fa-solid fa-clock"></i> Pendiente</span>';
    var metodo=ins.metodo_pago==='efectivo'
      ?'<span class="metodo-efectivo"><i class="fa-solid fa-money-bill"></i> Efectivo</span>'
      :'<span class="metodo-qr"><i class="fa-solid fa-qrcode"></i> QR</span>';
    var fecha=ins.fecha_pago?ins.fecha_pago.substring(0,10):'—';

    var tieneProductos=ins.productos&&ins.productos!==''&&ins.productos!=='—';
    var prodEnt='<span class="ent-na">—</span>';
    if(tieneProductos){
      if(String(ins.producto_entregado)==='1'){
        var titulo='Entregado por: '+(ins.producto_entregado_por||'—');
        prodEnt=PUEDE_ENTREGAR
          ?'<button class="badge-ent badge-ent-si" onclick="abrirEntrega(\'producto\','+ins.inscripcion_id+',0)" title="'+titulo+'"><i class="fa-solid fa-check"></i> Sí</button>'
          :'<span class="badge-ent badge-ent-si" title="'+titulo+'"><i class="fa-solid fa-check"></i> Sí</span>';
      } else {
        prodEnt=PUEDE_ENTREGAR
          ?'<button class="badge-ent badge-ent-no" onclick="abrirEntrega(\'producto\','+ins.inscripcion_id+',1)"><i class="fa-solid fa-xmark"></i> No</button>'
          :'<span class="badge-ent badge-ent-no"><i class="fa-solid fa-xmark"></i> No</span>';
      }
    }

    var matEnt='<span class="ent-na">—</span>';
    if(ins.material_entregado!==null && ins.material_entregado!==undefined){
      if(String(ins.material_entregado)==='1'){
        var tituloM='Entregado por: '+(ins.material_entregado_por||'—');
        matEnt=PUEDE_ENTREGAR
          ?'<button class="badge-ent badge-ent-si" onclick="abrirEntrega(\'material\','+ins.inscripcion_id+',0)" title="'+tituloM+'"><i class="fa-solid fa-check"></i> Sí</button>'
          :'<span class="badge-ent badge-ent-si" title="'+tituloM+'"><i class="fa-solid fa-check"></i> Sí</span>';
      } else {
        matEnt=PUEDE_ENTREGAR
          ?'<button class="badge-ent badge-ent-no" onclick="abrirEntrega(\'material\','+ins.inscripcion_id+',1)"><i class="fa-solid fa-xmark"></i> No</button>'
          :'<span class="badge-ent badge-ent-no"><i class="fa-solid fa-xmark"></i> No</span>';
      }
    }

    var acciones='';
    if(typeof PUEDE_GESTIONAR!=='undefined'&&PUEDE_GESTIONAR){
      var insJson=encodeURIComponent(JSON.stringify(ins));
      acciones+='<button class="btn-accion btn-ver" onclick="abrirEditar(\''+insJson+'\')" title="Editar"><i class="fa-solid fa-pen"></i></button> ';
    }
    acciones+='<button class="btn-accion btn-credencial" onclick="descargarCredencial('+ins.id+')" title="Credencial PDF"><i class="fa-solid fa-id-card"></i></button>';

    return '<tr>'+
      '<td>'+(inicio+i+1)+'</td>'+
      '<td><div class="participante-cell">'+
        '<div class="participante-avatar">'+(ins.nombre[0]||'').toUpperCase()+(ins.apellido[0]||'').toUpperCase()+'</div>'+
        '<div><div class="participante-nombre">'+esc(ins.nombre)+' '+esc(ins.apellido)+'</div>'+
        '<div class="participante-tipo">'+esc(ins.carnet)+'</div></div>'+
      '</div></td>'+
      '<td>'+esc(ins.celular||'—')+'</td>'+
      '<td>'+esc(ins.iglesia||'—')+'</td>'+
      '<td>'+esc(ins.distrito||'—')+'</td>'+
      '<td>'+esc(ins.paquete||'—')+'</td>'+
      '<td class="col-productos">'+esc(ins.productos||'—')+'</td>'+
      '<td>'+metodo+'</td>'+
      '<td>'+badge+'</td>'+
      '<td class="col-entrega">'+prodEnt+'</td>'+
      '<td class="col-entrega">'+matEnt+'</td>'+
      '<td>'+fecha+'</td>'+
      '<td class="col-usuario">'+esc(ins.registro_por||'—')+'</td>'+
      '<td class="col-usuario">'+esc(ins.confirmo_por||'—')+'</td>'+
      '<td class="col-acciones">'+acciones+'</td>'+
    '</tr>';
  }).join('');

  renderPaginacion();
}

function renderPaginacion(){
  var el=document.getElementById('paginacion'); if(!el) return;
  var totalPags=Math.ceil(inscritosFiltrados.length/porPagina);
  if(totalPags<=1){ el.innerHTML=''; return; }
  var html='<button class="pag-btn" '+(paginaTabla<=1?'disabled':'')+' onclick="cambiarPag('+(paginaTabla-1)+')"><i class="fa-solid fa-chevron-left"></i></button>';
  var ini=Math.max(1,paginaTabla-2), fin=Math.min(totalPags,paginaTabla+2);
  if(ini>1) html+='<button class="pag-btn" onclick="cambiarPag(1)">1</button>'+(ini>2?'<span style="color:var(--txt-xsoft);padding:0 4px;">…</span>':'');
  for(var i=ini;i<=fin;i++) html+='<button class="pag-btn '+(i===paginaTabla?'active':'')+'" onclick="cambiarPag('+i+')">'+i+'</button>';
  if(fin<totalPags) html+=(fin<totalPags-1?'<span style="color:var(--txt-xsoft);padding:0 4px;">…</span>':'')+'<button class="pag-btn" onclick="cambiarPag('+totalPags+')">'+totalPags+'</button>';
  html+='<button class="pag-btn" '+(paginaTabla>=totalPags?'disabled':'')+' onclick="cambiarPag('+(paginaTabla+1)+')"><i class="fa-solid fa-chevron-right"></i></button>';
  el.innerHTML=html;
}

window.cambiarPag=function(p){
  var totalPags=Math.ceil(inscritosFiltrados.length/porPagina);
  if(p<1||p>totalPags) return;
  paginaTabla=p; renderTabla();
  var card=document.querySelector('.card');
  if(card) card.scrollIntoView({behavior:'smooth',block:'start'});
};

/* ══════════════════════════════════════
   FILTROS
══════════════════════════════════════ */
function initFiltros(){
  ['filtro-carnet','filtro-iglesia','filtro-distrito','filtro-pago','filtro-estado'].forEach(function(id){
    var el=document.getElementById(id);
    if(el){ el.addEventListener('input',aplicarFiltros); el.addEventListener('change',aplicarFiltros); }
  });
}

function aplicarFiltros(){
  var q=val('filtro-carnet'),igl=val('filtro-iglesia'),dist=val('filtro-distrito'),pago=val('filtro-pago'),est=val('filtro-estado');
  inscritosFiltrados=todosInscritos.filter(function(ins){
    return (!q||(ins.nombre+' '+ins.apellido+' '+ins.carnet).toLowerCase().includes(q.toLowerCase()))
      &&(!igl||String(ins.iglesia_id)===igl)
      &&(!dist||String(ins.distrito_id)===dist)
      &&(!pago||ins.metodo_pago===pago)
      &&(!est||ins.estado_pago===est);
  });
  paginaTabla=1; renderTabla();
}

/* ══════════════════════════════════════
   MODAL ENTREGA
══════════════════════════════════════ */
var _entregaTipo=null, _entregaIns=null, _entregaVal=null;

function initModalEntrega(){
  var btnConf=document.getElementById('btn-confirmar-entrega');
  if(btnConf) btnConf.addEventListener('click',function(){
    var fd=new FormData();
    fd.append('accion',_entregaTipo==='producto'?'marcar_producto_entregado':'marcar_material_entregado');
    fd.append('inscripcion_id',_entregaIns);
    fd.append('entregado',_entregaVal);
    btnConf.disabled=true;
    fetch('api_dashboard.php',{method:'POST',body:fd})
    .then(function(r){ return r.json(); })
    .then(function(data){
      btnConf.disabled=false;
      if(data.ok){ toast(data.msg,'ok'); document.getElementById('modal-entrega-overlay').style.display='none'; cargarInscritos(); }
      else toast(data.msg,'error');
    }).catch(function(){ toast('Error de conexion','error'); btnConf.disabled=false; });
  });
}

window.abrirEntrega=function(tipo,inscripcionId,nuevoVal){
  _entregaTipo=tipo; _entregaIns=inscripcionId; _entregaVal=nuevoVal;
  var detalle=document.getElementById('entrega-detalle');
  var txt=document.getElementById('entrega-txt');
  var accion=nuevoVal===1?'marcar como ENTREGADO':'desmarcar (NO entregado)';
  var item=tipo==='producto'?'los productos':'el material/regalo';
  txt.textContent='¿Confirmas '+accion+' '+item+' de este inscrito?';
  detalle.innerHTML='<p style="text-align:center;color:var(--txt-xsoft);font-size:12px;"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</p>';
  document.getElementById('modal-entrega-overlay').style.display='grid';
  fetch('api_dashboard.php?accion=detalle_entrega&inscripcion_id='+inscripcionId+'&tipo='+tipo)
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(!data.ok||!data.items||!data.items.length){ detalle.innerHTML=''; return; }
    var html='<div style="background:var(--bg);border-radius:8px;padding:12px;margin-bottom:4px;">'+
      '<p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--txt-xsoft);margin-bottom:8px;">'+
        (tipo==='producto'?'Productos a entregar:':'Material a entregar:')+
      '</p>'+
      data.items.map(function(it){
        var gen=it.genero==='mujer'?'<i class="fa-solid fa-venus" style="color:#e879a0;"></i> Mujer'
          :it.genero==='unisex'?'<i class="fa-solid fa-circle" style="color:#8b5cf6;"></i> Unisex'
          :it.genero?'<i class="fa-solid fa-mars" style="color:#4f90d4;"></i> Hombre':'';
        return '<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--border);">'+
          '<span style="font-weight:600;font-size:13px;">'+esc(it.nombre)+'</span>'+
          '<span style="font-size:12px;color:var(--txt-soft);">x'+it.cantidad+(it.talla?' | <strong>'+esc(it.talla)+'</strong>':'')+(it.genero?' '+gen:'')+'</span>'+
        '</div>';
      }).join('')+'</div>';
    detalle.innerHTML=html;
  }).catch(function(){ detalle.innerHTML=''; });
};

/* ══════════════════════════════════════
   NUEVA INSCRIPCION EFECTIVO
══════════════════════════════════════ */
function initNuevaInscripcion(){
  var btnAbrir=document.getElementById('btn-nueva-inscripcion');
  if(btnAbrir) btnAbrir.addEventListener('click',function(){
    resetModalInscripcion();
    document.getElementById('modal-inscripcion-overlay').style.display='grid';
    /* inicializar picker al abrir el modal */
    setTimeout(function(){
      crearDatePicker('ni-fecha','ni-fecha-display','ni-fecha-btn');
    },50);
  });

  var elFecha=document.getElementById('ni-fecha');
  if(elFecha) elFecha.addEventListener('change',function(){
    calcularEdad(this.value,'ni-edad');
  });

  var elIgl=document.getElementById('ni-iglesia');
  if(elIgl) elIgl.addEventListener('change',function(){
    var opt=this.options[this.selectedIndex];
    document.getElementById('ni-distrito-id').value=opt.dataset.distritoId||'';
    document.getElementById('ni-distrito-nombre').value=opt.dataset.distrito||'Sin distrito';
  });

  var btnRes=document.getElementById('btn-ni-resumen');
  if(btnRes) btnRes.addEventListener('click',function(){
    if(!validarFormInscripcion()) return;
    mostrarResumenInscripcion();
  });

  var btnConf=document.getElementById('btn-ni-confirmar');
  if(btnConf) btnConf.addEventListener('click',confirmarInscripcionEfectivo);
}

function resetModalInscripcion(){
  document.getElementById('paso-datos').style.display='block';
  document.getElementById('paso-resumen').style.display='none';
  document.getElementById('btn-ni-resumen').style.display='inline-flex';
  document.getElementById('btn-ni-confirmar').style.display='none';
  document.getElementById('btn-ni-volver').style.display='none';
  ['ni-nombre','ni-apellido','ni-carnet','ni-celular','ni-edad','ni-distrito-nombre','ni-distrito-id'].forEach(function(id){
    var el=document.getElementById(id); if(el) el.value='';
  });
  var nf=document.getElementById('ni-fecha'); if(nf) nf.value='';
  var nd=document.getElementById('ni-fecha-display'); if(nd) nd.textContent='';
  var nt=document.getElementById('ni-tipo'); if(nt) nt.value='';
  var ni=document.getElementById('ni-iglesia'); if(ni) ni.value='';
  document.querySelectorAll('input[name="ni-paquete"]').forEach(function(r){ r.checked=false; });
  document.querySelectorAll('.ni-prod-cant').forEach(function(i){ i.value='0'; });
  /* resetear displays de ruleta */
  document.querySelectorAll('.ni-prod-cant-display').forEach(function(d){ d.textContent='0'; });
}

function validarFormInscripcion(){
  var ok=true;
  ['ni-nombre','ni-apellido','ni-carnet','ni-celular'].forEach(function(id){
    var el=document.getElementById(id);
    if(!el||!el.value.trim()){ if(el) el.style.borderColor='#dc2626'; ok=false; }
    else if(el) el.style.borderColor='';
  });
  if(!document.getElementById('ni-fecha').value){
    ok=false; toast('Selecciona la fecha de nacimiento','warn');
  }
  if(!document.querySelector('input[name="ni-paquete"]:checked')){
    toast('Selecciona un paquete','warn'); ok=false;
  }
  if(!ok && document.getElementById('ni-nombre') && document.getElementById('ni-nombre').value){
    toast('Completa todos los campos obligatorios','warn');
  }
  return ok;
}

function mostrarResumenInscripcion(){
  var radio=document.querySelector('input[name="ni-paquete"]:checked');
  var precioPaq=parseFloat(radio.dataset.precio)||0, total=precioPaq, prodRows='';
  document.querySelectorAll('.ni-prod-cant').forEach(function(inp){
    var cant=parseInt(inp.value)||0;
    if(cant>0){
      var tallaEl=document.querySelector('.ni-prod-talla[data-prod-id="'+inp.dataset.prodId+'"]');
      var sub=cant*(parseFloat(inp.dataset.precio)||0);
      total+=sub;
      prodRows+=fila(esc(inp.dataset.nombre),cant+'x — Bs. '+sub.toFixed(2)+(tallaEl&&tallaEl.value?' | '+esc(tallaEl.value):''));
    }
  });
  var iglSel=document.getElementById('ni-iglesia');
  var iglNombre=iglSel&&iglSel.selectedIndex>=0?iglSel.options[iglSel.selectedIndex].text:'—';
  var html='<div style="font-size:13px;">'+
    fila('Nombre',esc(val('ni-nombre')+' '+val('ni-apellido')))+
    fila('Carnet',esc(val('ni-carnet')))+
    fila('Celular',esc(val('ni-celular')))+
    fila('Iglesia',esc(iglNombre))+
    fila('Paquete',esc(radio.dataset.nombre)+' — Bs. '+precioPaq.toFixed(2))+
    prodRows+
    '<div style="background:#03045e;color:#fff;border-radius:8px;padding:12px 16px;display:flex;justify-content:space-between;margin-top:12px;">'+
    '<span style="font-weight:700;">TOTAL A PAGAR</span>'+
    '<strong style="font-size:18px;">Bs. '+total.toFixed(2)+'</strong></div></div>';
  document.getElementById('resumen-contenido').innerHTML=html;
  document.getElementById('paso-datos').style.display='none';
  document.getElementById('paso-resumen').style.display='block';
  document.getElementById('btn-ni-resumen').style.display='none';
  document.getElementById('btn-ni-confirmar').style.display='inline-flex';
  document.getElementById('btn-ni-volver').style.display='inline-flex';
}

function fila(l,v){
  return '<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #eee;">'+
    '<span style="color:#666;font-weight:600;">'+l+'</span><span>'+v+'</span></div>';
}

window.volverDatos=function(){
  document.getElementById('paso-datos').style.display='block';
  document.getElementById('paso-resumen').style.display='none';
  document.getElementById('btn-ni-resumen').style.display='inline-flex';
  document.getElementById('btn-ni-confirmar').style.display='none';
  document.getElementById('btn-ni-volver').style.display='none';
};

function confirmarInscripcionEfectivo(){
  var radio=document.querySelector('input[name="ni-paquete"]:checked');
  var prods=[];
  document.querySelectorAll('.ni-prod-cant').forEach(function(inp){
    var cant=parseInt(inp.value)||0;
    if(cant>0){
      var tipo=inp.dataset.tipo||'';
      var tallaEl=document.querySelector('.ni-prod-talla[data-prod-id="'+inp.dataset.prodId+'"]');
      var genEl=document.querySelector('.ni-prod-genero[data-prod-id="'+inp.dataset.prodId+'"]');
      prods.push({
        id:inp.dataset.prodId, cantidad:cant,
        talla:tallaEl?tallaEl.value:'',
        genero:tipo==='gorra'?'unisex':(genEl?genEl.value:'hombre')
      });
    }
  });
  var fd=new FormData();
  fd.append('accion','registrar_efectivo');
  fd.append('nombre',val('ni-nombre')); fd.append('apellido',val('ni-apellido'));
  fd.append('carnet',val('ni-carnet')); fd.append('fecha_nacimiento',val('ni-fecha'));
  fd.append('edad',val('ni-edad')); fd.append('celular',val('ni-celular'));
  fd.append('tipo_inscrito_id',val('ni-tipo')); fd.append('iglesia_id',val('ni-iglesia'));
  fd.append('distrito_id',val('ni-distrito-id')); fd.append('paquete_id',radio.value);
  fd.append('productos_json',JSON.stringify(prods));
  var btnConf=document.getElementById('btn-ni-confirmar');
  btnConf.disabled=true; btnConf.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Registrando...';
  fetch('api_dashboard.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    btnConf.disabled=false; btnConf.innerHTML='<i class="fa-solid fa-check"></i> Registrar y Confirmar';
    if(data.ok){
      window.cerrarModalInscripcion();
      toast('Inscripcion registrada y confirmada','ok');
      cargarInscritos(); cargarStats();
      if(data.credencial) window.open('../credenciales/'+data.credencial,'_blank');
    } else toast(data.msg,'error');
  }).catch(function(){ toast('Error de conexion','error'); btnConf.disabled=false; });
}

window.cerrarModalInscripcion=function(){
  document.getElementById('modal-inscripcion-overlay').style.display='none';
  document.querySelectorAll('.date-picker-popup').forEach(function(p){ p.style.display='none'; });
};

/* ══════════════════════════════════════
   EDITAR INSCRITO
══════════════════════════════════════ */
function initEditarInscrito(){
  var editFecha=document.getElementById('edit-fecha');
  if(editFecha) editFecha.addEventListener('change',function(){
    calcularEdad(this.value,'edit-edad');
  });
  var btnG=document.getElementById('btn-guardar-editar');
  if(btnG) btnG.addEventListener('click',function(){
    var fd=new FormData();
    fd.append('accion','editar_inscrito');
    fd.append('id',val('edit-inscrito-id'));
    fd.append('nombre',val('edit-nombre')); fd.append('apellido',val('edit-apellido'));
    fd.append('carnet',val('edit-carnet')); fd.append('fecha_nacimiento',val('edit-fecha'));
    fd.append('edad',val('edit-edad')); fd.append('celular',val('edit-celular'));
    fetch('api_dashboard.php',{method:'POST',body:fd})
    .then(function(r){ return r.json(); })
    .then(function(data){
      if(data.ok){ toast('Inscrito actualizado','ok'); window.cerrarModalEditar(); cargarInscritos(); }
      else toast(data.msg,'error');
    });
  });
}

window.abrirEditar=function(json){
  var ins=JSON.parse(decodeURIComponent(json));
  document.getElementById('edit-inscrito-id').value=ins.id;
  document.getElementById('edit-nombre').value=ins.nombre;
  document.getElementById('edit-apellido').value=ins.apellido;
  document.getElementById('edit-carnet').value=ins.carnet;
  document.getElementById('edit-fecha').value=ins.fecha_nacimiento||'';
  var dd=document.getElementById('edit-fecha-display');
  if(dd && ins.fecha_nacimiento){
    var p=ins.fecha_nacimiento.split('-');
    if(p.length===3) dd.textContent=p[2]+'/'+p[1]+'/'+p[0];
  }
  document.getElementById('edit-edad').value=ins.edad||'';
  document.getElementById('edit-celular').value=ins.celular||'';
  document.getElementById('modal-editar-overlay').style.display='grid';
  /* inicializar picker del editar */
  setTimeout(function(){
    crearDatePicker('edit-fecha','edit-fecha-display','edit-fecha-btn');
  },50);
};

window.cerrarModalEditar=function(){
  document.getElementById('modal-editar-overlay').style.display='none';
  document.querySelectorAll('.date-picker-popup').forEach(function(p){ p.style.display='none'; });
};

/* ══════════════════════════════════════
   CREDENCIAL
══════════════════════════════════════ */
function initCredencial(){
  var inp=document.getElementById('buscar-credencial');
  var btn=document.getElementById('btn-descargar-credencial');
  var timer;
  if(btn) btn.disabled=true;
  inscritoCredencial=null;
  if(inp) inp.addEventListener('input',function(){
    clearTimeout(timer);
    var q=this.value.trim();
    inscritoCredencial=null;
    if(btn) btn.disabled=true;
    var res=document.getElementById('credencial-resultados');
    if(q.length<2){ if(res) res.innerHTML=''; return; }
    timer=setTimeout(function(){
      fetch('api_dashboard.php?accion=buscar_inscrito&q='+encodeURIComponent(q))
      .then(function(r){ return r.json(); })
      .then(function(data){
        if(!res) return;
        if(!data.inscritos||!data.inscritos.length){
          res.innerHTML='<p style="color:var(--txt-xsoft);font-size:12px;margin-top:4px;">Sin resultados</p>';
          return;
        }
        res.innerHTML=data.inscritos.map(function(i){
          return '<div class="cred-resultado" onclick="seleccionarCredencial('+i.id+',\''+esc(i.nombre+' '+i.apellido)+'\')">'+
            '<i class="fa-solid fa-user" style="color:var(--accent);margin-right:6px;"></i>'+
            esc(i.nombre)+' '+esc(i.apellido)+' — <small style="color:var(--txt-xsoft);">'+esc(i.carnet)+'</small></div>';
        }).join('');
      }).catch(function(){});
    },350);
  });
  if(btn) btn.addEventListener('click',function(){
    if(!inscritoCredencial){ toast('Selecciona un inscrito primero','warn'); return; }
    descargarCredencial(inscritoCredencial);
  });
}

window.seleccionarCredencial=function(id,nombre){
  inscritoCredencial=id;
  document.getElementById('buscar-credencial').value=nombre;
  document.getElementById('credencial-resultados').innerHTML='';
  var btn=document.getElementById('btn-descargar-credencial');
  if(btn) btn.disabled=false;
};

window.descargarCredencial=function(inscritoId){
  var btn=document.getElementById('btn-descargar-credencial');
  if(btn){ btn.disabled=true; btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Generando...'; }
  var fd=new FormData();
  fd.append('accion','descargar_credencial');
  fd.append('inscrito_id',inscritoId);
  fetch('api_dashboard.php',{method:'POST',body:fd})
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(btn){ btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-download"></i> <span class="btn-txt-label"> Descargar Credencial PDF</span>'; }
    if(data.ok&&data.credencial){
      window.open('../credenciales/'+data.credencial,'_blank');
      toast('Credencial generada','ok');
    } else toast(data.msg||'Error al generar credencial','error');
  }).catch(function(){
    if(btn){ btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-download"></i> <span class="btn-txt-label"> Descargar Credencial PDF</span>'; }
    toast('Error de conexion','error');
  });
};

/* ══════════════════════════════════════
   EXPORTAR PDF
══════════════════════════════════════ */
/* ══════════════════════════════════════
   EXPORTAR PDF
══════════════════════════════════════ */
function initExportarPDF(){
  var btn = document.getElementById('btn-exportar-pdf-tabla');
  if(!btn) return;
  btn.addEventListener('click', function(){
    if(!inscritosFiltrados.length){ toast('No hay datos para exportar','warn'); return; }
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generando...';

    function cargarScript(src, cb){
      var s = document.createElement('script');
      s.src = src;
      s.onload = cb;
      s.onerror = function(){
        toast('Error al cargar librería PDF','error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-file-pdf"></i> <span class="btn-txt-label">Exportar PDF</span>';
      };
      document.head.appendChild(s);
    }

    function intentarGenerar(){
      if(window.jspdf && window.jspdf.jsPDF && window.jspdf.jsPDF.API && window.jspdf.jsPDF.API.autoTable){
        generarPDFTabla(btn);
      } else if(window.jspdf && window.jspdf.jsPDF){
        /* jsPDF cargó pero falta autoTable */
        cargarScript(
          'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js',
          function(){ setTimeout(function(){ generarPDFTabla(btn); }, 100); }
        );
      } else {
        /* cargar jsPDF primero */
        cargarScript(
          'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
          function(){
            cargarScript(
              'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js',
              function(){ setTimeout(function(){ generarPDFTabla(btn); }, 100); }
            );
          }
        );
      }
    }
    intentarGenerar();
  });
}

function generarPDFTabla(btn){
  try {
    var doc = new window.jspdf.jsPDF({ orientation:'landscape', unit:'mm', format:'a4' });

    /* ── cabecera ── */
    doc.setFillColor(3, 4, 94);
    doc.rect(0, 0, 297, 18, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(11);
    doc.setFont('helvetica','bold');
    doc.text('Inscritos — Encuentro Departamental de Jóvenes 2026', 10, 7);
    doc.setFontSize(8);
    doc.setFont('helvetica','normal');
    doc.text('IDDP Oruro · Tarija, julio 2026', 10, 13);
    var fecha = new Date().toLocaleString('es-BO');
    doc.text('Generado: ' + fecha + '   Total: ' + inscritosFiltrados.length + ' registros', 287, 13, { align:'right' });
    doc.setTextColor(0, 0, 0);

    /* ── filas ── */
    var body = inscritosFiltrados.map(function(ins, i){
      var prodEnt = '—';
      if(ins.productos && ins.productos !== '' && ins.productos !== '—'){
        prodEnt = String(ins.producto_entregado) === '1' ? '✓' : '✗';
      }
      var matEnt = '—';
      if(ins.material_entregado !== null && ins.material_entregado !== undefined){
        matEnt = String(ins.material_entregado) === '1' ? '✓' : '✗';
      }
      return [
        i + 1,
        (ins.nombre || '') + ' ' + (ins.apellido || ''),
        ins.carnet   || '—',
        ins.celular  || '—',
        ins.iglesia  || '—',
        ins.distrito || '—',
        ins.paquete  || '—',
        ins.metodo_pago  || '—',
        ins.estado_pago  || '—',
        prodEnt,
        matEnt,
        ins.fecha_pago ? ins.fecha_pago.substring(0,10) : '—'
      ];
    });

    /* ── tabla ── */
    doc.autoTable({
      startY: 22,
      head: [[
        '#','Nombre Completo','Carnet','Celular',
        'Iglesia','Distrito','Paquete',
        'Método','Estado','Prod.','Mat.','Fecha'
      ]],
      body: body,
      styles: {
        fontSize: 7.5,
        cellPadding: 2.5,
        overflow: 'linebreak',
        valign: 'middle'
      },
      headStyles: {
        fillColor: [3, 4, 94],
        textColor: 255,
        fontStyle: 'bold',
        fontSize: 8
      },
      alternateRowStyles: {
        fillColor: [240, 242, 248]
      },
      columnStyles: {
        0:  { cellWidth: 8,  halign:'center' },   /* # */
        1:  { cellWidth: 38 },                     /* Nombre */
        2:  { cellWidth: 18 },                     /* Carnet */
        3:  { cellWidth: 18 },                     /* Celular */
        4:  { cellWidth: 28 },                     /* Iglesia */
        5:  { cellWidth: 22 },                     /* Distrito */
        6:  { cellWidth: 30 },                     /* Paquete */
        7:  { cellWidth: 16, halign:'center' },    /* Método */
        8:  { cellWidth: 20, halign:'center' },    /* Estado */
        9:  { cellWidth: 12, halign:'center' },    /* Prod */
        10: { cellWidth: 12, halign:'center' },    /* Mat */
        11: { cellWidth: 22, halign:'center' }     /* Fecha */
      },
      didDrawCell: function(data){
        /* colorear ✓ en verde y ✗ en rojo */
        if(data.section === 'body' && (data.column.index === 9 || data.column.index === 10)){
          var val = String(data.cell.raw);
          if(val === '✓') data.cell.styles.textColor = [6, 95, 70];
          if(val === '✗') data.cell.styles.textColor = [220, 0, 43];
        }
        /* colorear estado */
        if(data.section === 'body' && data.column.index === 8){
          var est = String(data.cell.raw);
          if(est === 'confirmado') data.cell.styles.textColor = [6, 95, 70];
          if(est === 'pendiente')  data.cell.styles.textColor = [146, 64, 14];
        }
      },
      margin: { top: 22, left: 7, right: 7, bottom: 10 },
      tableWidth: 'auto'
    });

    /* ── pie de página ── */
    var totalPags = doc.internal.getNumberOfPages();
    for(var p = 1; p <= totalPags; p++){
      doc.setPage(p);
      doc.setFontSize(7);
      doc.setTextColor(150);
      doc.text(
        'Sistema de inscripciones — IDDP Oruro · Lima Technology   |   Pág. ' + p + '/' + totalPags,
        148.5, 205, { align:'center' }
      );
    }

    var nombreArchivo = 'inscritos_' + new Date().toISOString().substring(0,10) + '.pdf';
    doc.save(nombreArchivo);

    toast('PDF descargado correctamente','ok');
  } catch(e){
    console.error('PDF error:', e);
    toast('Error al generar PDF: ' + e.message, 'error');
  } finally {
    if(btn){
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-file-pdf"></i> <span class="btn-txt-label">Exportar PDF</span>';
    }
  }
}
/* ══════════════════════════════════════
   ANIMACIONES
══════════════════════════════════════ */
function animarNum(id,objetivo){
  var el=document.getElementById(id); if(!el) return;
  var t0=null,dur=700;
  function step(ts){
    if(!t0) t0=ts;
    var p=Math.min((ts-t0)/dur,1);
    el.textContent=Math.round(p*objetivo);
    if(p<1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

})();