/* ============================================================
   ADMIN-ENCUENTRO.JS
   ============================================================ */
(function(){
'use strict';

var todosInscritos     = [];
var inscritosFiltrados = [];
var paginaTabla        = 1;
var porPagina          = 15;
var inscritoCredencial = null;

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
  initDatePickers();
});

/* ══════════════════════════════════════
   DATE PICKER TIPO RULETA
══════════════════════════════════════ */
function initDatePickers(){
  crearDatePicker('ni-fecha','ni-fecha-display','ni-fecha-btn');
  crearDatePicker('edit-fecha','edit-fecha-display','edit-fecha-btn');
}

function crearDatePicker(inputId, displayId, btnId){
  var input   = document.getElementById(inputId);
  var display = document.getElementById(displayId);
  var btn     = document.getElementById(btnId);
  if(!input||!display||!btn) return;

  var hoy = new Date();
  var selY = hoy.getFullYear()-18, selM = hoy.getMonth()+1, selD = hoy.getDate();

  /* si el input ya tiene valor, úsalo */
  if(input.value){
    var parts = input.value.split('-');
    selY=parseInt(parts[0]); selM=parseInt(parts[1]); selD=parseInt(parts[2]);
  }


  var picker = document.getElementById(inputId+'-picker');
  if(!picker){
    picker = document.createElement('div');
    picker.id = inputId+'-picker';
    picker.className = 'date-picker-popup';
    picker.style.display = 'none';
    /* insertar dentro del div contenedor del botón */
    var contenedor = btn.parentNode;
    contenedor.style.position = 'relative';
    contenedor.appendChild(picker);
  }

  function diasEnMes(y,m){ return new Date(y,m,0).getDate(); }
  var meses=['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

  function render(){
    var dMax = diasEnMes(selY,selM);
    if(selD>dMax) selD=dMax;
    var years=[], anioActual=hoy.getFullYear();
    for(var y=anioActual;y>=anioActual-100;y--) years.push(y);

    picker.innerHTML =
      '<div class="dp-header">Seleccionar Fecha</div>'+
      '<div class="dp-cols">'+
        /* DÍA */
        '<div class="dp-col">'+
          '<button class="dp-arrow" data-col="d" data-dir="-1">▲</button>'+
          '<div class="dp-items" id="'+inputId+'-dcol">'+
            [selD-1,selD,selD+1].map(function(d,i){
              var real = ((d-1+dMax)%dMax)+1;
              return '<div class="dp-item'+(i===1?' dp-sel':'')+'" data-col="d" data-val="'+real+'">'+pad(real)+'</div>';
            }).join('')+
          '</div>'+
          '<button class="dp-arrow" data-col="d" data-dir="1">▼</button>'+
          '<div class="dp-label">Día</div>'+
        '</div>'+
        /* MES */
        '<div class="dp-col">'+
          '<button class="dp-arrow" data-col="m" data-dir="-1">▲</button>'+
          '<div class="dp-items">'+
            [selM-1,selM,selM+1].map(function(m,i){
              var real = ((m-1+12)%12)+1;
              return '<div class="dp-item'+(i===1?' dp-sel':'')+'" data-col="m" data-val="'+real+'">'+meses[real-1].substring(0,3)+'</div>';
            }).join('')+
          '</div>'+
          '<button class="dp-arrow" data-col="m" data-dir="1">▼</button>'+
          '<div class="dp-label">Mes</div>'+
        '</div>'+
        /* AÑO */
        '<div class="dp-col dp-col-wide">'+
          '<button class="dp-arrow" data-col="y" data-dir="-1">▲</button>'+
          '<div class="dp-items">'+
            [years.indexOf(selY)-1,years.indexOf(selY),years.indexOf(selY)+1].map(function(idx,i){
              var y = years[Math.max(0,Math.min(idx,years.length-1))];
              return '<div class="dp-item'+(i===1?' dp-sel':'')+'" data-col="y" data-val="'+y+'">'+y+'</div>';
            }).join('')+
          '</div>'+
          '<button class="dp-arrow" data-col="y" data-dir="1">▼</button>'+
          '<div class="dp-label">Año</div>'+
        '</div>'+
      '</div>'+
      '<button class="dp-ok" id="'+inputId+'-ok">Confirmar</button>';

    /* eventos flechas e items */
    picker.querySelectorAll('.dp-arrow').forEach(function(el){
      el.addEventListener('click',function(e){
        e.stopPropagation();
        var col=this.dataset.col, dir=parseInt(this.dataset.dir);
        if(col==='d'){ selD=((selD-1+dir+dMax)%dMax)+1; }
        if(col==='m'){ selM=((selM-1+dir+12)%12)+1; }
        if(col==='y'){ var idx=years.indexOf(selY); selY=years[Math.max(0,Math.min(idx+dir,years.length-1))]; }
        render();
      });
    });
    picker.querySelectorAll('.dp-item').forEach(function(el){
      el.addEventListener('click',function(e){
        e.stopPropagation();
        var col=this.dataset.col, v=parseInt(this.dataset.val);
        if(col==='d') selD=v;
        if(col==='m') selM=v;
        if(col==='y') selY=v;
        render();
      });
    });
    var ok=document.getElementById(inputId+'-ok');
    if(ok) ok.addEventListener('click',function(e){
      e.stopPropagation();
      var val=selY+'-'+pad(selM)+'-'+pad(selD);
      input.value=val;
      display.textContent=pad(selD)+'/'+pad(selM)+'/'+selY;
      picker.style.display='none';
      /* disparar change para calcular edad */
      input.dispatchEvent(new Event('change'));
    });
  }

  btn.addEventListener('click',function(e){
    e.stopPropagation();
    if(picker.style.display==='none'){
      /* si input tiene valor, sincronizar */
      if(input.value){
        var parts=input.value.split('-');
        selY=parseInt(parts[0]); selM=parseInt(parts[1]); selD=parseInt(parts[2]);
      }
      render();
      picker.style.display='block';
    } else {
      picker.style.display='none';
    }
  });

  document.addEventListener('click',function(e){
    if(!picker.contains(e.target) && e.target!==btn && !btn.contains(e.target)){
      picker.style.display='none';
    }
  });
}

function pad(n){ return n<10?'0'+n:String(n); }

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
  var inicio=Math.max(1,paginaTabla-2), fin=Math.min(totalPags,paginaTabla+2);
  if(inicio>1) html+='<button class="pag-btn" onclick="cambiarPag(1)">1</button>'+(inicio>2?'<span style="color:var(--txt-xsoft);padding:0 4px;">…</span>':'');
  for(var i=inicio;i<=fin;i++) html+='<button class="pag-btn '+(i===paginaTabla?'active':'')+'" onclick="cambiarPag('+i+')">'+i+'</button>';
  if(fin<totalPags) html+=(fin<totalPags-1?'<span style="color:var(--txt-xsoft);padding:0 4px;">…</span>':'')+'<button class="pag-btn" onclick="cambiarPag('+totalPags+')">'+totalPags+'</button>';
  html+='<button class="pag-btn" '+(paginaTabla>=totalPags?'disabled':'')+' onclick="cambiarPag('+(paginaTabla+1)+')"><i class="fa-solid fa-chevron-right"></i></button>';
  el.innerHTML=html;
}

