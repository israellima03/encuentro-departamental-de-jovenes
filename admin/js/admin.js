
(function () {
  'use strict';

  /* ══════════════════════════════════════
     NAVEGACIÓN SPA
  ══════════════════════════════════════ */
  var paginaActual = 'dashboard';

  function irAPagina(page) {
    // ocultar todas
    document.querySelectorAll('.page').forEach(function (p) {
      p.classList.remove('active');
    });

    // mostrar la pedida
    var target = document.getElementById('page-' + page);
    if (target) target.classList.add('active');

    // marcar nav-item activo
    document.querySelectorAll('.nav-item').forEach(function (el) {
      el.classList.remove('active');
    });
    document.querySelectorAll('.nav-subitem').forEach(function (el) {
      el.classList.remove('active');
    });

    var navEl = document.querySelector('[data-page="' + page + '"]');
    if (navEl) navEl.classList.add('active');

    paginaActual = page;

    // en móvil cerrar sidebar
    if (window.innerWidth < 768) {
      document.body.classList.add('sidebar-collapsed');
    }
  }

  // clicks en nav-items normales
  document.querySelectorAll('.nav-item[data-page]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      irAPagina(this.dataset.page);
    });
  });

  // clicks en nav-subitems
  document.querySelectorAll('.nav-subitem[data-page]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      irAPagina(this.dataset.page);
    });
  });

  // submenú programa
  var togglePrograma = document.getElementById('nav-programa-toggle');
  var submenuPrograma = document.getElementById('submenu-programa');
  if (togglePrograma && submenuPrograma) {
    togglePrograma.addEventListener('click', function () {
      this.classList.toggle('open');
      submenuPrograma.classList.toggle('open');
    });
  }
  // submenú administradores
  var toggleAdmin = document.getElementById('nav-administradores-toggle');
  var submenuAdmin = document.getElementById('submenu-administradores');

  if (toggleAdmin && submenuAdmin) {
    toggleAdmin.addEventListener('click', function () {
      this.classList.toggle('open');
      submenuAdmin.classList.toggle('open');
    });
  }

  /* ══════════════════════════════════════
     TOGGLE SIDEBAR
  ══════════════════════════════════════ */
  var btnToggle = document.getElementById('btn-toggle-sidebar');
  if (btnToggle) {
    btnToggle.addEventListener('click', function () {
      document.body.classList.toggle('sidebar-collapsed');
    });
  }

  /* ══════════════════════════════════════
     CERRAR SESIÓN
  ══════════════════════════════════════ */
  var btnCerrar = document.querySelector('.btn-cerrar-sesion');
  if (btnCerrar) {
    btnCerrar.addEventListener('click', function (e) {
      e.preventDefault();
      if (confirm('¿Deseas cerrar sesión?')) {
        window.location.href = 'logout.php'; // ajusta la ruta si es necesario
      }
    });
  }

  /* ══════════════════════════════════════
     STATS — DATOS DEMO (luego se conecta a la BD)
  ══════════════════════════════════════ */
  function cargarStatsDashboard() {
    // Simulación — reemplazar con fetch a admin_api.php cuando esté lista
    var demo = {
      total:       0,
      pendientes:  0,
      confirmados: 0,
      cupos_disp:  0,
      cupos_total: 100,
      recauda_conf: 0,
      recauda_pend: 0,
    };

    animarNumero('stat-total',       demo.total);
    animarNumero('stat-pendientes',  demo.pendientes);
    animarNumero('stat-confirmados', demo.confirmados);
    animarNumero('stat-cupos',       demo.cupos_disp);

    var pct = demo.cupos_total > 0
      ? Math.round(((demo.cupos_total - demo.cupos_disp) / demo.cupos_total) * 100)
      : 0;
    var barEl = document.getElementById('stat-cupos-bar');
    var pctEl = document.getElementById('stat-cupos-pct');
    if (barEl) setTimeout(function () { barEl.style.width = pct + '%'; }, 200);
    if (pctEl) pctEl.textContent = pct + '% ocupado';

    setText('stat-hoy',    '+0 hoy');
    setText('stat-conf-sub', demo.confirmados + ' pagos verificados');

    setText('recauda-total',      'Bs. ' + demo.recauda_conf.toFixed(2));
    setText('recauda-pendiente',  'Bs. ' + demo.recauda_pend.toFixed(2));
    setText('recauda-confirmado', 'Bs. ' + demo.recauda_conf.toFixed(2));

    // badge QR
    setText('badge-qr', demo.pendientes);
    if (demo.pendientes > 0) {
      var dot = document.getElementById('notif-dot');
      if (dot) dot.classList.add('visible');
    }

    // widget paquetes demo
    renderWidgetPaquetes([]);
  }

  function animarNumero(id, objetivo) {
    var el = document.getElementById(id);
    if (!el) return;
    var inicio = 0;
    var duracion = 800;
    var inicio_ts = null;
    function step(ts) {
      if (!inicio_ts) inicio_ts = ts;
      var progreso = Math.min((ts - inicio_ts) / duracion, 1);
      el.textContent = Math.round(progreso * objetivo);
      if (progreso < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  function renderWidgetPaquetes(paquetes) {
    var el = document.getElementById('widget-paquetes');
    if (!el) return;
    if (!paquetes || paquetes.length === 0) {
      el.innerHTML = '<p style="color:var(--txt-xsoft);font-size:12px;text-align:center;padding:12px 0;">Sin datos aún</p>';
      return;
    }
    var max = Math.max.apply(null, paquetes.map(function (p) { return p.total; })) || 1;
    el.innerHTML = paquetes.map(function (p) {
      var pct = Math.round((p.total / max) * 100);
      return '<div class="paq-row">' +
        '<div class="paq-label"><strong>' + p.nombre + '</strong><span>' + p.total + '</span></div>' +
        '<div class="paq-bar-bg"><div class="paq-bar-fill" style="width:' + pct + '%"></div></div>' +
        '</div>';
    }).join('');
  }

  /* ══════════════════════════════════════
     TABLA INSCRITOS — DATOS DEMO
  ══════════════════════════════════════ */
  var todosInscritos = [];   // se llenará con fetch
  var inscritosFiltrados = [];
  var paginaTabla = 1;
  var porPagina   = 10;

  function cargarTablaInscritos() {
    var tbody = document.getElementById('tbody-inscritos');
    if (!tbody) return;

    // Demo: tabla vacía con mensaje amigable
    tbody.innerHTML =
      '<tr><td colspan="11" class="tabla-vacia">' +
      '<i class="fa-solid fa-inbox" style="font-size:28px;display:block;margin-bottom:10px;color:var(--border);"></i>' +
      'Conecta la base de datos para ver los inscritos' +
      '</td></tr>';

    // Poblar filtros demo
    poblarFiltro('filtro-iglesia',  [], 'Todas las iglesias');
    poblarFiltro('filtro-distrito', [], 'Todos los distritos');
  }

  function poblarFiltro(selectId, items, placeholder) {
    var sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = '<option value="">' + placeholder + '</option>';
    items.forEach(function (item) {
      var opt = document.createElement('option');
      opt.value = item.id;
      opt.textContent = item.nombre;
      sel.appendChild(opt);
    });
  }

  function renderTabla(datos) {
    var tbody = document.getElementById('tbody-inscritos');
    if (!tbody) return;

    if (!datos || datos.length === 0) {
      tbody.innerHTML = '<tr><td colspan="11" class="tabla-vacia">No se encontraron inscritos con esos filtros.</td></tr>';
      renderPaginacion(0, 0);
      return;
    }

    var inicio = (paginaTabla - 1) * porPagina;
    var fin    = inicio + porPagina;
    var pagina = datos.slice(inicio, fin);

    tbody.innerHTML = pagina.map(function (ins, i) {
      var iniciales = (ins.nombre ? ins.nombre[0] : '') + (ins.apellido ? ins.apellido[0] : '');
      var estadoBadge = ins.estado_pago === 'confirmado'
        ? '<span class="badge badge-confirmado"><i class="fa-solid fa-circle-check"></i> Confirmado</span>'
        : '<span class="badge badge-pendiente"><i class="fa-solid fa-clock"></i> Pendiente</span>';

      return '<tr>' +
        '<td>' + (inicio + i + 1) + '</td>' +
        '<td><div class="participante-cell">' +
          '<div class="participante-avatar">' + iniciales.toUpperCase() + '</div>' +
          '<div><div class="participante-nombre">' + ins.nombre + ' ' + ins.apellido + '</div>' +
          '<div class="participante-tipo">' + (ins.tipo || '') + '</div></div>' +
        '</div></td>' +
        '<td>' + ins.carnet + '</td>' +
        '<td>' + ins.celular + '</td>' +
        '<td>' + (ins.iglesia || '—') + '</td>' +
        '<td>' + (ins.distrito || '—') + '</td>' +
        '<td>' + (ins.paquete || '—') + '</td>' +
        '<td><strong>Bs. ' + parseFloat(ins.precio_final || 0).toFixed(2) + '</strong></td>' +
        '<td>' + estadoBadge + '</td>' +
        '<td>' + (ins.fecha || '—') + '</td>' +
        '<td>' +
          '<button class="btn-accion btn-ver"     title="Ver detalle"  onclick="verInscrito(' + ins.id + ')"><i class="fa-solid fa-eye"></i></button> ' +
          (ins.estado_pago === 'pendiente'
            ? '<button class="btn-accion btn-confirmar" title="Confirmar pago" onclick="confirmarPago(' + ins.id + ')"><i class="fa-solid fa-check"></i></button> '
            : '') +
          '<button class="btn-accion btn-eliminar"   title="Eliminar"     onclick="eliminarInscrito(' + ins.id + ')"><i class="fa-solid fa-trash"></i></button>' +
        '</td>' +
      '</tr>';
    }).join('');

    renderPaginacion(datos.length, pagina.length);
  }

  function renderPaginacion(total, enPagina) {
    var el = document.getElementById('paginacion');
    if (!el) return;
    var totalPags = Math.ceil(total / porPagina);
    if (totalPags <= 1) { el.innerHTML = ''; return; }

    var html = '';
    html += '<button class="pag-btn" onclick="cambiarPagina(' + (paginaTabla - 1) + ')" ' + (paginaTabla <= 1 ? 'disabled' : '') + '><i class="fa-solid fa-chevron-left"></i></button>';
    for (var i = 1; i <= totalPags; i++) {
      html += '<button class="pag-btn ' + (i === paginaTabla ? 'active' : '') + '" onclick="cambiarPagina(' + i + ')">' + i + '</button>';
    }
    html += '<button class="pag-btn" onclick="cambiarPagina(' + (paginaTabla + 1) + ')" ' + (paginaTabla >= totalPags ? 'disabled' : '') + '><i class="fa-solid fa-chevron-right"></i></button>';
    el.innerHTML = html;
  }

  window.cambiarPagina = function (pag) {
    var totalPags = Math.ceil(inscritosFiltrados.length / porPagina);
    if (pag < 1 || pag > totalPags) return;
    paginaTabla = pag;
    renderTabla(inscritosFiltrados);
  };

  /* ── filtros ── */
  function aplicarFiltros() {
    var nombre   = (document.getElementById('filtro-nombre')   || {}).value || '';
    var iglesia  = (document.getElementById('filtro-iglesia')  || {}).value || '';
    var distrito = (document.getElementById('filtro-distrito') || {}).value || '';
    var estado   = (document.getElementById('filtro-estado')   || {}).value || '';

    inscritosFiltrados = todosInscritos.filter(function (ins) {
      var matchNombre  = !nombre   || (ins.nombre + ' ' + ins.apellido + ' ' + ins.carnet).toLowerCase().includes(nombre.toLowerCase());
      var matchIgl     = !iglesia  || ins.iglesia_id == iglesia;
      var matchDist    = !distrito || ins.distrito_id == distrito;
      var matchEstado  = !estado   || ins.estado_pago === estado;
      return matchNombre && matchIgl && matchDist && matchEstado;
    });

    paginaTabla = 1;
    renderTabla(inscritosFiltrados);
  }

  ['filtro-nombre','filtro-iglesia','filtro-distrito','filtro-estado'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) {
      el.addEventListener('input',  aplicarFiltros);
      el.addEventListener('change', aplicarFiltros);
    }
  });

  /* ══════════════════════════════════════
     MODAL VER INSCRITO
  ══════════════════════════════════════ */
  window.verInscrito = function (id) {
    var ins = todosInscritos.find(function (i) { return i.id == id; });
    if (!ins) { mostrarToast('No se encontró el inscrito', 'error'); return; }

    var body = document.getElementById('modal-body');
    if (!body) return;

    body.innerHTML =
      '<div class="modal-grid">' +
        '<div class="modal-section-title">Datos Personales</div>' +
        campo('Nombre completo', ins.nombre + ' ' + ins.apellido) +
        campo('Carnet', ins.carnet) +
        campo('Fecha de nacimiento', ins.fecha_nacimiento || '—') +
        campo('Edad', ins.edad ? ins.edad + ' años' : '—') +
        campo('Celular', ins.celular) +
        campo('Tipo', ins.tipo || '—') +
        '<div class="modal-section-title">Datos de Iglesia</div>' +
        campo('Ministerio', ins.ministerio || '—') +
        campo('Iglesia', ins.iglesia || '—') +
        campo('Distrito', ins.distrito || '—') +
        '<div class="modal-section-title">Inscripción</div>' +
        campo('Paquete', ins.paquete || '—') +
        campo('Precio paquete', 'Bs. ' + parseFloat(ins.precio_paquete || 0).toFixed(2)) +
        campo('Productos', 'Bs. ' + parseFloat(ins.precio_productos || 0).toFixed(2)) +
        campo('Descuento', '- Bs. ' + parseFloat(ins.descuento || 0).toFixed(2)) +
        campo('TOTAL', 'Bs. ' + parseFloat(ins.precio_final || 0).toFixed(2), true) +
        campo('Estado', ins.estado_pago === 'confirmado'
          ? '<span class="badge badge-confirmado"><i class="fa-solid fa-circle-check"></i> Confirmado</span>'
          : '<span class="badge badge-pendiente"><i class="fa-solid fa-clock"></i> Pendiente</span>', false, true) +
        campo('Fecha registro', ins.fecha || '—') +
        (ins.comprobante_qr ? '<div class="comprobante-preview"><div class="modal-label">Comprobante</div><img src="comprobantes/' + ins.comprobante_qr + '" alt="Comprobante"></div>' : '') +
      '</div>';

    var btnConf = document.getElementById('btn-modal-confirmar');
    if (btnConf) {
      btnConf.style.display = ins.estado_pago === 'pendiente' ? 'inline-flex' : 'none';
      btnConf.onclick = function () { confirmarPago(id); cerrarModal(); };
    }

    abrirModal();
  };

  function campo(label, val, grande, html) {
    return '<div class="modal-field' + (grande ? ' full' : '') + '">' +
      '<span class="modal-label">' + label + '</span>' +
      (html ? '<span class="modal-value">' + val + '</span>'
            : '<span class="modal-value">' + val + '</span>') +
    '</div>';
  }

  function abrirModal() {
    var ov = document.getElementById('modal-overlay');
    if (ov) ov.classList.add('open');
  }
  function cerrarModal() {
    var ov = document.getElementById('modal-overlay');
    if (ov) ov.classList.remove('open');
  }

  var btnModalClose = document.getElementById('btn-modal-close');
  var btnModalCancel = document.getElementById('btn-modal-cancelar');
  if (btnModalClose)  btnModalClose.addEventListener('click', cerrarModal);
  if (btnModalCancel) btnModalCancel.addEventListener('click', cerrarModal);

  document.getElementById('modal-overlay').addEventListener('click', function (e) {
    if (e.target === this) cerrarModal();
  });

  /* ══════════════════════════════════════
     ACCIONES TABLA (placeholders)
  ══════════════════════════════════════ */
  window.confirmarPago = function (id) {
    mostrarToast('Conecta la BD para confirmar pagos', 'warn');
  };

  window.eliminarInscrito = function (id) {
    mostrarToast('Conecta la BD para eliminar inscritos', 'warn');
  };

  /* ══════════════════════════════════════
     HERRAMIENTAS EXPORTAR
  ══════════════════════════════════════ */
  var btnPDF = document.getElementById('btn-exportar-pdf');
  var btnCSV = document.getElementById('btn-exportar-csv');
  var btnPrint = document.getElementById('btn-imprimir');

  if (btnPDF)   btnPDF.addEventListener('click',   function () { mostrarToast('Exportar PDF — próximamente', 'warn'); });
  if (btnCSV)   btnCSV.addEventListener('click',   exportarCSV);
  if (btnPrint) btnPrint.addEventListener('click', function () { window.print(); });

  function exportarCSV() {
    if (!inscritosFiltrados.length) { mostrarToast('No hay datos para exportar', 'warn'); return; }
    var cab = ['#','Nombre','Apellido','Carnet','Celular','Iglesia','Distrito','Paquete','Total','Estado','Fecha'];
    var filas = inscritosFiltrados.map(function (ins, i) {
      return [i+1, ins.nombre, ins.apellido, ins.carnet, ins.celular,
        ins.iglesia||'', ins.distrito||'', ins.paquete||'',
        parseFloat(ins.precio_final||0).toFixed(2), ins.estado_pago, ins.fecha||''].join(',');
    });
    var csv = [cab.join(',')].concat(filas).join('\n');
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href = url; a.download = 'inscritos.csv'; a.click();
    URL.revokeObjectURL(url);
    mostrarToast('CSV descargado', 'ok');
  }

  /* ══════════════════════════════════════
     TOAST
  ══════════════════════════════════════ */
  var toastTimer = null;
  function mostrarToast(msg, tipo) {
    var el = document.getElementById('toast');
    if (!el) return;
    el.textContent = msg;
    el.className = 'toast show' + (tipo ? ' toast-' + tipo : '');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () {
      el.classList.remove('show');
    }, 3200);
  }

  /* ══════════════════════════════════════
     IR A INSCRIBIR (nueva inscripción)
  ══════════════════════════════════════ */
  window.irAInscribir = function () {
    window.open('../registro.php', '_blank');
  };

  /* ══════════════════════════════════════
     HELPERS
  ══════════════════════════════════════ */
  function setText(id, val) {
    var el = document.getElementById(id);
    if (el) el.textContent = val;
  }

  /* ══════════════════════════════════════
     INIT
  ══════════════════════════════════════ */
  document.addEventListener('DOMContentLoaded', function () {
    cargarStatsDashboard();
    cargarTablaInscritos();

    // responsive: colapsar sidebar en móvil por defecto
    if (window.innerWidth < 768) {
      document.body.classList.add('sidebar-collapsed');
    }
  });

})();