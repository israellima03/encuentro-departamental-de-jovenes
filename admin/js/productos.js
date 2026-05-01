/* ============================================================
   PRODUCTOS.JS
   ============================================================ */
(function(){
'use strict';

var todosProductos = [];
var todosPedidos   = [];
var pedidosFilt    = [];

/* ── CARGAR TODO ── */
function init(){
  cargarProductos();
  cargarPedidos();
  initAddPedido();
  initCrearProducto();

  /* filtros pedidos */
  ['ped-buscar','ped-producto','ped-talla','ped-genero'].forEach(function(id){
    var el = document.getElementById(id);
    if(el) el.addEventListener('input',  filtrarPedidos);
    if(el) el.addEventListener('change', filtrarPedidos);
  });

  /* exportar PDF */
  var btnPDF = document.getElementById('btn-exportar-pdf-prod');
  if(btnPDF) btnPDF.addEventListener('click', exportarPDF);
}

/* ── PRODUCTOS ── */
function cargarProductos(){
  fetch('api_productos.php?accion=listar_productos')
  .then(function(r){ return r.json(); })
  .then(function(data){
    todosProductos = data.productos || [];
    renderProductos();
    poblarSelectProducto();
  });
}

function renderProductos(){
  var grid = document.getElementById('prod-grid');
  if(!todosProductos.length){
    grid.innerHTML = '<p style="color:var(--txt-xsoft);padding:20px;">No hay productos</p>';
    return;
  }
  grid.innerHTML = todosProductos.map(function(p){
    var pct = p.cupo_total > 0 ? Math.round(((p.cupo_total - p.cupos_disponibles)/p.cupo_total)*100) : 0;
    var imgSrc = p.imagen ? '../img/' + p.imagen : '';
    return '<div class="prod-card">' +
      '<div class="prod-card-img">' +
        (imgSrc
          ? '<img src="' + imgSrc + '" alt="' + esc(p.nombre) + '">'
          : '<div class="prod-no-img"><i class="fa-solid fa-shirt"></i></div>') +
      '</div>' +
      '<div class="prod-card-body">' +
        '<h4>' + esc(p.nombre) + '</h4>' +
        '<p class="prod-tipo">' + esc(p.tipo||'') + '</p>' +
        '<p class="prod-precio">Bs. ' + parseFloat(p.precio).toFixed(2) + '</p>' +
        '<div class="prod-cupos">' +
          '<div class="prod-cupos-bar"><div class="prod-cupos-fill" style="width:'+pct+'%"></div></div>' +
          '<span>' + p.cupos_disponibles + ' / ' + p.cupo_total + ' disponibles</span>' +
        '</div>' +
      '</div>' +
      '<div class="prod-card-footer">' +
        '<button class="btn-tool" onclick="editarProducto('+p.id+')"><i class="fa-solid fa-pen"></i> Editar</button>' +
        '<button class="btn-tool" onclick="verTallas('+p.id+')"><i class="fa-solid fa-ruler"></i> Tallas</button>' +
      '</div>' +
    '</div>';
  }).join('');
}

/* ── EDITAR PRODUCTO ── */
window.editarProducto = function(id){
  var p = todosProductos.find(function(x){ return x.id == id; });
  if(!p) return;
  var body = document.getElementById('modal-prod-body');
  body.innerHTML =
    '<div class="modal-grid" style="grid-template-columns:1fr 1fr;">' +
    mf('Nombre', '<input type="text" id="ep-nombre" class="input-filtro" value="' + esc(p.nombre) + '" style="width:100%;">') +
    mf('Precio (Bs.)', '<input type="number" id="ep-precio" class="input-filtro" value="' + p.precio + '" step="0.01" style="width:100%;">') +
    mf('Cupos total', '<input type="number" id="ep-cupos" class="input-filtro" value="' + p.cupo_total + '" style="width:100%;">') +
    mf('Cupos disponibles', '<input type="number" id="ep-cupos-disp" class="input-filtro" value="' + p.cupos_disponibles + '" style="width:100%;">') +
    '<div class="modal-field full">' +
      '<span class="modal-label">Imagen del producto</span>' +
      (p.imagen ? '<img src="../img/'+p.imagen+'" style="width:100px;border-radius:8px;margin-bottom:8px;display:block;">' : '') +
      '<input type="file" id="ep-imagen" accept="image/*">' +
    '</div>' +
    '</div>';

  document.getElementById('btn-guardar-prod').onclick = function(){
    var fd = new FormData();
    fd.append('accion',            'editar_producto');
    fd.append('id',                id);
    fd.append('nombre',            document.getElementById('ep-nombre').value);
    fd.append('precio',            document.getElementById('ep-precio').value);
    fd.append('cupos',             document.getElementById('ep-cupos').value);
    fd.append('cupos_disponibles', document.getElementById('ep-cupos-disp').value);
    var img = document.getElementById('ep-imagen').files[0];
    if(img) fd.append('imagen', img);

    fetch('api_productos.php', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if(data.ok){
        toast('Producto actualizado','ok');
        document.getElementById('modal-prod-overlay').classList.remove('open');
        cargarProductos();
      } else {
        toast(data.msg,'error');
      }
    });
  };

  document.getElementById('modal-prod-overlay').classList.add('open');
};

/* ── TALLAS ── */
window.verTallas = function(pid){
  fetch('api_productos.php?accion=listar_tallas&producto_id='+pid)
  .then(function(r){ return r.json(); })
  .then(function(data){
    var tallas = data.tallas || [];
    var body   = document.getElementById('modal-tallas-body');

    var html = '<div style="overflow-x:auto;">' +
      '<table style="width:100%;border-collapse:collapse;font-size:13px;">' +
      '<thead><tr style="background:var(--sidebar-bg);color:#fff;">' +
      '<th style="padding:9px 10px;text-align:left;">Talla</th>' +
      '<th style="padding:9px 10px;">Género</th>' +
      '<th style="padding:9px 10px;">Ancho cm</th>' +
      '<th style="padding:9px 10px;">Alto cm</th>' +
      '<th style="padding:9px 10px;">Acción</th>' +
      '</tr></thead><tbody>';

    tallas.forEach(function(t){
      html += '<tr style="border-bottom:1px solid var(--border);">' +
        '<td style="padding:8px 10px;"><input type="text" value="'+esc(t.talla)+'" id="t-talla-'+t.id+'" style="width:60px;border:1px solid var(--border);padding:4px 6px;border-radius:4px;"></td>' +
        '<td style="padding:8px 10px;text-align:center;"><select id="t-gen-'+t.id+'" style="border:1px solid var(--border);padding:4px 6px;border-radius:4px;">' +
          '<option value="hombre"'+(t.genero==='hombre'?' selected':'')+'>Hombre</option>' +
          '<option value="mujer"'+(t.genero==='mujer'?' selected':'')+'>Mujer</option>' +
          '<option value="unisex"'+(t.genero==='unisex'?' selected':'')+'>Unisex</option>' +
        '</select></td>' +
        '<td style="padding:8px 10px;text-align:center;"><input type="number" value="'+t.ancho_cm+'" id="t-ancho-'+t.id+'" step="0.1" style="width:70px;border:1px solid var(--border);padding:4px 6px;border-radius:4px;"></td>' +
        '<td style="padding:8px 10px;text-align:center;"><input type="number" value="'+t.alto_cm+'"  id="t-alto-'+t.id+'"  step="0.1" style="width:70px;border:1px solid var(--border);padding:4px 6px;border-radius:4px;"></td>' +
        '<td style="padding:8px 10px;text-align:center;">' +
          '<button class="btn-accion btn-confirmar" onclick="guardarTalla('+t.id+','+pid+')" title="Guardar"><i class="fa-solid fa-floppy-disk"></i></button> ' +
          '<button class="btn-accion btn-eliminar"  onclick="eliminarTalla('+t.id+','+pid+')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>' +
        '</td>' +
      '</tr>';
    });

    /* fila nueva */
    html += '<tr style="background:var(--bg);">' +
      '<td style="padding:8px 10px;"><input type="text" id="t-new-talla" placeholder="XS" style="width:60px;border:1px solid var(--border);padding:4px 6px;border-radius:4px;"></td>' +
      '<td style="padding:8px 10px;text-align:center;"><select id="t-new-gen" style="border:1px solid var(--border);padding:4px 6px;border-radius:4px;"><option value="hombre">Hombre</option><option value="mujer">Mujer</option><option value="unisex">Unisex</option></select></td>' +
      '<td style="padding:8px 10px;text-align:center;"><input type="number" id="t-new-ancho" step="0.1" placeholder="0" style="width:70px;border:1px solid var(--border);padding:4px 6px;border-radius:4px;"></td>' +
      '<td style="padding:8px 10px;text-align:center;"><input type="number" id="t-new-alto"  step="0.1" placeholder="0" style="width:70px;border:1px solid var(--border);padding:4px 6px;border-radius:4px;"></td>' +
      '<td style="padding:8px 10px;text-align:center;">' +
        '<button class="btn-accion btn-confirmar" onclick="guardarTalla(0,'+pid+')" title="Agregar"><i class="fa-solid fa-plus"></i></button>' +
      '</td>' +
    '</tr>';

    html += '</tbody></table></div>';
    body.innerHTML = html;
    document.getElementById('modal-tallas-overlay').classList.add('open');
  });
};

window.guardarTalla = function(id, pid){
  var fd = new FormData();
  fd.append('accion',      'guardar_talla');
  fd.append('id',          id);
  fd.append('producto_id', pid);
  if(id > 0){
    fd.append('talla',    document.getElementById('t-talla-'+id).value);
    fd.append('genero',   document.getElementById('t-gen-'+id).value);
    fd.append('ancho_cm', document.getElementById('t-ancho-'+id).value);
    fd.append('alto_cm',  document.getElementById('t-alto-'+id).value);
  } else {
    fd.append('talla',    document.getElementById('t-new-talla').value);
    fd.append('genero',   document.getElementById('t-new-gen').value);
    fd.append('ancho_cm', document.getElementById('t-new-ancho').value);
    fd.append('alto_cm',  document.getElementById('t-new-alto').value);
  }
  fetch('api_productos.php', { method:'POST', body:fd })
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast('Talla guardada','ok'); verTallas(pid); }
    else toast(data.msg,'error');
  });
};

