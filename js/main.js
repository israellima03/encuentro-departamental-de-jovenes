console.log("JS FUNCIONANDO");

(function () {
    "use strict";

    /* ========================================
       MENU HAMBURGUESA
       ======================================== */
    var menuMovil  = document.querySelector('.menu-movil');
    var navegacion = document.querySelector('.navegacion-principal');

    if (menuMovil) {
        menuMovil.addEventListener('click', function () {
            navegacion.classList.toggle('activo');
            menuMovil.classList.toggle('activo');
        });
    }

    /* ========================================
       CONTADOR REGRESIVO
       ======================================== */
    if (document.querySelector('.cuenta-regresiva')) {
        var fechaEvento      = new Date('2026-07-10T14:00:00');
        var elementoDias     = document.getElementById('dias');
        var elementoHoras    = document.getElementById('horas');
        var elementoMinutos  = document.getElementById('minutos');
        var elementoSegundos = document.getElementById('segundos');

        function actualizarContador() {
            var ahora      = new Date();
            var diferencia = fechaEvento - ahora;
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

    /* ========================================
       DOM LISTO
       ======================================== */
    document.addEventListener('DOMContentLoaded', function () {

        /* ---- PROGRAMA POR DIA (index.php) ---- */
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

        /* ========================================
           BUSCADOR DE ESTADO — siempre activo
           ======================================== */
        var btnBuscarEstado = document.getElementById('btn-buscar');
        if (btnBuscarEstado) {
            btnBuscarEstado.addEventListener('click', function () {
                ejecutarBusqueda();
            });
        }

        /* tambien buscar al presionar Enter en el input */
        var inputBuscar = document.getElementById('buscar-inscrito');
        if (inputBuscar) {
            inputBuscar.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    ejecutarBusqueda();
                }
            });
        }

        function ejecutarBusqueda() {
            var busqueda  = document.getElementById('buscar-inscrito').value.trim();
            var resultado = document.getElementById('resultado-busqueda');
            var btn       = document.getElementById('btn-buscar');

            if (!busqueda) {
                resultado.innerHTML = '<p style="color:#da002b;text-align:center;">Ingresa tu carnet o celular</p>';
                return;
            }

            btn.disabled  = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Buscando...';

            var fd = new FormData();
            fd.append('accion',   'buscar_estado');
            fd.append('busqueda', busqueda);

            fetch('guardar_inscripcion.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa-solid fa-search"></i> Consultar Estado';

                if (data.ok) {
                    var d   = data.datos;
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
                            ? '<p class="aviso-pendiente-msg">Tu comprobante esta siendo revisado por la tesorera. Para saber si fue confirmada pregunta a tu lider local o distrital.</p>'
                            : '<p class="aviso-confirmado-msg">Tu inscripcion esta confirmada. Nos vemos en el encuentro!</p>') +
                        '</div>';
                } else {
                    resultado.innerHTML =
                        '<div class="aviso-inscrito-existente" style="display:block;">' +
                        '<i class="fa-solid fa-circle-info"></i> ' + data.msg +
                        '</div>';
                }
            })
            .catch(function () {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa-solid fa-search"></i> Consultar Estado';
                resultado.innerHTML = '<p style="color:#da002b;text-align:center;">Error de conexion. Intenta de nuevo.</p>';
            });
        }

        /* ========================================
           REGISTRO
           ======================================== */
        var formReg = document.getElementById('form-registro');
        if (!formReg) return;

        /* ---- EDAD AUTOMATICA ---- */
        var elFecha = document.getElementById('fecha_nacimiento');
        if (elFecha) {
            elFecha.addEventListener('change', function () {
                var hoy = new Date();
                var nac = new Date(this.value);
                var e   = hoy.getFullYear() - nac.getFullYear();
                var m   = hoy.getMonth() - nac.getMonth();
                if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) e--;
                document.getElementById('edad').value = e > 0 ? e : '';
                revisarCompleto();
            });
        }

        /* ---- AUTOCOMPLETAR IGLESIA Y DISTRITO DESDE MINISTERIO ---- */
        var elMinisterio = document.getElementById('ministerio_id');
        if (elMinisterio) {
            elMinisterio.addEventListener('change', function () {
                var opt = this.options[this.selectedIndex];
                document.getElementById('iglesia_id').value      = opt.dataset.iglesiaId  || '';
                document.getElementById('distrito_id').value     = opt.dataset.distritoId || '';
                document.getElementById('iglesia_nombre').value  = opt.dataset.iglesia    || '';
                document.getElementById('distrito_nombre').value = opt.dataset.distrito   || '';
                limpiarError('err-ministerio');
                revisarCompleto();
            });
        }

        /* ---- TALLA CUANDO CANTIDAD > 0 ---- */
        document.querySelectorAll('.input-cantidad').forEach(function (inp) {
            inp.addEventListener('input', function () {
                var card  = this.closest('.card-producto');
                var talla = card.querySelector('.producto-talla');
                if (talla) talla.style.display = parseInt(this.value) > 0 ? 'block' : 'none';
                revisarCompleto();
            });
        });

        /* ---- VIGILAR CAMPOS PARA ACTIVAR BTN-CALCULAR ---- */
        var camposRequeridos = [
            'nombre', 'apellido', 'carnet', 'celular',
            'ministerio_id', 'tipo_inscrito_id', 'regalo_id'
        ];

        camposRequeridos.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('input',  revisarCompleto);
                el.addEventListener('change', revisarCompleto);
            }
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

        /* ====================================================
           BTN CALCULAR — validar — verificar duplicado — RESUMEN
           ==================================================== */
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

                    if (data.inscrito) {
                        mostrarYaInscrito(data);
                        return;
                    }

                    mostrarResumen(radio);
                })
                .catch(function () {
                    btnCalc.disabled  = false;
                    btnCalc.innerHTML = '<i class="fa-solid fa-eye"></i> Ver Resumen y Continuar al Pago';
                    alert('Error de conexion. Intenta de nuevo.');
                });
            });
        }

        /* ---- MOSTRAR BLOQUE RESUMEN CON TODOS LOS DATOS ---- */
        function mostrarResumen(radio) {
            var nombre   = document.getElementById('nombre').value.trim();
            var apellido = document.getElementById('apellido').value.trim();
            var carnet   = document.getElementById('carnet').value.trim();
            var fecha    = document.getElementById('fecha_nacimiento').value;
            var edad     = document.getElementById('edad').value;
            var celular  = document.getElementById('celular').value.trim();

            var elTipo       = document.getElementById('tipo_inscrito_id');
            var tipoTexto    = elTipo.options[elTipo.selectedIndex].text;

            var elMin        = document.getElementById('ministerio_id');
            var ministerioTx = elMin.options[elMin.selectedIndex].text;
            var iglesiaTx    = document.getElementById('iglesia_nombre').value;
            var distritoTx   = document.getElementById('distrito_nombre').value;

            var elRegalo     = document.getElementById('regalo_id');
            var regaloTx     = elRegalo.options[elRegalo.selectedIndex].text;

            var precioPaq    = parseFloat(radio.dataset.precio) || 0;
            var nombrePaq    = radio.dataset.nombre;
            var total        = precioPaq;

            setText('res-nombre-completo', nombre + ' ' + apellido);
            setText('res-carnet',   carnet);
            setText('res-fecha',    formatearFecha(fecha));
            setText('res-edad',     edad ? edad + ' años' : '—');
            setText('res-celular',  celular);
            setText('res-tipo',     tipoTexto);

            setText('res-ministerio', ministerioTx);
            setText('res-iglesia',    iglesiaTx  || '—');
            setText('res-distrito',   distritoTx || '—');

            setText('res-paquete',        nombrePaq);
            setText('res-precio-paquete', 'Bs. ' + precioPaq.toFixed(2));
            setText('res-regalo',         regaloTx);

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
                    tr.innerHTML =
                        '<td class="lbl">' + inp.dataset.nombre + '</td>' +
                        '<td>' + cant + 'x' +
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

        /* ---- BOTON EDITAR: regresa al formulario ---- */
        var btnEditar = document.getElementById('btn-editar');
        if (btnEditar) {
            btnEditar.addEventListener('click', function () {
                document.getElementById('bloque-resumen').style.display    = 'none';
                document.getElementById('bloque-formulario').style.display = 'block';
                document.getElementById('seccion-qr').style.display        = 'none';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        /* ---- BOTON IR AL PAGO: desde resumen abre QR ---- */
        var btnIrPago = document.getElementById('btn-ir-pago');
        if (btnIrPago) {
            btnIrPago.addEventListener('click', function () {
                document.getElementById('bloque-resumen').style.display = 'none';
                var secQR = document.getElementById('seccion-qr');
                secQR.style.display = 'block';
                secQR.scrollIntoView({ behavior: 'smooth' });
            });
        }

        /* ---- AVISO YA INSCRITO ---- */
        function mostrarYaInscrito(data) {
            var est = data.estado === 'confirmado'
                ? '<span class="estado-confirmado"><i class="fa-solid fa-circle-check"></i> CONFIRMADO</span>'
                : '<span class="estado-pendiente"><i class="fa-solid fa-clock"></i> PENDIENTE</span>';

            var div = document.getElementById('aviso-ya-inscrito');
            if (!div) {
                div = document.createElement('div');
                div.id        = 'aviso-ya-inscrito';
                div.className = 'aviso-inscrito-existente';
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
                '<i class="fa-solid fa-arrow-left"></i> Volver a corregir mis datos' +
                '</button>' +
                '</div>';
            div.scrollIntoView({ behavior: 'smooth' });
        }

        /* ---- HABILITAR BTN-SUBIR CUANDO HAY ARCHIVO ---- */
        var elComp = document.getElementById('comprobante');
        var btnSub = document.getElementById('btn-subir');

        if (elComp) {
            elComp.addEventListener('change', function () {
                if (btnSub) btnSub.disabled = this.files.length === 0;
                limpiarError('err-comprobante');
            });
        }

        /* ---- SUBIR COMPROBANTE ---- */
        if (btnSub) {
            btnSub.addEventListener('click', function () {
                var arch     = document.getElementById('comprobante').files[0];
                var nombre   = document.getElementById('nombre').value;
                var apellido = document.getElementById('apellido').value;

                if (!arch) { mostrarError('err-comprobante', 'Selecciona un archivo'); return; }

                var fd = new FormData();
                fd.append('accion',            'subir_comprobante');
                fd.append('comprobante',       arch);
                fd.append('nombre_inscrito',   nombre);
                fd.append('apellido_inscrito', apellido);

                btnSub.disabled  = true;
                btnSub.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Subiendo...';

                fetch('guardar_inscripcion.php', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var msgDiv = document.getElementById('msg-subida');
                    msgDiv.style.display = 'block';
                    if (data.ok) {
                        msgDiv.className = 'mensaje-exito';
                        msgDiv.innerHTML =
                            '<i class="fa-solid fa-check-circle"></i> ' + data.msg +
                            '<br><small>precione el boton de confirmar inscripcion .</small>';
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

        /* ---- CONFIRMAR INSCRIPCION ---- */
        var btnReg = document.getElementById('btn-registrar');
        if (btnReg) {
            btnReg.addEventListener('click', function () {
                if (!formReg.dataset.comprobante) {
                    mostrarError('err-comprobante', 'Debes subir el comprobante primero');
                    return;
                }

                var radio = document.querySelector('input[name="paquete"]:checked');

                var prods = [];
                document.querySelectorAll('.input-cantidad').forEach(function (inp) {
                    var cant = parseInt(inp.value) || 0;
                    if (cant > 0) {
                        var card  = inp.closest('.card-producto');
                        var talla = card.querySelector('.select-talla');
                        prods.push({
                            id:       inp.dataset.id,
                            nombre:   inp.dataset.nombre,
                            cantidad: cant,
                            talla:    talla ? talla.value : ''
                        });
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
                fd.append('ministerio_id',    document.getElementById('ministerio_id').value);
                fd.append('iglesia_id',       document.getElementById('iglesia_id').value);
                fd.append('distrito_id',      document.getElementById('distrito_id').value);
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
                        mostrarExito(
                            document.getElementById('nombre').value,
                            document.getElementById('apellido').value
                        );
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

        /* ---- MENSAJE FINAL EXITO ---- */
        function mostrarExito(nombre, apellido) {
            /* ocultar formulario y bloques relacionados SIN borrar el buscador */
            var bloqueForm = document.getElementById('bloque-formulario');
            var bloqueRes  = document.getElementById('bloque-resumen');
            var secQR      = document.getElementById('seccion-qr');
            var avisoQR    = document.querySelector('.aviso-qr');

            if (bloqueForm) bloqueForm.style.display = 'none';
            if (bloqueRes)  bloqueRes.style.display  = 'none';
            if (secQR)      secQR.style.display      = 'none';
            if (avisoQR)    avisoQR.style.display    = 'none';

            /* crear el mensaje de exito e insertarlo antes del buscador */
            var buscador   = document.querySelector('.buscador-estado');
            var mensajeDiv = document.createElement('div');
            mensajeDiv.className = 'mensaje-registro-exitoso';
            mensajeDiv.innerHTML =
                '<div class="mre-icono"><i class="fa-solid fa-circle-check"></i></div>' +
                '<h3>Inscripcion enviada, ' + nombre + ' ' + apellido + '!</h3>' +
                '<p>Tu registro ha sido recibido con estado <strong>PENDIENTE</strong>.</p>' +
                '<p>La tesorera verificara tu comprobante de pago y confirmara tu inscripcion.</p>' +
                '<div class="mre-nota">' +
                '<i class="fa-solid fa-envelope"></i> ' +
                'Se envio un aviso a la tesorera con tu comprobante adjunto.' +
                '</div>';

            if (buscador) {
                /* insertar el mensaje justo antes del buscador existente */
                buscador.parentNode.insertBefore(mensajeDiv, buscador);
                /* asegurar que el buscador sea visible */
                buscador.style.display = 'block';
                mensajeDiv.scrollIntoView({ behavior: 'smooth' });
            } else {
                /* si no encuentra el buscador lo agrega al contenedor */
                var sec = document.querySelector('.seccion.contenedor');
                if (sec) sec.appendChild(mensajeDiv);
                mensajeDiv.scrollIntoView({ behavior: 'smooth' });
            }
        }

        /* ==========================================
           VALIDACIONES
           ========================================== */
        function validar() {
            var ok = true;

            function req(id, errId, msg) {
                var el = document.getElementById(id);
                if (!el || el.value.trim() === '') { mostrarError(errId, msg); ok = false; }
            }

            req('nombre',          'err-nombre',    'El nombre es obligatorio');
            req('apellido',        'err-apellido',  'El apellido es obligatorio');
            req('ministerio_id',   'err-ministerio','Selecciona tu ministerio');
            req('tipo_inscrito_id','err-tipo',      'Selecciona el tipo de inscrito');
            req('regalo_id',       'err-regalo',    'Selecciona un regalo');

            var carnet = document.getElementById('carnet');
            if (!carnet || carnet.value.trim() === '') {
                mostrarError('err-carnet', 'El carnet es obligatorio'); ok = false;
            } else if (!/^\d{6,8}(-\d{1,2}[A-Za-z]?)?[A-Za-z]?$/.test(carnet.value.trim())) {
                mostrarError('err-carnet', 'Formato invalido. Ej: 1234567 o 1234567-1A'); ok = false;
            }

            var cel = document.getElementById('celular');
            if (!cel || cel.value.trim() === '') {
                mostrarError('err-celular', 'El celular es obligatorio'); ok = false;
            } else if (!/^[67]\d{7}$/.test(cel.value.trim())) {
                mostrarError('err-celular', 'Celular boliviano invalido. Ej: 68319277'); ok = false;
            }

            var fecha = document.getElementById('fecha_nacimiento');
            if (!fecha || fecha.value === '') {
                mostrarError('err-fecha', 'La fecha de nacimiento es obligatoria'); ok = false;
            } else if (new Date(fecha.value) >= new Date()) {
                mostrarError('err-fecha', 'La fecha no puede ser hoy o en el futuro'); ok = false;
            }

            if (!document.querySelector('input[name="paquete"]:checked')) {
                mostrarError('err-paquete', 'Debes elegir un paquete'); ok = false;
            }

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

        /* ---- HELPERS ---- */
        function mostrarError(id, msg) {
            var el = document.getElementById(id);
            if (el) { el.textContent = msg; el.style.display = 'block'; }
        }

        function limpiarError(id) {
            var el = document.getElementById(id);
            if (el) { el.textContent = ''; el.style.display = 'none'; }
        }

        function limpiarErrores() {
            document.querySelectorAll('.campo-error').forEach(function (el) {
                el.textContent = ''; el.style.display = 'none';
            });
            document.querySelectorAll('.select-talla').forEach(function (el) {
                el.style.borderColor = '';
            });
        }

        function setText(id, val) {
            var el = document.getElementById(id);
            if (el) el.textContent = val || '—';
        }

        function formatearFecha(f) {
            if (!f) return '—';
            var p = f.split('-');
            return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : f;
        }

        /* permite al usuario corregir sus datos y volver a intentar */
        window.cerrarAvisoInscrito = function() {
            var div = document.getElementById('aviso-ya-inscrito');
            if (div) div.style.display = 'none';

            var btn = document.getElementById('btn-calcular');
            if (btn) {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa-solid fa-eye"></i> Ver Resumen y Continuar al Pago';
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

    }); /* fin DOMContentLoaded */

})();