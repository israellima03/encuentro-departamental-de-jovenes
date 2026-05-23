console.log("JS FUNCIONANDO");

(function () {
    "use strict";

    function domReady(fn){
      if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', fn);
      } else {
        fn();
      }
    }

    var barra = document.querySelector('.barra');
    if(barra){
      var barraOffset = barra.offsetTop;
      window.addEventListener('scroll', function(){
        if(window.scrollY >= barraOffset){
          barra.classList.add('sticky');
        } else {
          barra.classList.remove('sticky');
        }
      });
    }

    domReady(function(){

      /* HAMBURGUESA */
      var menuMovil  = document.querySelector('.menu-movil');
      var navegacion = document.querySelector('.navegacion-principal');

      if(menuMovil && navegacion){
        /* mover nav al body para evitar herencia de filter */
          if(window.innerWidth < 768){
            document.body.appendChild(navegacion);
          } else {
            /* en desktop dejarla dentro de la barra */
            var contenedor = document.querySelector('.barra .contenedor');
            if(contenedor) contenedor.appendChild(navegacion);
          }
        window.addEventListener('resize', function(){
          if(window.innerWidth >= 768){
            var contenedor = document.querySelector('.barra .contenedor');
            if(contenedor && !contenedor.contains(navegacion)){
              contenedor.appendChild(navegacion);
            }
          } else {
            if(!document.body.contains(navegacion) || navegacion.parentElement !== document.body){
              document.body.appendChild(navegacion);
            }
          }
        });
        /* crear overlay una sola vez */
        var overlay = document.createElement('div');
        overlay.id = 'menu-overlay';
        overlay.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(0,5,30,0.5);z-index:9997;';
        document.body.appendChild(overlay);

        function cerrarMenu(){
          navegacion.classList.remove('activo');
          menuMovil.classList.remove('activo');
          document.body.classList.remove('menu-abierto');
          overlay.style.display = 'none';
          document.body.style.overflow = '';
        }

        menuMovil.addEventListener('click', function(){
          var abierto = navegacion.classList.toggle('activo');
          menuMovil.classList.toggle('activo');
          if(abierto){
            document.body.classList.add('menu-abierto');
            document.body.style.overflow = 'hidden';
            overlay.style.display = 'block';
            if(barra && !barra.classList.contains('sticky')){
              window.scrollTo({ top: barra.offsetTop, behavior: 'smooth' });
            }
          } else {
            cerrarMenu();
          }
        });
        navegacion.querySelectorAll('a').forEach(function(a){
        a.addEventListener('click', function(e){
          var href = this.getAttribute('href');
          e.preventDefault();
          cerrarMenu();
          setTimeout(function(){ window.location.href = href; }, 200);
        });
      });

      document.addEventListener('keydown', function(e){
        if(e.key === 'Escape') cerrarMenu();
      });

      document.addEventListener('click', function(e){
        if(navegacion.classList.contains('activo') &&
           !navegacion.contains(e.target) &&
           !menuMovil.contains(e.target)){
          cerrarMenu();
        }
      });
    }
        
      /* ── BOTONES +/− PRODUCTOS ── */
      document.querySelectorAll('.btn-cant').forEach(function(btn){
        btn.addEventListener('click', function(){
          var id  = this.dataset.id;
          var inp = document.querySelector('.input-cantidad[data-id="'+id+'"]');
          if(!inp) return;
          var val = parseInt(inp.value) || 0;
          var max = parseInt(inp.max) || 999;
          if(this.dataset.accion === 'mas'){
            if(val < max) inp.value = val + 1;
          } else {
            if(val > 0) inp.value = val - 1;
          }
          inp.dispatchEvent(new Event('input'));
        });
      });

      /* ── COMBOBOX FECHA NACIMIENTO ── */
      function actualizarFechaNacimiento(){
        var d = document.getElementById('fn-dia');
        var m = document.getElementById('fn-mes');
        var y = document.getElementById('fn-anio');
        var fn = document.getElementById('fecha_nacimiento');
        if(!d||!m||!y||!fn) return;
        if(d.value && m.value && y.value){
          fn.value = y.value + '-' + m.value + '-' + d.value;
          fn.dispatchEvent(new Event('change'));
        } else {
          fn.value = '';
        }
      }

      ['fn-dia','fn-mes','fn-anio'].forEach(function(id){
        var el = document.getElementById(id);
        if(el) el.addEventListener('change', actualizarFechaNacimiento);
      });


      /* CUENTA REGRESIVA */
      if (document.querySelector('.cuenta-regresiva')) {
        var fechaEvento      = new Date('2026-07-10T14:00:00');
        var elementoDias     = document.getElementById('dias');
        var elementoHoras    = document.getElementById('horas');
        var elementoMinutos  = document.getElementById('minutos');
        var elementoSegundos = document.getElementById('segundos');
        function actualizarContador() {
          var ahora = new Date(); var diferencia = fechaEvento - ahora;
          if (diferencia <= 0) {
            if (elementoDias)     elementoDias.textContent     = '0';
            if (elementoHoras)    elementoHoras.textContent    = '0';
            if (elementoMinutos)  elementoMinutos.textContent  = '0';
            if (elementoSegundos) elementoSegundos.textContent = '0';
            return;
          }
          if (elementoDias)     elementoDias.textContent     = Math.floor(diferencia / (1000 * 60 * 60 * 24));
          if (elementoHoras)    elementoHoras.textContent    = Math.floor((diferencia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          if (elementoMinutos)  elementoMinutos.textContent  = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
          if (elementoSegundos) elementoSegundos.textContent = Math.floor((diferencia % (1000 * 60)) / 1000);
        }
        actualizarContador();
        setInterval(actualizarContador, 1000);
      }

      /* PROGRAMA POR DIA */
      var menuPrograma = document.querySelector('.menu-programa');
      if (menuPrograma) {
        var enlacesPrograma   = document.querySelectorAll('.menu-programa a');
        var seccionesPrograma = document.querySelectorAll('.info-curso');
        seccionesPrograma.forEach(function (s) { s.classList.add('hidden'); });
        var viernes = document.getElementById('viernes');
        if (viernes) viernes.classList.remove('hidden');
        if (enlacesPrograma[0]) enlacesPrograma[0].classList.add('activo');
        enlacesPrograma.forEach(function (enlace) {
          enlace.addEventListener('click', function (e) {
            e.preventDefault();
            enlacesPrograma.forEach(function (a) { a.classList.remove('activo'); });
            seccionesPrograma.forEach(function (s) { s.classList.add('hidden'); });
            this.classList.add('activo');
            var sec = document.getElementById(this.getAttribute('href').replace('#', ''));
            if (sec) sec.classList.remove('hidden');
          });
        });
      }

      /* BUSCADOR DE ESTADO */
      var btnBuscarEstado = document.getElementById('btn-buscar');
      if (btnBuscarEstado) btnBuscarEstado.addEventListener('click', ejecutarBusqueda);

      var inputBuscar = document.getElementById('buscar-inscrito');
      if (inputBuscar) {
        inputBuscar.addEventListener('keydown', function (e) {
          if (e.key === 'Enter') { e.preventDefault(); ejecutarBusqueda(); }
        });
      }

      function ejecutarBusqueda() {
        var busqueda  = document.getElementById('buscar-inscrito').value.trim();
        var resultado = document.getElementById('resultado-busqueda');
        var btn       = document.getElementById('btn-buscar');
        if (!busqueda) { resultado.innerHTML = '<p style="color:#da002b;text-align:center;">Ingresa tu carnet o celular</p>'; return; }
        btn.disabled  = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Buscando...';
        var fd = new FormData();
        fd.append('accion', 'buscar_estado');
        fd.append('busqueda', busqueda);
        fetch('guardar_inscripcion.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          btn.disabled  = false;
          btn.innerHTML = '<i class="fa-solid fa-search"></i> Consultar Estado';
          if (data.ok) {
            var d = data.datos;
            var est = d.estado_pago === 'confirmado'
              ? '<span class="estado-confirmado"><i class="fa-solid fa-circle-check"></i> CONFIRMADO</span>'
              : '<span class="estado-pendiente"><i class="fa-solid fa-clock"></i> PENDIENTE — en espera de verificacion</span>';
            resultado.innerHTML =
              '<div class="card-resultado-estado">' +
              '<p><strong><i class="fa-solid fa-user"></i> ' + d.nombre + ' ' + d.apellido + '</strong></p>' +
              '<p><i class="fa-solid fa-id-card"></i> Carnet: ' + d.carnet + '</p>' +
              '<p><i class="fa-solid fa-box"></i> Paquete: ' + d.paquete + '</p>' +
              '<p><i class="fa-solid fa-calendar"></i> Registrado: ' + d.fecha + '</p>' +
              '<p>Estado: ' + est + '</p>' +
              (d.estado_pago === 'pendiente'
                ? '<p class="aviso-pendiente-msg">Tu comprobante esta siendo revisado por la tesorera.</p>'
                : '<p class="aviso-confirmado-msg">Tu inscripcion esta confirmada. Nos vemos en el encuentro!</p>') +
              '</div>';
          } else {
            resultado.innerHTML = '<div class="aviso-inscrito-existente" style="display:block;"><i class="fa-solid fa-circle-info"></i> ' + data.msg + '</div>';
          }
        })
        .catch(function () {
          btn.disabled  = false;
          btn.innerHTML = '<i class="fa-solid fa-search"></i> Consultar Estado';
          resultado.innerHTML = '<p style="color:#da002b;text-align:center;">Error de conexion.</p>';
        });
      }

      /* REGISTRO */
      var formReg = document.getElementById('form-registro');
      if (!formReg) return;

      var elFecha = document.getElementById('fecha_nacimiento');
      if (elFecha) {
        elFecha.addEventListener('change', function () {
          var hoy = new Date(); var nac = new Date(this.value);
          var e = hoy.getFullYear() - nac.getFullYear();
          var m = hoy.getMonth() - nac.getMonth();
          if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) e--;
          document.getElementById('edad').value = e > 0 ? e : '';
          revisarCompleto();
        });
      }

      var elIglesia = document.getElementById('iglesia_id');
      if (elIglesia) {
        elIglesia.addEventListener('change', function () {
          var opt        = this.options[this.selectedIndex];
          var distritoId = opt.dataset.distritoId || '';
          var distrito   = opt.dataset.distrito   || '';
          document.getElementById('distrito_id').value     = distritoId;
          document.getElementById('distrito_nombre').value = distritoId ? distrito : 'Invitado (sin distrito)';
          limpiarError('err-iglesia');
          revisarCompleto();
        });
      }

      document.querySelectorAll('.input-cantidad').forEach(function (inp) {
        inp.addEventListener('input', function () {
          var card  = this.closest('.card-producto');
          var talla = card.querySelector('.producto-talla');
          if (talla) talla.style.display = parseInt(this.value) > 0 ? 'block' : 'none';
          if(parseInt(this.value) > 0){
            var pid    = this.dataset.id;
            var selT   = card.querySelector('.select-talla');
            var tipo   = selT ? (selT.dataset.tipo || '') : '';
            var selGen = card.querySelector('.radio-genero:checked');
            var genero = selGen ? selGen.value : 'hombre';
            cargarMedidas(card, pid, genero, tipo);
          }
          revisarCompleto();
        });
      });

      document.querySelectorAll('.radio-genero').forEach(function(radio){
        radio.addEventListener('change', function(){
          var card = this.closest('.card-producto');
          var inp  = card.querySelector('.input-cantidad');
          var selT = card.querySelector('.select-talla');
          var pid  = inp ? inp.dataset.id : '';
          var tipo = selT ? (selT.dataset.tipo || '').toLowerCase().trim() : '';
          if(tipo === 'gorra') return;
          cargarMedidas(card, pid, this.value, tipo);
        });
      });

      function cargarMedidas(card, pid, genero, tipo){
        var wrapMedidas = card.querySelector('.tabla-medidas-wrap');
        var tbody       = card.querySelector('.tbody-medidas');
        var selTalla    = card.querySelector('.select-talla');
        if(!wrapMedidas || !tbody) return;
        var esGorra = tipo && tipo.toLowerCase().trim() === 'gorra';
        var generoConsulta = esGorra ? 'unisex' : genero;
        var fd2 = new FormData();
        fd2.append('accion',      'listar_tallas_publico');
        fd2.append('producto_id', pid);
        fd2.append('genero',      generoConsulta);
        fetch('guardar_inscripcion.php', { method: 'POST', body: fd2 })
        .then(function(r){ return r.json(); })
        .then(function(data){
          if(data.ok && data.tallas && data.tallas.length){
            var valorActual = selTalla ? selTalla.value : '';
            if(selTalla){
              selTalla.innerHTML = '<option value="">-- Talla --</option>' +
                data.tallas.map(function(t){
                  return '<option value="'+t.talla+'"'+(t.talla===valorActual?' selected':'')+'>'+t.talla+'</option>';
                }).join('');
            }
            if(esGorra){
              wrapMedidas.style.display = 'none';
            } else {
              tbody.innerHTML = data.tallas.map(function(t){
                return '<tr><td>'+t.talla+'</td><td>'+t.ancho_cm+' cm</td><td>'+t.alto_cm+' cm</td></tr>';
              }).join('');
              wrapMedidas.style.display = 'block';
              resaltarTalla(card, selTalla ? selTalla.value : '');
            }
          } else {
            mostrarMedidasDefault(card, genero, tipo, selTalla);
          }
        })
        .catch(function(){ mostrarMedidasDefault(card, genero, tipo, selTalla); });
      }

      function mostrarMedidasDefault(card, genero, tipo, selTalla){
        var wrapMedidas = card.querySelector('.tabla-medidas-wrap');
        var tbody       = card.querySelector('.tbody-medidas');
        if(!wrapMedidas || !tbody) return;
        var medidasHombre = [
          {t:'XS',a:50,h:69},{t:'S',a:52,h:72},{t:'M',a:54,h:75},
          {t:'L',a:56,h:78},{t:'XL',a:58,h:81},{t:'XXL',a:60,h:84},{t:'XXXL',a:62,h:87}
        ];
        var medidasMujer = [
          {t:'XS',a:48,h:61},{t:'S',a:50,h:64},{t:'M',a:52,h:67},
          {t:'L',a:54,h:70},{t:'XL',a:56,h:73},{t:'XXL',a:58,h:76},{t:'XXXL',a:60,h:79}
        ];
        var esGorra = tipo && tipo.toLowerCase() === 'gorra';
        if(esGorra){
          if(selTalla){
            selTalla.innerHTML = '<option value="">-- Talla --</option>' +
              ['Pequeño','Mediano','Grande','Extra Grande'].map(function(t){
                return '<option value="'+t+'">'+t+'</option>';
              }).join('');
          }
          wrapMedidas.style.display = 'none';
        } else {
          var medidas = genero === 'mujer' ? medidasMujer : medidasHombre;
          tbody.innerHTML = medidas.map(function(m){
            return '<tr><td>'+m.t+'</td><td>'+m.a+' cm</td><td>'+m.h+' cm</td></tr>';
          }).join('');
          if(selTalla){
            var valorActual = selTalla.value;
            selTalla.innerHTML = '<option value="">-- Talla --</option>' +
              medidas.map(function(m){
                return '<option value="'+m.t+'"'+(m.t===valorActual?' selected':'')+'>'+m.t+'</option>';
              }).join('');
          }
          wrapMedidas.style.display = 'block';
        }
        resaltarTalla(card, selTalla ? selTalla.value : '');
      }

      function resaltarTalla(card, tallaSeleccionada){
        var filas = card.querySelectorAll('.tbody-medidas tr');
        filas.forEach(function(fila){
          var celdaTalla = fila.querySelector('td:first-child');
          if(celdaTalla && celdaTalla.textContent.trim() === tallaSeleccionada){
            fila.style.background = '#03045e';
            fila.style.color      = '#fff';
            fila.style.fontWeight = 'bold';
          } else {
            fila.style.background = '';
            fila.style.color      = '';
            fila.style.fontWeight = '';
          }
        });
      }

      var camposRequeridos = ['nombre','apellido','carnet','celular','iglesia_id','tipo_inscrito_id'];
      camposRequeridos.forEach(function (id) {
        var el = document.getElementById(id);
        if (el) { el.addEventListener('input', revisarCompleto); el.addEventListener('change', revisarCompleto); }
      });
      document.querySelectorAll('input[name="paquete"]').forEach(function (r) {
        r.addEventListener('change', revisarCompleto);
      });

      function revisarCompleto() {
        var ok = true;
        camposRequeridos.forEach(function (id) {
          var el = document.getElementById(id);
          if (!el || el.value.trim() === '') ok = false;
        });
        var f = document.getElementById('fecha_nacimiento');
        if (!f || f.value === '') ok = false;
        if (!document.querySelector('input[name="paquete"]:checked')) ok = false;
        var btn  = document.getElementById('btn-calcular');
        var hint = document.getElementById('hint-calcular');
        if (btn)  btn.disabled = !ok;
        if (hint) hint.style.display = ok ? 'none' : 'block';
      }

      revisarCompleto();

      var btnCalc = document.getElementById('btn-calcular');
      if (btnCalc) {
        btnCalc.addEventListener('click', function () {
          limpiarErrores();
          if (!validar()) return;
          var radio   = document.querySelector('input[name="paquete"]:checked');
          var carnet  = document.getElementById('carnet').value.trim();
          var celular = document.getElementById('celular').value.trim();
          btnCalc.disabled  = true;
          btnCalc.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verificando...';
          var fd = new FormData();
          fd.append('accion',  'verificar');
          fd.append('carnet',  carnet);
          fd.append('celular', celular);
          fetch('guardar_inscripcion.php', { method: 'POST', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            btnCalc.disabled  = false;
            btnCalc.innerHTML = '<i class="fa-solid fa-eye"></i> Ver Resumen y Continuar al Pago';
            if (data.inscrito) { mostrarYaInscrito(data); return; }
            mostrarResumen(radio);
          })
          .catch(function () {
            btnCalc.disabled  = false;
            btnCalc.innerHTML = '<i class="fa-solid fa-eye"></i> Ver Resumen y Continuar al Pago';
            alert('Error de conexion. Intenta de nuevo.');
          });
        });
      }

      function mostrarResumen(radio) {
        var nombre    = document.getElementById('nombre').value.trim();
        var apellido  = document.getElementById('apellido').value.trim();
        var carnet    = document.getElementById('carnet').value.trim();
        var fecha     = document.getElementById('fecha_nacimiento').value;
        var edad      = document.getElementById('edad').value;
        var celular   = document.getElementById('celular').value.trim();
        var elTipo    = document.getElementById('tipo_inscrito_id');
        var tipoTx    = elTipo.options[elTipo.selectedIndex].text;
        var elIgl     = document.getElementById('iglesia_id');
        var iglesiaTx = elIgl.options[elIgl.selectedIndex].text;
        var distTx    = document.getElementById('distrito_nombre').value || 'Invitado (sin distrito)';
        var precioPaq = parseFloat(radio.dataset.precio) || 0;
        var total     = precioPaq;

        setText('res-nombre-completo', nombre + ' ' + apellido);
        setText('res-carnet',         carnet);
        setText('res-fecha',          formatearFecha(fecha));
        setText('res-edad',           edad ? edad + ' años' : '—');
        setText('res-celular',        celular);
        setText('res-tipo',           tipoTx);
        setText('res-iglesia',        iglesiaTx);
        setText('res-distrito',       distTx);
        setText('res-paquete',        radio.dataset.nombre);
        setText('res-precio-paquete', 'Bs. ' + precioPaq.toFixed(2));

        var tablaProds = document.getElementById('res-productos-tabla');
        var wrapProds  = document.getElementById('res-productos-wrap');
        tablaProds.innerHTML = '';
        var hayProductos = false;
        document.querySelectorAll('.input-cantidad').forEach(function (inp) {
          var cant = parseInt(inp.value) || 0;
          if (cant > 0) {
            hayProductos = true;
            var card  = inp.closest('.card-producto');
            var talla = card.querySelector('.select-talla');
            var sub   = cant * (parseFloat(inp.dataset.precio) || 0);
            total += sub;
            var tr = document.createElement('tr');
            tr.innerHTML = '<td class="lbl">' + inp.dataset.nombre + '</td><td>' + cant + 'x' +
              (talla && talla.value ? ' <em>Talla: ' + talla.value + '</em>' : '') +
              ' — Bs. ' + sub.toFixed(2) + '</td>';
            tablaProds.appendChild(tr);
          }
        });
        wrapProds.style.display = hayProductos ? 'block' : 'none';
        setText('res-total', 'Bs. ' + total.toFixed(2));
        formReg.dataset.totalCalculado = total.toFixed(2);

        document.getElementById('bloque-formulario').style.display = 'none';
        document.getElementById('bloque-resumen').style.display    = 'block';
        document.getElementById('seccion-qr').style.display        = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }

      var btnEditar = document.getElementById('btn-editar');
      if (btnEditar) {
        btnEditar.addEventListener('click', function () {
          document.getElementById('bloque-resumen').style.display    = 'none';
          document.getElementById('bloque-formulario').style.display = 'block';
          document.getElementById('seccion-qr').style.display        = 'none';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      }

      var btnIrPago = document.getElementById('btn-ir-pago');
      if (btnIrPago) {
        btnIrPago.addEventListener('click', function () {
          document.getElementById('bloque-resumen').style.display = 'none';
          var secQR = document.getElementById('seccion-qr');
          secQR.style.display = 'block';
          secQR.scrollIntoView({ behavior: 'smooth' });
        });
      }

      function mostrarYaInscrito(data) {
        var est = data.estado === 'confirmado'
          ? '<span class="estado-confirmado"><i class="fa-solid fa-circle-check"></i> CONFIRMADO</span>'
          : '<span class="estado-pendiente"><i class="fa-solid fa-clock"></i> PENDIENTE</span>';
        var div = document.getElementById('aviso-ya-inscrito');
        if (!div) {
          div = document.createElement('div');
          div.id = 'aviso-ya-inscrito'; div.className = 'aviso-inscrito-existente';
          var bloqueForm = document.getElementById('bloque-formulario');
          bloqueForm.parentNode.insertBefore(div, bloqueForm.nextSibling);
        }
        div.style.display = 'block';
        div.innerHTML =
          '<i class="fa-solid fa-triangle-exclamation"></i> ' +
          '<strong>Ya estas inscrito al encuentro</strong>' +
          '<p>Tu carnet o celular ya tiene una inscripcion registrada a nombre de <strong>' + (data.nombre || '') + '</strong>.</p>' +
          '<p>Estado actual: ' + est + '</p>' +
          '<p>Si crees que te equivocaste de datos, puedes volver a corregirlos.</p>' +
          '<p class="aviso-no-continuar">Si los datos son correctos, no puedes inscribirte dos veces.</p>' +
          '<div style="margin-top:15px;text-align:center;">' +
          '<button type="button" class="button hollow" onclick="cerrarAvisoInscrito()" style="border-color:#03045e;color:#03045e;">' +
          '<i class="fa-solid fa-arrow-left"></i> Volver a corregir mis datos</button></div>';
        div.scrollIntoView({ behavior: 'smooth' });
      }

      var elComp = document.getElementById('comprobante');
      var btnSub = document.getElementById('btn-subir');
      if (elComp) {
        elComp.addEventListener('change', function () {
          if (btnSub) btnSub.disabled = this.files.length === 0;
          limpiarError('err-comprobante');
        });
      }

      if (btnSub) {
        btnSub.addEventListener('click', function () {
          var arch = document.getElementById('comprobante').files[0];
          if (!arch) { mostrarError('err-comprobante', 'Selecciona un archivo'); return; }
          var fd = new FormData();
          fd.append('accion',           'subir_comprobante');
          fd.append('comprobante',      arch);
          fd.append('nombre_inscrito',  document.getElementById('nombre').value);
          fd.append('apellido_inscrito',document.getElementById('apellido').value);
          btnSub.disabled  = true;
          btnSub.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Subiendo...';
          fetch('guardar_inscripcion.php', { method: 'POST', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            var msgDiv = document.getElementById('msg-subida');
            msgDiv.style.display = 'block';
            if (data.ok) {
              msgDiv.className = 'mensaje-exito';
              msgDiv.innerHTML = '<i class="fa-solid fa-check-circle"></i> ' + data.msg + '<br><small>Presione el boton de confirmar inscripcion.</small>';
              formReg.dataset.comprobante = data.archivo;
              document.getElementById('btn-registrar').disabled = false;
              btnSub.innerHTML = '<i class="fa-solid fa-upload"></i> Subir Comprobante';
            } else {
              msgDiv.className = 'mensaje-error';
              msgDiv.innerHTML = '<i class="fa-solid fa-times-circle"></i> ' + data.msg;
              btnSub.disabled  = false;
              btnSub.innerHTML = '<i class="fa-solid fa-upload"></i> Subir Comprobante';
            }
          })
          .catch(function () {
            btnSub.disabled  = false;
            btnSub.innerHTML = '<i class="fa-solid fa-upload"></i> Subir Comprobante';
            alert('Error de conexion al subir el comprobante.');
          });
        });
      }

      var btnReg = document.getElementById('btn-registrar');
      if (btnReg) {
        btnReg.addEventListener('click', function () {
          if (!formReg.dataset.comprobante) { mostrarError('err-comprobante', 'Debes subir el comprobante primero'); return; }
          var radio = document.querySelector('input[name="paquete"]:checked');
          var prods = [];
          document.querySelectorAll('.input-cantidad').forEach(function (inp) {
            var cant = parseInt(inp.value) || 0;
            if (cant > 0) {
              var card  = inp.closest('.card-producto');
              var talla = card.querySelector('.select-talla');
              var tipo  = talla ? (talla.dataset.tipo || '').toLowerCase().trim() : '';
              var genero = 'hombre';
              if(tipo === 'gorra'){
                genero = 'unisex';
              } else {
                var radioGen = card.querySelector('.radio-genero:checked');
                genero = radioGen ? radioGen.value : 'hombre';
              }
              prods.push({ id:inp.dataset.id, nombre:inp.dataset.nombre, cantidad:cant, talla:talla?talla.value:'', genero:genero });
            }
          });
          var fd = new FormData();
          fd.append('accion',           'registrar');
          fd.append('nombre',           document.getElementById('nombre').value);
          fd.append('apellido',         document.getElementById('apellido').value);
          fd.append('carnet',           document.getElementById('carnet').value);
          fd.append('fecha_nacimiento', document.getElementById('fecha_nacimiento').value);
          fd.append('edad',             document.getElementById('edad').value);
          fd.append('celular',          document.getElementById('celular').value);
          fd.append('iglesia_id',       document.getElementById('iglesia_id').value);
          fd.append('distrito_id',      document.getElementById('distrito_id').value);
          fd.append('ministerio_id',    document.getElementById('ministerio_id').value);
          fd.append('tipo_inscrito_id', document.getElementById('tipo_inscrito_id').value);
          fd.append('paquete_id',       radio.value);
          fd.append('regalo_id',        document.getElementById('regalo_id').value);
          fd.append('comprobante_arch', formReg.dataset.comprobante);
          fd.append('productos_json',   JSON.stringify(prods));
          btnReg.disabled  = true;
          btnReg.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Registrando...';
          fetch('guardar_inscripcion.php', { method: 'POST', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.ok) {
              mostrarExito(document.getElementById('nombre').value, document.getElementById('apellido').value);
            } else {
              alert('Error: ' + data.msg);
              btnReg.disabled  = false;
              btnReg.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar Inscripcion';
            }
          })
          .catch(function () {
            btnReg.disabled  = false;
            btnReg.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar Inscripcion';
            alert('Error de conexion. Intenta de nuevo.');
          });
        });
      }

      function mostrarExito(nombre, apellido) {
        var bloqueForm = document.getElementById('bloque-formulario');
        var bloqueRes  = document.getElementById('bloque-resumen');
        var secQR      = document.getElementById('seccion-qr');
        var avisoQR    = document.querySelector('.aviso-qr');
        if (bloqueForm) bloqueForm.style.display = 'none';
        if (bloqueRes)  bloqueRes.style.display  = 'none';
        if (secQR)      secQR.style.display      = 'none';
        if (avisoQR)    avisoQR.style.display    = 'none';
        var buscador   = document.querySelector('.buscador-estado');
        var mensajeDiv = document.createElement('div');
        mensajeDiv.className = 'mensaje-registro-exitoso';
        mensajeDiv.innerHTML =
          '<div class="mre-icono"><i class="fa-solid fa-circle-check"></i></div>' +
          '<h3>Inscripcion enviada, ' + nombre + ' ' + apellido + '!</h3>' +
          '<p>Tu registro ha sido recibido con estado <strong>PENDIENTE</strong>.</p>' +
          '<p>La tesorera verificara tu comprobante de pago y confirmara tu inscripcion.</p>' +
          '<div class="mre-nota"><i class="fa-solid fa-envelope"></i> Se envio un aviso a la tesorera con tu comprobante adjunto.</div>';
        if (buscador) {
          buscador.parentNode.insertBefore(mensajeDiv, buscador);
          buscador.style.display = 'block';
          mensajeDiv.scrollIntoView({ behavior: 'smooth' });
        } else {
          var sec = document.querySelector('.seccion.contenedor');
          if (sec) sec.appendChild(mensajeDiv);
          mensajeDiv.scrollIntoView({ behavior: 'smooth' });
        }
      }

      function validar() {
        var ok = true;

        /* ── NOMBRE ── */
       var nombre = document.getElementById('nombre');
        if (!nombre || nombre.value.trim() === '') {
          mostrarError('err-nombre', 'El nombre es obligatorio'); ok = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(nombre.value.trim())) {
          mostrarError('err-nombre', 'El nombre solo puede contener letras'); ok = false;
        } else if (nombre.value.trim().length < 2) {
          mostrarError('err-nombre', 'El nombre debe tener al menos 2 letras'); ok = false;
        } else if (nombre.value.trim().length > 50) {
          mostrarError('err-nombre', 'El nombre no puede superar 50 caracteres'); ok = false;
        }

        /* ── APELLIDO ── */
        var apellido = document.getElementById('apellido');
        if (!apellido || apellido.value.trim() === '') {
          mostrarError('err-apellido', 'El apellido es obligatorio'); ok = false;
        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(apellido.value.trim())) {
          mostrarError('err-apellido', 'El apellido solo puede contener letras'); ok = false;
        } else if (apellido.value.trim().length < 2) {
          mostrarError('err-apellido', 'El apellido debe tener al menos 2 letras'); ok = false;
        } else if (apellido.value.trim().length > 60) {
          mostrarError('err-apellido', 'El apellido no puede superar 60 caracteres'); ok = false;
        }

        /* ── CARNET ── */
        var carnet = document.getElementById('carnet');
        if (!carnet || carnet.value.trim() === '') {
          mostrarError('err-carnet', 'El carnet es obligatorio'); ok = false;
        } else if (!/^\d{6,8}(-\d{1,2}[A-Za-z]?)?[A-Za-z]?$/.test(carnet.value.trim())) {
          mostrarError('err-carnet', 'Formato invalido. Ej: 1234567 o 1234567-1A'); ok = false;
        }
      
        /* ── CELULAR ── */
        var cel = document.getElementById('celular');
        if (!cel || cel.value.trim() === '') {
          mostrarError('err-celular', 'El celular es obligatorio'); ok = false;
        } else if (!/^\d{8}$/.test(cel.value.trim())) {
          mostrarError('err-celular', 'El celular debe tener exactamente 8 digitos numericos'); ok = false;
        } else if (!/^[67]/.test(cel.value.trim())) {
          mostrarError('err-celular', 'El celular debe empezar con 6 o 7. Ej: 68319277'); ok = false;
        }
      
        /* ── IGLESIA ── */
        var igl = document.getElementById('iglesia_id');
        if (!igl || igl.value === '') {
          mostrarError('err-iglesia', 'Selecciona tu iglesia'); ok = false;
        }
      
        /* ── TIPO INSCRITO ── */
        var tipo = document.getElementById('tipo_inscrito_id');
        if (!tipo || tipo.value === '') {
          mostrarError('err-tipo', 'Selecciona el tipo de inscrito'); ok = false;
        }
      
        /* ── FECHA NACIMIENTO ── */
        var fecha = document.getElementById('fecha_nacimiento');
        if (!fecha || fecha.value === '') {
          mostrarError('err-fecha', 'La fecha de nacimiento es obligatoria'); ok = false;
        } else if (new Date(fecha.value) >= new Date()) {
          mostrarError('err-fecha', 'La fecha no puede ser hoy o en el futuro'); ok = false;
        } else {
          /* edad mínima 5 años, máxima 100 */
          var hoy = new Date();
          var nac = new Date(fecha.value);
          var edad = hoy.getFullYear() - nac.getFullYear();
          var mes  = hoy.getMonth() - nac.getMonth();
          if (mes < 0 || (mes === 0 && hoy.getDate() < nac.getDate())) edad--;
          if (edad < 5) {
            mostrarError('err-fecha', 'La edad minima es 5 años'); ok = false;
          } else if (edad > 100) {
            mostrarError('err-fecha', 'Verifica el año de nacimiento'); ok = false;
          }
        }
      
        /* ── PAQUETE ── */
        if (!document.querySelector('input[name="paquete"]:checked')) {
          mostrarError('err-paquete', 'Debes elegir un paquete'); ok = false;
        }
      
        /* ── TALLAS DE PRODUCTOS ── */
        document.querySelectorAll('.input-cantidad').forEach(function (inp) {
          var cant = parseInt(inp.value) || 0;
          if (cant > 0) {
            var card  = inp.closest('.card-producto');
            var talla = card.querySelector('.select-talla');
            var errT  = card.querySelector('.error-talla');
            if (talla && talla.value === '') {
              if (errT) { errT.textContent = 'Selecciona una talla para ' + inp.dataset.nombre; errT.style.display = 'block'; }
              talla.style.borderColor = '#da002b';
              ok = false;
            }
          }
        });
      
        if (!ok) {
          var primero = document.querySelector('.campo-error[style*="block"]');
          if (primero && primero.offsetParent !== null) primero.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return ok;
      }

      function mostrarError(id, msg) { var el = document.getElementById(id); if (el) { el.textContent = msg; el.style.display = 'block'; } }
      function limpiarError(id)      { var el = document.getElementById(id); if (el) { el.textContent = ''; el.style.display = 'none'; } }
      function limpiarErrores() {
        document.querySelectorAll('.campo-error').forEach(function (el) { el.textContent = ''; el.style.display = 'none'; });
        document.querySelectorAll('.select-talla').forEach(function (el) { el.style.borderColor = ''; });
      }
      function setText(id, val) { var el = document.getElementById(id); if (el) el.textContent = val || '—'; }
      function formatearFecha(f) {
        if (!f) return '—';
        var p = f.split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : f;
      }

      window.cerrarAvisoInscrito = function() {
        var div = document.getElementById('aviso-ya-inscrito');
        if (div) div.style.display = 'none';
        var btn = document.getElementById('btn-calcular');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-eye"></i> Ver Resumen y Continuar al Pago'; }
        window.scrollTo({ top: 0, behavior: 'smooth' });
      };

    }); // cierra domReady

})(); // cierra IIFE