window.eliminarTalla = function(id, pid){
  if(!confirm('Eliminar esta talla?')) return;
  var fd = new FormData();
  fd.append('accion','eliminar_talla');
  fd.append('id', id);
  fetch('api_productos.php', { method:'POST', body:fd })
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast('Talla eliminada','ok'); verTallas(pid); }
  });
};

/* ── PEDIDOS ── */
function cargarPedidos(){
  fetch('api_productos.php?accion=listar_pedidos')
  .then(function(r){ return r.json(); })
  .then(function(data){
    todosPedidos = data.pedidos || [];
    pedidosFilt  = todosPedidos.slice();
    renderPedidos();
    renderResumenTallas();
    poblarFiltrosTalla();
  });
}

function filtrarPedidos(){
  var q    = document.getElementById('ped-buscar').value.toLowerCase();
  var prod = document.getElementById('ped-producto').value;
  var tal  = document.getElementById('ped-talla').value;
  var gen  = document.getElementById('ped-genero').value;

  pedidosFilt = todosPedidos.filter(function(p){
    var matchQ   = !q    || (p.nombre+' '+p.apellido+' '+p.carnet).toLowerCase().includes(q);
    var matchP   = !prod || p.producto_id == prod;
    var matchT   = !tal  || p.talla === tal;
    var matchG   = !gen  || p.genero === gen;
    return matchQ && matchP && matchT && matchG;
  });
  renderPedidos();
  renderResumenTallas();
}