window.cambiarPag=function(p){
  var totalPags=Math.ceil(inscritosFiltrados.length/porPagina);
  if(p<1||p>totalPags) return;
  paginaTabla=p; renderTabla();
  document.querySelector('.card') && document.querySelector('.card').scrollIntoView({behavior:'smooth',block:'start'});
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
  });

  /* calcular edad al cambiar fecha */
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

  /* ruleta cantidad */
  document.querySelectorAll('.ni-prod-cant').forEach(function(inp){
    inp.addEventListener('change',function(){ if(parseInt(this.value)<0) this.value=0; });
  });
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
  var nt=document.getElementById('ni-tipo');  if(nt) nt.value='';
  var ni=document.getElementById('ni-iglesia'); if(ni) ni.value='';
  document.querySelectorAll('input[name="ni-paquete"]').forEach(function(r){ r.checked=false; });
  document.querySelectorAll('.ni-prod-cant').forEach(function(i){ i.value='0'; });
}

function validarFormInscripcion(){
  var ok=true;
  ['ni-nombre','ni-apellido','ni-carnet','ni-celular'].forEach(function(id){
    var el=document.getElementById(id);
    if(!el||!el.value.trim()){ if(el) el.style.borderColor='#dc2626'; ok=false; }
    else if(el) el.style.borderColor='';
  });
  if(!document.getElementById('ni-fecha').value){ ok=false; toast('Selecciona la fecha de nacimiento','warn'); }
  if(!document.querySelector('input[name="ni-paquete"]:checked')){
    toast('Selecciona un paquete','warn'); ok=false;
  }
  if(!ok && document.getElementById('ni-nombre').value) toast('Completa todos los campos obligatorios','warn');
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
      prods.push({id:inp.dataset.prodId,cantidad:cant,talla:tallaEl?tallaEl.value:'',genero:tipo==='gorra'?'unisex':(genEl?genEl.value:'hombre')});
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
    fd.append('accion','editar_inscrito'); fd.append('id',val('edit-inscrito-id'));
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
  /* actualizar display del date picker */
  var dd=document.getElementById('edit-fecha-display');
  if(dd && ins.fecha_nacimiento){
    var p=ins.fecha_nacimiento.split('-');
    dd.textContent=p[2]+'/'+p[1]+'/'+p[0];
  }
  document.getElementById('edit-edad').value=ins.edad||'';
  document.getElementById('edit-celular').value=ins.celular||'';
  document.getElementById('modal-editar-overlay').style.display='grid';
};
window.cerrarModalEditar=function(){
  document.getElementById('modal-editar-overlay').style.display='none';
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
   EXPORTAR PDF — CORREGIDO
══════════════════════════════════════ */
function initExportarPDF(){
  var btn=document.getElementById('btn-exportar-pdf-tabla');
  if(!btn) return;
  btn.addEventListener('click',function(){
    if(!inscritosFiltrados.length){ toast('No hay datos para exportar','warn'); return; }
    btn.disabled=true;
    btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Generando...';
    if(window.html2pdf){
      generarPDFTabla(btn);
    } else {
      var s=document.createElement('script');
      s.src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
      s.onload=function(){ setTimeout(function(){ generarPDFTabla(btn); },300); };
      s.onerror=function(){ toast('Error al cargar librería PDF','error'); btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-file-pdf"></i> <span class="btn-txt-label">Exportar PDF</span>'; };
      document.head.appendChild(s);
    }
  });
}

function generarPDFTabla(btn){
  var filas=inscritosFiltrados.map(function(ins,i){
    var prodEnt=ins.productos&&ins.productos!=='—'?(String(ins.producto_entregado)==='1'?'✓':'✗'):'—';
    var matEnt=ins.material_entregado!==null&&ins.material_entregado!==undefined?(String(ins.material_entregado)==='1'?'✓':'✗'):'—';
    var bg=i%2===0?'#ffffff':'#f0f2f8';
    return '<tr style="background:'+bg+'">'+
      '<td style="padding:4px 6px;border-bottom:1px solid #e0e0e0;font-size:9px;">'+(i+1)+'</td>'+
      '<td style="padding:4px 6px;border-bottom:1px solid #e0e0e0;font-size:9px;font-weight:600;">'+esc(ins.nombre)+' '+esc(ins.apellido)+'</td>'+
      '<td style="padding:4px 6px;border-bottom:1px solid #e0e0e0;font-size:9px;">'+esc(ins.carnet||'')+'</td>'+
      '<td style="padding:4px 6px;border-bottom:1px solid #e0e0e0;font-size:9px;">'+esc(ins.celular||'')+'</td>'+
      '<td style="padding:4px 6px;border-bottom:1px solid #e0e0e0;font-size:9px;">'+esc(ins.iglesia||'')+'</td>'+
      '<td style="padding:4px 6px;border-bottom:1px solid #e0e0e0;font-size:9px;">'+esc(ins.paquete||'')+'</td>'+
      '<td style="padding:4px 6px;border-bottom:1px solid #e0e0e0;font-size:9px;">'+esc(ins.metodo_pago||'')+'</td>'+
      '<td style="padding:4px 6px;border-bottom:1px solid #e0e0e0;font-size:9px;font-weight:700;color:'+(ins.estado_pago==='confirmado'?'#065f46':'#92400e')+'">'+esc(ins.estado_pago||'')+'</td>'+
      '<td style="padding:4px 6px;border-bottom:1px solid #e0e0e0;font-size:9px;text-align:center;color:'+(prodEnt==='✓'?'#065f46':'#dc2626')+'">'+prodEnt+'</td>'+
      '<td style="padding:4px 6px;border-bottom:1px solid #e0e0e0;font-size:9px;text-align:center;color:'+(matEnt==='✓'?'#065f46':'#dc2626')+'">'+matEnt+'</td>'+
      '<td style="padding:4px 6px;border-bottom:1px solid #e0e0e0;font-size:9px;">'+(ins.fecha_pago?ins.fecha_pago.substring(0,10):'')+'</td>'+
    '</tr>';
  }).join('');

  var html='<!DOCTYPE html><html><head><meta charset="UTF-8">'+
    '<style>body{font-family:Arial,sans-serif;margin:0;padding:0;}'+
    'table{width:100%;border-collapse:collapse;}'+
    'th{background:#03045e;color:#fff;padding:6px;font-size:9px;text-align:left;}'+
    '</style></head><body>'+
    '<div style="padding:16px;">'+
    '<div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #03045e;padding-bottom:10px;margin-bottom:14px;">'+
      '<div>'+
        '<div style="font-size:16px;font-weight:700;color:#03045e;">Inscritos — Encuentro Departamental de Jóvenes 2026</div>'+
        '<div style="font-size:10px;color:#666;margin-top:2px;">IDDP Oruro · Tarija, julio 2026</div>'+
      '</div>'+
      '<div style="text-align:right;font-size:10px;color:#666;">'+
        'Generado: '+new Date().toLocaleString('es-BO')+'<br>'+
        'Total: '+inscritosFiltrados.length+' registros'+
      '</div>'+
    '</div>'+
    '<table>'+
    '<thead><tr>'+
      '<th>#</th><th>Nombre Completo</th><th>Carnet</th><th>Celular</th>'+
      '<th>Iglesia</th><th>Paquete</th><th>Método</th><th>Estado</th>'+
      '<th>Prod.</th><th>Mat.</th><th>Fecha</th>'+
    '</tr></thead><tbody>'+filas+'</tbody></table>'+
    '<div style="margin-top:14px;text-align:center;font-size:8px;color:#aaa;border-top:1px solid #eee;padding-top:6px;">'+
      'Sistema de inscripciones — IDDP Oruro · Lima Technology'+
    '</div>'+
    '</div></body></html>';

  var opt={
    margin:[8,6,8,6],
    filename:'inscritos_'+new Date().toISOString().substring(0,10)+'.pdf',
    image:{type:'jpeg',quality:0.95},
    html2canvas:{scale:2,useCORS:true,logging:false,backgroundColor:'#ffffff'},
    jsPDF:{unit:'mm',format:'letter',orientation:'landscape'}
  };

  html2pdf().set(opt).from(html).save()
  .then(function(){
    toast('PDF descargado correctamente','ok');
    if(btn){ btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-file-pdf"></i> <span class="btn-txt-label">Exportar PDF</span>'; }
  }).catch(function(e){
    console.error(e);
    toast('Error al generar PDF','error');
    if(btn){ btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-file-pdf"></i> <span class="btn-txt-label">Exportar PDF</span>'; }
  });
}

/* ══════════════════════════════════════
   HELPERS
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
function setText(id,v){ var el=document.getElementById(id); if(el) el.textContent=v; }
function val(id){ var el=document.getElementById(id); return el?el.value.trim():''; }
function toast(msg,tipo){
  var el=document.getElementById('toast-dashboard'); if(!el) return;
  el.textContent=msg;
  el.className='toast show'+(tipo?' toast-'+tipo:'');
  clearTimeout(toast._t);
  toast._t=setTimeout(function(){ el.classList.remove('show'); },3500);
}
function esc(s){
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

})();