console.log("JS FUNCIONANDO");

(function() {
    "use strict";

    /* ========================================
       MENU HAMBURGUESA
       ======================================== */
    var menuMovil  = document.querySelector('.menu-movil');
    var navegacion = document.querySelector('.navegacion-principal');

    if (menuMovil) {
        menuMovil.addEventListener('click', function() {
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
       TODO LO QUE NECESITA EL DOM LISTO
       ======================================== */
    document.addEventListener('DOMContentLoaded', function() {

        /* ----------------------------------------
           PROGRAMA POR DIA (solo index.php)
           ---------------------------------------- */
        var menuPrograma = document.querySelector('.menu-programa');

        if (menuPrograma) {
            var enlacesPrograma   = document.querySelectorAll('.menu-programa a');
            var seccionesPrograma = document.querySelectorAll('.info-curso');

            /* oculta todas las secciones */
            seccionesPrograma.forEach(function(sec) {
                sec.classList.add('hidden');
            });

            /* muestra viernes por defecto */
            var viernes = document.getElementById('viernes');
            if (viernes) viernes.classList.remove('hidden');

            /* marca el primer enlace como activo */
            if (enlacesPrograma[0]) enlacesPrograma[0].classList.add('activo');

            /* click en cada enlace */
            enlacesPrograma.forEach(function(enlace) {
                enlace.addEventListener('click', function(e) {
                    e.preventDefault();

                    enlacesPrograma.forEach(function(a) {
                        a.classList.remove('activo');
                    });

                    seccionesPrograma.forEach(function(sec) {
                        sec.classList.add('hidden');
                    });

                    this.classList.add('activo');

                    var id = this.getAttribute('href').replace('#', '');
                    var seccion = document.getElementById(id);
                    if (seccion) seccion.classList.remove('hidden');
                });
            });
        }

        /* ========================================
           REGISTRO CON QR Y VALIDACIONES
           ======================================== */
        var formReg = document.getElementById('form-registro');
        if (!formReg) return;

        /* ---- edad automatica ---- */
        var elFecha = document.getElementById('fecha_nacimiento');
        if (elFecha) {
            elFecha.addEventListener('change', function() {
                var hoy = new Date();
                var nac = new Date(this.value);
                var e   = hoy.getFullYear() - nac.getFullYear();
                var m   = hoy.getMonth() - nac.getMonth();
                if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) e--;
                document.getElementById('edad').value = e > 0 ? e : '';
                revisarCompleto();
            });
        }

        /* ---- talla cuando cantidad > 0 ---- */
        document.querySelectorAll('.input-cantidad').forEach(function(inp) {
            inp.addEventListener('input', function() {
                var card  = this.closest('.card-producto');
                var talla = card.querySelector('.producto-talla');
                if (talla) talla.style.display = parseInt(this.value) > 0 ? 'block' : 'none';
                revisarCompleto();
            });
        });

        /* ---- vigilar campos para activar btn-calcular ---- */
        var ids = [
            'nombre', 'apellido', 'carnet', 'celular',
            'ministerio_id', 'iglesia_id', 'distrito_id',
            'tipo_inscrito_id', 'regalo_id'
        ];

        ids.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('input',  revisarCompleto);
                el.addEventListener('change', revisarCompleto);
            }
        });

        document.querySelectorAll('input[name="paquete"]').forEach(function(r) {
            r.addEventListener('change', revisarCompleto);
        });

        /* ---- revisa si el formulario esta completo ---- */
        function revisarCompleto() {
            var ok = true;
            ids.forEach(function(id) {
                var el = document.getElementById(id);
                if (!el || el.value.trim() === '') ok = false;
            });
            if (!document.querySelector('input[name="paquete"]:checked')) ok = false;

            var btn  = document.getElementById('btn-calcular');
            var hint = document.getElementById('hint-calcular');
            if (btn)  btn.disabled = !ok;
            if (hint) hint.style.display = ok ? 'none' : 'block';
        }

        revisarCompleto();

        /* ---- calcular total + verificar duplicado + mostrar QR ---- */
        var btnCalc = document.getElementById('btn-calcular');
        if (btnCalc) {
            btnCalc.addEventListener('click', function() {
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
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    btnCalc.disabled  = false;
                    btnCalc.innerHTML = '<i class="fa-solid fa-calculator"></i> Calcular Total y Ver QR';

                    if (data.inscrito) {
                        mostrarYaInscrito(data);
                        return;
                    }

                    /* calcular resumen */
                    var total = parseFloat(radio.dataset.precio) || 0;
                    var items = ['Paquete: ' + radio.dataset.nombre + ' — Bs. ' + total.toFixed(2)];

                    document.querySelectorAll('.input-cantidad').forEach(function(inp) {
                        var cant = parseInt(inp.value) || 0;
                        if (cant > 0) {
                            var sub = cant * parseFloat(inp.dataset.precio);
                            total += sub;
                            items.push(cant + 'x ' + inp.dataset.nombre + ' — Bs. ' + sub.toFixed(2));
                        }
                    });

                    var lista = document.getElementById('lista-productos');
                    lista.style.display = 'block';
                    lista.innerHTML = '';
                    items.forEach(function(i) { lista.innerHTML += '<p>' + i + '</p>'; });
                    document.getElementById('suma-total').innerHTML = '<strong>Bs. ' + total.toFixed(2) + '</strong>';

                    var qr = document.getElementById('seccion-qr');
                    qr.style.display = 'block';
                    qr.scrollIntoView({ behavior: 'smooth' });
                })
                .catch(function() {
                    btnCalc.disabled  = false;
                    btnCalc.innerHTML = '<i class="fa-solid fa-calculator"></i> Calcular Total y Ver QR';
                    alert('Error de conexion. Intenta de nuevo.');
                });
            });
        }

        /* ---- aviso ya inscrito ---- */
        function mostrarYaInscrito(data) {
            var est = data.estado === 'confirmado'
                ? '<span class="estado-confirmado"><i class="fa-solid fa-circle-check"></i> CONFIRMADO</span>'
                : '<span class="estado-pendiente"><i class="fa-solid fa-clock"></i> PENDIENTE</span>';

            var div = document.getElementById('aviso-ya-inscrito');
            if (!div) {
                div = document.createElement('div');
                div.id        = 'aviso-ya-inscrito';
                div.className = 'aviso-inscrito-existente';
                document.getElementById('seccion-qr').before(div);
            }
            div.style.display = 'block';
            div.innerHTML =
                '<i class="fa-solid fa-triangle-exclamation"></i> ' +
                '<strong>Ya estas inscrito al encuentro</strong>' +
                '<p>Tu carnet o celular ya tiene una inscripcion registrada.</p>' +
                '<p>Estado: ' + est + '</p>' +
                '<p class="aviso-no-continuar">No puedes inscribirte nuevamente con estos datos.</p>';
            div.scrollIntoView({ behavior: 'smooth' });
            document.getElementById('seccion-qr').style.display = 'none';
        }

        /* ---- habilitar btn-subir cuando hay archivo ---- */
        var elComp = document.getElementById('comprobante');
        var btnSub = document.getElementById('btn-subir');

        if (elComp) {
            elComp.addEventListener('change', function() {
                if (btnSub) btnSub.disabled = this.files.length === 0;
                limpiarError('err-comprobante');
            });
        }

        /* ---- subir comprobante ---- */
        if (btnSub) {
            btnSub.addEventListener('click', function() {
                var arch     = document.getElementById('comprobante').files[0];
                var nombre   = document.getElementById('nombre').value;
                var apellido = document.getElementById('apellido').value;

                if (!arch) {
                    mostrarError('err-comprobante', 'Selecciona un archivo');
                    return;
                }

                var fd = new FormData();
                fd.append('accion',            'subir_comprobante');
                fd.append('comprobante',       arch);
                fd.append('nombre_inscrito',   nombre);
                fd.append('apellido_inscrito', apellido);

                btnSub.disabled  = true;
                btnSub.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Subiendo...';

                fetch('guardar_inscripcion.php', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var msgDiv = document.getElementById('msg-subida');
                    msgDiv.style.display = 'block';

                    if (data.ok) {
                        msgDiv.className = 'mensaje-exito';
                        msgDiv.innerHTML =
                            '<i class="fa-solid fa-check-circle"></i> ' + data.msg +
                            '<br><small>La tesorera revisara tu comprobante y confirmara tu inscripcion.</small>';
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
                .catch(function() {
                    btnSub.disabled  = false;
                    btnSub.innerHTML = '<i class="fa-solid fa-upload"></i> Subir Comprobante';
                    alert('Error de conexion al subir el comprobante.');
                });
            });
        }

        /* ---- confirmar inscripcion ---- */
        var btnReg = document.getElementById('btn-registrar');
        if (btnReg) {
            btnReg.addEventListener('click', function() {
                limpiarErrores();
                if (!validar()) return;

                if (!formReg.dataset.comprobante) {
                    mostrarError('err-comprobante', 'Debes subir el comprobante primero');
                    return;
                }

                var radio = document.querySelector('input[name="paquete"]:checked');

                /* recolectar productos */
                var prods = [];
                document.querySelectorAll('.input-cantidad').forEach(function(inp) {
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
                fd.append('accion',            'registrar');
                fd.append('nombre',            document.getElementById('nombre').value);
                fd.append('apellido',          document.getElementById('apellido').value);
                fd.append('carnet',            document.getElementById('carnet').value);
                fd.append('fecha_nacimiento',  document.getElementById('fecha_nacimiento').value);
                fd.append('edad',              document.getElementById('edad').value);
                fd.append('celular',           document.getElementById('celular').value);
                fd.append('ministerio_id',     document.getElementById('ministerio_id').value);
                fd.append('iglesia_id',        document.getElementById('iglesia_id').value);
                fd.append('distrito_id',       document.getElementById('distrito_id').value);
                fd.append('tipo_inscrito_id',  document.getElementById('tipo_inscrito_id').value);
                fd.append('paquete_id',        radio.value);
                fd.append('regalo_id',         document.getElementById('regalo_id').value);
                fd.append('comprobante_arch',  formReg.dataset.comprobante);
                fd.append('productos_json',    JSON.stringify(prods));

                btnReg.disabled  = true;
                btnReg.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Registrando...';

                fetch('guardar_inscripcion.php', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.ok) {
                        mostrarExito(
                            document.getElementById('nombre').value,
                            document.getElementById('apellido').value,
                            data.msg
                        );
                    } else {
                        alert('Error: ' + data.msg);
                        btnReg.disabled  = false;
                        btnReg.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar Inscripcion';
                    }
                })
                .catch(function() {
                    btnReg.disabled  = false;
                    btnReg.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar Inscripcion';
                    alert('Error de conexion. Intenta de nuevo.');
                });
            });
        }

        /* ---- mensaje de exito final ---- */
        function mostrarExito(nombre, apellido, msg) {
            var sec = document.querySelector('.seccion.contenedor');
            var div = document.createElement('div');
            div.className = 'mensaje-registro-exitoso';
            div.innerHTML =
                '<div class="mre-icono"><i class="fa-solid fa-circle-check"></i></div>' +
                '<h3>Inscripcion enviada, ' + nombre + ' ' + apellido + '!</h3>' +
                '<p>' + msg + '</p>' +
                '<div class="mre-nota">' +
                '<i class="fa-solid fa-envelope"></i> ' +
                'Se envio un aviso a la tesorera. Ella verificara tu comprobante y confirmara tu inscripcion.' +
                '</div>';
            sec.innerHTML = '';
            sec.appendChild(div);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        /* ---- buscador de estado ---- */
        var btnBuscar = document.getElementById('btn-buscar');
        if (btnBuscar) {
            btnBuscar.addEventListener('click', function() {
                var busqueda  = document.getElementById('buscar-inscrito').value.trim();
                var resultado = document.getElementById('resultado-busqueda');

                if (!busqueda) {
                    resultado.innerHTML = '<p style="color:#da002b;text-align:center;">Ingresa tu carnet o celular</p>';
                    return;
                }

                btnBuscar.disabled  = true;
                btnBuscar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Buscando...';

                var fd = new FormData();
                fd.append('accion',   'buscar_estado');
                fd.append('busqueda', busqueda);

                fetch('guardar_inscripcion.php', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    btnBuscar.disabled  = false;
                    btnBuscar.innerHTML = '<i class="fa-solid fa-search"></i> Consultar Estado';

                    if (data.ok) {
                        var d   = data.datos;
                        var est = d.estado_pago === 'confirmado'
                            ? '<span class="estado-confirmado"><i class="fa-solid fa-circle-check"></i> CONFIRMADO</span>'
                            : '<span class="estado-pendiente"><i class="fa-solid fa-clock"></i> PENDIENTE — en espera de verificacion por la tesorera</span>';

                        resultado.innerHTML =
                            '<div class="card-resultado-estado">' +
                            '<p><strong><i class="fa-solid fa-user"></i> ' + d.nombre + ' ' + d.apellido + '</strong></p>' +
                            '<p><i class="fa-solid fa-id-card"></i> Carnet: ' + d.carnet + '</p>' +
                            '<p><i class="fa-solid fa-box"></i> Paquete: ' + d.paquete + '</p>' +
                            '<p><i class="fa-solid fa-calendar"></i> Registrado: ' + d.fecha_pago + '</p>' +
                            '<p>Estado: ' + est + '</p>' +
                            (d.estado_pago === 'pendiente'
                                ? '<p class="aviso-pendiente-msg">Para confirmar si tu inscripcion fue aprobada pregunta a tu lider local o distrital.</p>'
                                : '<p class="aviso-confirmado-msg">Tu inscripcion esta confirmada. Nos vemos en el encuentro!</p>'
                            ) +
                            '</div>';
                    } else {
                        resultado.innerHTML =
                            '<div class="aviso-inscrito-existente" style="display:block;">' +
                            '<i class="fa-solid fa-circle-info"></i> ' + data.msg +
                            '</div>';
                    }
                })
                .catch(function() {
                    btnBuscar.disabled  = false;
                    btnBuscar.innerHTML = '<i class="fa-solid fa-search"></i> Consultar Estado';
                    resultado.innerHTML = '<p style="color:#da002b">Error de conexion. Intenta de nuevo.</p>';
                });
            });
        }

        /* ==========================================
           VALIDACIONES
           ========================================== */
        function validar() {
            var ok = true;

            function req(id, errId, msg) {
                var el = document.getElementById(id);
                if (!el || el.value.trim() === '') {
                    mostrarError(errId, msg);
                    ok = false;
                }
            }

            req('nombre',          'err-nombre',    'El nombre es obligatorio');
            req('apellido',        'err-apellido',  'El apellido es obligatorio');
            req('ministerio_id',   'err-ministerio','Selecciona tu ministerio');
            req('iglesia_id',      'err-iglesia',   'Selecciona tu iglesia');
            req('distrito_id',     'err-distrito',  'Selecciona tu distrito');
            req('tipo_inscrito_id','err-tipo',      'Selecciona el tipo de inscrito');
            req('regalo_id',       'err-regalo',    'Selecciona un regalo');

            /* carnet */
            var carnet = document.getElementById('carnet');
            if (!carnet || carnet.value.trim() === '') {
                mostrarError('err-carnet', 'El carnet es obligatorio');
                ok = false;
            } else if (!/^\d{6,8}(-\d{1,2}[A-Za-z]?)?$/.test(carnet.value.trim())) {
                mostrarError('err-carnet', 'Formato invalido. Ej: 1234567 o 123456-1A');
                ok = false;
            }

            /* celular */
            var cel = document.getElementById('celular');
            if (!cel || cel.value.trim() === '') {
                mostrarError('err-celular', 'El celular es obligatorio');
                ok = false;
            } else if (!/^[67]\d{7}$/.test(cel.value.trim())) {
                mostrarError('err-celular', 'Celular boliviano invalido. Ej: 68319277');
                ok = false;
            }

            /* fecha nacimiento */
            var fecha = document.getElementById('fecha_nacimiento');
            if (!fecha || fecha.value === '') {
                mostrarError('err-fecha', 'La fecha de nacimiento es obligatoria');
                ok = false;
            } else if (new Date(fecha.value) >= new Date()) {
                mostrarError('err-fecha', 'La fecha no puede ser hoy o en el futuro');
                ok = false;
            }

            /* paquete */
            if (!document.querySelector('input[name="paquete"]:checked')) {
                mostrarError('err-paquete', 'Debes elegir un paquete');
                ok = false;
            }

            /* tallas de productos con cantidad > 0 */
            document.querySelectorAll('.input-cantidad').forEach(function(inp) {
                var cant = parseInt(inp.value) || 0;
                if (cant > 0) {
                    var card  = inp.closest('.card-producto');
                    var talla = card.querySelector('.select-talla');
                    var errT  = card.querySelector('.error-talla');
                    if (talla && talla.value === '') {
                        if (errT) {
                            errT.textContent   = 'Selecciona una talla para ' + inp.dataset.nombre;
                            errT.style.display = 'block';
                        }
                        talla.style.borderColor = '#da002b';
                        ok = false;
                    }
                }
            });

            /* scroll al primer error */
            if (!ok) {
                var primero = document.querySelector('.campo-error[style*="block"]');
                if (primero) primero.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            return ok;
        }

        /* ---- helpers de errores ---- */
        function mostrarError(id, msg) {
            var el = document.getElementById(id);
            if (el) { el.textContent = msg; el.style.display = 'block'; }
        }

        function limpiarError(id) {
            var el = document.getElementById(id);
            if (el) { el.textContent = ''; el.style.display = 'none'; }
        }

        function limpiarErrores() {
            document.querySelectorAll('.campo-error').forEach(function(el) {
                el.textContent   = '';
                el.style.display = 'none';
            });
            document.querySelectorAll('.select-talla').forEach(function(el) {
                el.style.borderColor = '';
            });
        }

    }); /* fin DOMContentLoaded */

})();