function renderPedidos(){
  var tbody = document.getElementById('tbody-pedidos');
  var lbl   = document.getElementById('pedidos-total-lbl');
  if(!pedidosFilt.length){
    tbody.innerHTML = '<tr><td colspan="10" class="tabla-vacia">Sin pedidos</td></tr>';
    if(lbl) lbl.textContent = '0 registros';
    return;
  }
  if(lbl) lbl.textContent = pedidosFilt.length + ' registros';
  tbody.innerHTML = pedidosFilt.map(function(p,i){
    var badge = p.estado_pago === 'confirmado'
      ? '<span class="badge badge-confirmado"><i class="fa-solid fa-circle-check"></i> Confirmado</span>'
      : '<span class="badge badge-pendiente"><i class="fa-solid fa-clock"></i> Pendiente</span>';
    return '<tr>' +
      '<td>'+(i+1)+'</td>' +
      '<td><div class="participante-cell">' +
        '<div class="participante-avatar">'+(p.nombre[0]||'')+(p.apellido[0]||'')+'</div>' +
        '<div><div class="participante-nombre">'+esc(p.nombre)+' '+esc(p.apellido)+'</div></div>' +
      '</div></td>' +
      '<td>'+esc(p.carnet)+'</td>' +
      '<td>'+badge+'</td>' +
      '<td>'+esc(p.producto)+'</td>' +
      '<td>'+esc(p.talla||'—')+'</td>' +
      '<td>'+(p.genero==='mujer'
        ? '<i class="fa-solid fa-venus" style="color:#e879a0;"></i> Mujer'
        : p.genero==='unisex'
          ? '<i class="fa-solid fa-circle" style="color:#8b5cf6;"></i> Unisex'
          : '<i class="fa-solid fa-mars" style="color:#4f90d4;"></i> Hombre')+'</td>' +
      
      '<td>'+p.cantidad+'</td>' +
      '<td>Bs. '+parseFloat(p.subtotal).toFixed(2)+'</td>' +
      '<td>' +
        '<button class="btn-accion btn-ver" onclick="editarPedido('+p.pedido_id+','+p.cantidad+',\''+esc(p.talla||'')+'\','+p.producto_id+',\''+esc(p.genero||'hombre')+'\')" title="Editar">' +
          '<i class="fa-solid fa-pen"></i>' +
        '</button>' +
      '</td>' +

    '</tr>';
  }).join('');
}

function renderResumenTallas(){
  var wrap = document.getElementById('resumen-tallas-wrap');
  var cont = document.getElementById('resumen-tallas');
  if(!pedidosFilt.length){ wrap.style.display='none'; return; }
  wrap.style.display='block';

  var grupos = {};
  pedidosFilt.forEach(function(p){
    var k = p.producto + '|' + (p.genero||'hombre');
    if(!grupos[k]) grupos[k] = { nombre: p.producto, genero: p.genero, tallas:{}, total:0, subtotal:0 };
    var t = p.talla || 'Sin talla';
    grupos[k].tallas[t] = (grupos[k].tallas[t] || 0) + parseInt(p.cantidad || 0);
    grupos[k].total    += parseInt(p.cantidad || 0);
    grupos[k].subtotal += parseFloat(p.subtotal || 0);
  });

  cont.innerHTML = Object.values(grupos).map(function(g){
    var genIcon = g.genero === 'mujer'
      ? '<i class="fa-solid fa-venus" style="color:#e879a0;"></i> Mujer'
      : g.genero === 'unisex'
        ? '<i class="fa-solid fa-circle" style="color:#8b5cf6;"></i> Unisex'
        : '<i class="fa-solid fa-mars" style="color:#4f90d4;"></i> Hombre';
    var tallasHtml = Object.entries(g.tallas).map(function(e){
      return '<span class="talla-chip">'+esc(e[0])+' <strong>x'+e[1]+'</strong></span>';
    }).join('');
    return '<div class="resumen-grupo">' +
      '<div class="resumen-grupo-head">' +
        '<span>'+esc(g.nombre)+'</span>' +
        '<span>'+genIcon+'</span>' +
        '<span>Total: <strong>'+g.total+'</strong> uds.</span>' +
        '<span>Bs. <strong>'+g.subtotal.toFixed(2)+'</strong></span>' +
      '</div>' +
      '<div class="resumen-tallas-chips">'+tallasHtml+'</div>' +
    '</div>';
  }).join('');
}

function poblarFiltrosTalla(){
  var selP = document.getElementById('ped-producto');
  var selT = document.getElementById('ped-talla');
  todosProductos.forEach(function(p){
    var o = document.createElement('option');
    o.value = p.id; o.textContent = p.nombre;
    selP.appendChild(o);
  });
  var tallasSet = [];
  todosPedidos.forEach(function(p){ if(p.talla && tallasSet.indexOf(p.talla)===-1) tallasSet.push(p.talla); });
  tallasSet.sort().forEach(function(t){
    var o = document.createElement('option');
    o.value = t; o.textContent = t;
    selT.appendChild(o);
  });
}

function poblarSelectProducto(){
  var sel = document.getElementById('add-ped-producto');
  sel.innerHTML = '<option value="">-- Selecciona --</option>';
  todosProductos.forEach(function(p){
    var o = document.createElement('option');
    o.value = p.id; o.textContent = p.nombre + ' (Bs. ' + parseFloat(p.precio).toFixed(2) + ')';
    o.dataset.tipo = p.tipo || '';
    sel.appendChild(o);
  });
}

/* ── ELIMINAR PEDIDO ── */
window.eliminarPedido = function(id){
  if(!confirm('Eliminar este pedido?')) return;
  var fd = new FormData();
  fd.append('accion','eliminar_pedido');
  fd.append('id', id);
  fetch('api_productos.php', { method:'POST', body:fd })
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.ok){ toast('Pedido eliminado','ok'); cargarPedidos(); }
    else toast(data.msg,'error');
  });
};

/* ── AÑADIR PEDIDO A INSCRITO ── */
function initAddPedido(){
  var inputBuscar = document.getElementById('add-ped-buscar');
  var selProducto = document.getElementById('add-ped-producto');
  var selGenero   = document.getElementById('add-ped-genero');
  var selTalla    = document.getElementById('add-ped-talla');
  var btnGuardar  = document.getElementById('btn-guardar-pedido');
  var timer;

  if(inputBuscar){
    inputBuscar.addEventListener('input', function(){
      clearTimeout(timer);
      var q = this.value.trim();
      if(q.length < 2){ document.getElementById('add-ped-resultados').innerHTML=''; return; }
      timer = setTimeout(function(){
        fetch('api_productos.php?accion=buscar_inscritos&q='+encodeURIComponent(q))
        .then(function(r){ return r.json(); })
        .then(function(data){
          var res = document.getElementById('add-ped-resultados');
          if(!data.inscritos || !data.inscritos.length){
            res.innerHTML='<p style="color:var(--txt-xsoft);font-size:12px;">Sin resultados (solo inscritos confirmados)</p>';
            return;
          }
          res.innerHTML = data.inscritos.map(function(i){
            return '<div class="inscrito-result" onclick="seleccionarInscrito('+i.id+','+i.inscripcion_id+',\''+esc(i.nombre+' '+i.apellido)+'\')">' +
              '<i class="fa-solid fa-user"></i> '+esc(i.nombre)+' '+esc(i.apellido)+' — <small>'+esc(i.carnet)+'</small>' +
            '</div>';
          }).join('');
        });
      }, 350);
    });
  }

  if(selProducto){
    selProducto.addEventListener('change', function(){
      var pid  = this.value;
      var opt  = this.options[this.selectedIndex];
      var tipo = (opt.dataset.tipo || '').toLowerCase().trim();
      var generoWrap = document.getElementById('add-ped-genero-wrap');

      if(!pid){
        selTalla.innerHTML='<option value="">-- Selecciona producto primero --</option>';
        return;
      }

      /* gorras son unisex — ocultar selector género */
      if(tipo === 'gorra'){
        if(generoWrap) generoWrap.style.display = 'none';
        cargarTallasSelect(pid, 'unisex');
      } else {
        if(generoWrap) generoWrap.style.display = 'block';
        cargarTallasSelect(pid, selGenero ? selGenero.value : 'hombre');
      }
    });
  }

  if(selGenero){
    selGenero.addEventListener('change', function(){
      var pid = selProducto ? selProducto.value : '';
      if(pid) cargarTallasSelect(pid, this.value);
    });
  }

  if(btnGuardar){
    btnGuardar.addEventListener('click', function(){
      var opt  = selProducto ? selProducto.options[selProducto.selectedIndex] : null;
      var tipo = opt ? (opt.dataset.tipo || '').toLowerCase().trim() : '';
      var generoEnviar = tipo === 'gorra' ? 'unisex' : (selGenero ? selGenero.value : 'hombre');

      var fd = new FormData();
      fd.append('accion',         'añadir_pedido');
      fd.append('inscripcion_id', document.getElementById('add-ped-inscrito-id').value);
      fd.append('producto_id',    selProducto.value);
      fd.append('talla',          selTalla.value);
      fd.append('genero',         generoEnviar);
      fd.append('cantidad',       document.getElementById('add-ped-cantidad').value);
     
      fetch('api_productos.php', { method:'POST', body:fd })
      .then(function(r){ return r.json(); })
      .then(function(data){
        if(data.ok){
          toast('Pedido anadido correctamente','ok');
          document.getElementById('modal-add-ped-overlay').classList.remove('open');
          cargarPedidos();
          cargarProductos();
        } else {
          toast(data.msg,'error');
        }
      });
    });
  }
}

function cargarTallasSelect(pid, genero){
  var sel = document.getElementById('add-ped-talla');
  fetch('api_productos.php?accion=listar_tallas&producto_id='+pid)
  .then(function(r){ return r.json(); })
  .then(function(data){
    /* filtrar por género pedido, o unisex como fallback */
    var tallas = (data.tallas||[]).filter(function(t){
      return t.genero === genero || t.genero === 'unisex';
    });
    if(!tallas.length){
      var prod = todosProductos.find(function(p){ return p.id == pid; });
      var tipo = prod ? prod.tipo.toLowerCase().trim() : '';
      var opts = tipo === 'gorra'
        ? ['Pequeño','Mediano','Grande','Extra Grande']
        : ['XS','S','M','L','XL','XXL','XXXL'];
      sel.innerHTML = opts.map(function(t){
        return '<option value="'+t+'">'+t+'</option>';
      }).join('');
    } else {
      sel.innerHTML = tallas.map(function(t){
        var extra = (t.ancho_cm && t.alto_cm) ? ' ('+t.ancho_cm+'x'+t.alto_cm+' cm)' : '';
        return '<option value="'+esc(t.talla)+'">'+esc(t.talla)+extra+'</option>';
      }).join('');
    }
  });
}

window.seleccionarInscrito = function(id, inscripcionId, nombre){
  document.getElementById('add-ped-inscrito-id').value         = inscripcionId;
  document.getElementById('add-ped-inscrito-nombre').textContent = nombre;
  document.getElementById('add-ped-resultados').innerHTML       = '';
  document.getElementById('add-ped-buscar').value               = nombre;
  document.getElementById('add-ped-form').style.display         = 'block';
  document.getElementById('btn-guardar-pedido').style.display   = 'inline-flex';
};

/* ── CREAR PRODUCTO ── */
function initCrearProducto(){
  var btn = document.getElementById('btn-crear-producto');
  if(!btn) return;
  btn.addEventListener('click', function(){
    var fd = new FormData();
    fd.append('accion',  'crear_producto');
    fd.append('nombre',  document.getElementById('new-prod-nombre').value);
    fd.append('precio',  document.getElementById('new-prod-precio').value);
    fd.append('tipo',    document.getElementById('new-prod-tipo').value);
    fd.append('cupos',   document.getElementById('new-prod-cupos').value);
    fetch('api_productos.php', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if(data.ok){
        toast('Producto creado','ok');
        document.getElementById('modal-add-prod-overlay').classList.remove('open');
        cargarProductos();
      } else toast(data.msg,'error');
    });
  });
}

/* ── EXPORTAR PDF ── */
function exportarPDF(){
  document.getElementById('modal-pdf-overlay').classList.add('open');
}

function cargarJsPDF(callback){
  if(window.jspdf && window.jspdf.jsPDF){
    callback();
    return;
  }
  var s1 = document.createElement('script');
  s1.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
  s1.onerror = function(){ toast('Error al cargar libreria PDF','error'); };
  s1.onload = function(){
    var intentos = 0;
    var esperar = setInterval(function(){
      intentos++;
      if(window.jspdf && window.jspdf.jsPDF){
        clearInterval(esperar);
        var s2 = document.createElement('script');
        s2.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js';
        s2.onerror = function(){ toast('Error al cargar autotable','error'); };
        s2.onload  = function(){ setTimeout(callback, 100); };
        document.head.appendChild(s2);
      }
      if(intentos > 20){ clearInterval(esperar); toast('Error al cargar jsPDF','error'); }
    }, 100);
  };
  document.head.appendChild(s1);
}

window.generarPDF = function(tipo){
  if(!pedidosFilt.length){
    toast('No hay datos para exportar','warn');
    document.getElementById('modal-pdf-overlay').classList.remove('open');
    return;
  }

  toast('Generando PDF...','');

  cargarJsPDF(function(){
    try {
      var jsPDF = window.jspdf.jsPDF;
      var doc   = new jsPDF({ orientation:'landscape', unit:'mm', format:'a4' });
      var fecha = new Date().toLocaleString('es-BO');
      var pie   = 'Creado por Lima Technology  |  ' + fecha;

      function addHeader(titulo){
        doc.setFillColor(3,4,94);
        doc.rect(0,0,297,18,'F');
        doc.setTextColor(255,255,255);
        doc.setFontSize(13);
        doc.setFont('helvetica','bold');
        doc.text('Encuentro Departamental de Jovenes', 10, 8);
        doc.setFontSize(10);
        doc.setFont('helvetica','normal');
        doc.text(titulo, 10, 15);
        doc.setTextColor(40,40,40);
      }

      function addFooter(){
        doc.setFontSize(8);
        doc.setTextColor(150,150,150);
        doc.text(pie, 10, doc.internal.pageSize.height - 5);
      }

      function addTablaPedidos(datos, titulo){
        addHeader(titulo);
        var cols = ['#','Inscrito','Carnet','Estado','Producto','Talla','Genero','Cant.','Subtotal Bs.'];
        var rows = datos.map(function(p,i){
          return [
            i+1,
            p.nombre+' '+p.apellido,
            p.carnet,
            p.estado_pago,
            p.producto,
            p.talla||'--',
            p.genero==='mujer' ? 'Mujer' : p.genero==='unisex' ? 'Unisex' : 'Hombre',
            p.cantidad,
            parseFloat(p.subtotal).toFixed(2)
          ];
        });
        var totalUds = datos.reduce(function(a,p){ return a+parseInt(p.cantidad||0); },0);
        var totalBs  = datos.reduce(function(a,p){ return a+parseFloat(p.subtotal||0); },0)

        doc.autoTable({
          head:[cols],
          body:rows,
          foot:[['','','','','','','TOTAL',totalUds,'Bs. '+totalBs.toFixed(2)]],
          startY:22,
          styles:{ fontSize:8, cellPadding:2 },
          headStyles:{ fillColor:[3,4,94], textColor:255, fontStyle:'bold' },
          footStyles:{ fillColor:[218,0,43], textColor:255, fontStyle:'bold' },
          alternateRowStyles:{ fillColor:[240,242,248] },
          margin:{ left:10, right:10 }
        });
        addFooter();
      }

      /* ── TODOS LOS PEDIDOS ── */
      if(tipo === 'todos'){
        addTablaPedidos(pedidosFilt, 'Todos los Pedidos de Productos');
        doc.save('productos-todos.pdf');

      /* ── POR TALLA ── */
      } else if(tipo === 'tallas'){
        var gruposTalla = {};
        pedidosFilt.forEach(function(p){
          var t = p.talla||'Sin talla';
          if(!gruposTalla[t]) gruposTalla[t]=[];
          gruposTalla[t].push(p);
        });
        var primera = true;
        Object.keys(gruposTalla).sort().forEach(function(talla){
          if(!primera) doc.addPage();
          primera = false;
          addTablaPedidos(gruposTalla[talla], 'Pedidos -- Talla: '+talla);
        });
        doc.save('productos-por-talla.pdf');

      /* ── POR GÉNERO ── */
      } else if(tipo === 'genero'){
        var hombres = pedidosFilt.filter(function(p){ return p.genero === 'hombre'; });
        var mujeres  = pedidosFilt.filter(function(p){ return p.genero === 'mujer'; });
        var unisex   = pedidosFilt.filter(function(p){ return p.genero === 'unisex'; });

        var primera = true;
        if(hombres.length){
          addTablaPedidos(hombres, 'Pedidos -- Genero: Hombre');
          primera = false;
        }
        if(mujeres.length){
          if(!primera) doc.addPage();
          addTablaPedidos(mujeres, 'Pedidos -- Genero: Mujer');
          primera = false;
        }
        if(unisex.length){
          if(!primera) doc.addPage();
          addTablaPedidos(unisex, 'Pedidos -- Genero: Unisex');
        }
        doc.save('productos-por-genero.pdf');

      /* ── RESUMEN FINANCIERO ── */
      } else if(tipo === 'dinero'){
        addHeader('Resumen Financiero -- Productos');

        var porProd = {};
        pedidosFilt.forEach(function(p){
          if(!porProd[p.producto]) porProd[p.producto]={cant:0,total:0};
          porProd[p.producto].cant  += parseInt(p.cantidad||0);
          porProd[p.producto].total += parseFloat(p.subtotal||0);
        });
        var grandTotal = pedidosFilt.reduce(function(a,p){ return a+parseFloat(p.subtotal||0); },0);
        var grandCant  = pedidosFilt.reduce(function(a,p){ return a+parseInt(p.cantidad||0); },0);

        doc.autoTable({
          head:[['Producto','Unidades','Total recaudado']],
          body:Object.entries(porProd).map(function(e){
            return [e[0], e[1].cant, 'Bs. '+e[1].total.toFixed(2)];
          }),
          foot:[['TOTAL GENERAL', grandCant, 'Bs. '+grandTotal.toFixed(2)]],
          startY:22, styles:{fontSize:9,cellPadding:3},
          headStyles:{fillColor:[3,4,94],textColor:255,fontStyle:'bold'},
          footStyles:{fillColor:[16,185,129],textColor:255,fontStyle:'bold'},
          alternateRowStyles:{fillColor:[240,242,248]},
          margin:{left:10,right:10}
        });

        var y2 = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(11); doc.setFont('helvetica','bold'); doc.setTextColor(3,4,94);
        doc.text('Recaudado por Talla', 10, y2);

        var porTalla = {};
        pedidosFilt.forEach(function(p){
          var t = p.talla||'Sin talla';
          if(!porTalla[t]) porTalla[t]={cant:0,total:0};
          porTalla[t].cant  += parseInt(p.cantidad||0);
          porTalla[t].total += parseFloat(p.subtotal||0);
        });
        doc.autoTable({
          head:[['Talla','Unidades','Total recaudado']],
          body:Object.entries(porTalla).sort().map(function(e){
            return [e[0], e[1].cant, 'Bs. '+e[1].total.toFixed(2)];
          }),
          startY:y2+4, styles:{fontSize:9,cellPadding:3},
          headStyles:{fillColor:[0,137,228],textColor:255,fontStyle:'bold'},
          alternateRowStyles:{fillColor:[240,242,248]},
          margin:{left:10,right:10}
        });

        var y3 = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(11); doc.setFont('helvetica','bold'); doc.setTextColor(3,4,94);
        doc.text('Recaudado por Genero', 10, y3);

        var porGen = {hombre:{cant:0,total:0}, mujer:{cant:0,total:0}, unisex:{cant:0,total:0}};
        pedidosFilt.forEach(function(p){
          var g = p.genero==='mujer' ? 'mujer' : p.genero==='unisex' ? 'unisex' : 'hombre';
          porGen[g].cant  += parseInt(p.cantidad || 0);
          porGen[g].total += parseFloat(p.subtotal || 0);
        });
        doc.autoTable({
          head:[['Genero','Unidades','Total recaudado']],
          body:[
            ['Hombre', porGen.hombre.cant, 'Bs. '+porGen.hombre.total.toFixed(2)],
            ['Mujer',  porGen.mujer.cant,  'Bs. '+porGen.mujer.total.toFixed(2)],
            ['Unisex', porGen.unisex.cant, 'Bs. '+porGen.unisex.total.toFixed(2)]
          ],
          foot:[['TOTAL', grandCant, 'Bs. '+grandTotal.toFixed(2)]],
          startY:y3+4, styles:{fontSize:9,cellPadding:3},
          headStyles:{fillColor:[139,92,246],textColor:255,fontStyle:'bold'},
          footStyles:{fillColor:[16,185,129],textColor:255,fontStyle:'bold'},
          alternateRowStyles:{fillColor:[240,242,248]},
          margin:{left:10,right:10}
        });

        addFooter();
        doc.save('productos-resumen-financiero.pdf');
      }

      document.getElementById('modal-pdf-overlay').classList.remove('open');
      toast('PDF descargado','ok');

    } catch(err){
      console.error('Error PDF:', err);
      toast('Error al generar PDF: ' + err.message, 'error');
    }
  });
};
window.editarPedido = function(id, cantidad, talla, productoId, genero){
  document.getElementById('edit-ped-id').value       = id;
  document.getElementById('edit-ped-cantidad').value = cantidad;

  /* cargar tallas del producto en el select */
  var selTalla = document.getElementById('edit-ped-talla');
  selTalla.innerHTML = '<option value="">Cargando...</option>';

  fetch('api_productos.php?accion=listar_tallas&producto_id='+productoId)
  .then(function(r){ return r.json(); })
  .then(function(data){
    var generoFiltro = genero === 'unisex' ? 'unisex' : genero;
    var tallas = (data.tallas||[]).filter(function(t){
      return t.genero === generoFiltro || t.genero === 'unisex';
    });

    if(!tallas.length){
      /* fallback predeterminado */
      var prod = todosProductos.find(function(p){ return p.id == productoId; });
      var tipo = prod ? (prod.tipo||'').toLowerCase().trim() : '';
      var opts = tipo === 'gorra'
        ? ['Pequeño','Mediano','Grande','Extra Grande']
        : ['XS','S','M','L','XL','XXL','XXXL'];
      selTalla.innerHTML = opts.map(function(t){
        return '<option value="'+t+'"'+(t===talla?' selected':'')+'>'+t+'</option>';
      }).join('');
    } else {
      selTalla.innerHTML = tallas.map(function(t){
        return '<option value="'+esc(t.talla)+'"'+(t.talla===talla?' selected':'')+'>'+esc(t.talla)+'</option>';
      }).join('');
    }
  });

  document.getElementById('modal-editar-ped-overlay').classList.add('open');
};

document.addEventListener('DOMContentLoaded', function(){
  var btnGuardar = document.getElementById('btn-guardar-editar-ped');
  if(btnGuardar){
    btnGuardar.addEventListener('click', function(){
      var id       = document.getElementById('edit-ped-id').value;
      var cantidad = parseInt(document.getElementById('edit-ped-cantidad').value);
      var talla    = document.getElementById('edit-ped-talla').value;

      if(!id || isNaN(cantidad) || cantidad < 1){
        toast('Cantidad invalida','error'); return;
      }

      var fd = new FormData();
      fd.append('accion',   'editar_pedido');
      fd.append('id',       id);
      fd.append('cantidad', cantidad);
      fd.append('talla',    talla);

      fetch('api_productos.php', { method:'POST', body:fd })
      .then(function(r){ return r.json(); })
      .then(function(data){
        if(data.ok){
          toast('Pedido actualizado','ok');
          document.getElementById('modal-editar-ped-overlay').classList.remove('open');
          cargarPedidos();
          cargarProductos();
        } else {
          toast(data.msg||'Error','error');
        }
      })
      .catch(function(){ toast('Error de conexion','error'); });
    });
  }
});
/* ── HELPERS ── */
function esc(s){
  return String(s||'')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function mf(label, input){
  return '<div class="modal-field"><span class="modal-label">'+label+'</span>'+input+'</div>';
}
function toast(msg, tipo){
  var el = document.getElementById('toast-prod');
  if(!el) return;
  el.textContent = msg;
  el.className   = 'toast show' + (tipo ? ' toast-'+tipo : '');
  setTimeout(function(){ el.classList.remove('show'); }, 3200);
}

/* ── CERRAR MODALES AL CLICK FUERA ── */
['modal-prod-overlay','modal-tallas-overlay','modal-add-ped-overlay',
 'modal-add-prod-overlay','modal-pdf-overlay','modal-editar-ped-overlay'].forEach(function(id){
  var el = document.getElementById(id);
  if(el) el.addEventListener('click', function(e){
    if(e.target === this) this.classList.remove('open');
  });
});

/* ── INIT ── */
document.addEventListener('DOMContentLoaded', init);

})();