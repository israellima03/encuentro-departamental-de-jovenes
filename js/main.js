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

        /* ---- PROGRAMA POR DIA (solo index.php) ---- */
        var menuPrograma = document.querySelector('.menu-programa');

        if (menuPrograma) {
            var enlacesPrograma   = document.querySelectorAll('.menu-programa a');
            var seccionesPrograma = document.querySelectorAll('.info-curso');

            seccionesPrograma.forEach(function(sec) {
                sec.classList.add('hidden');
            });

            var viernes = document.getElementById('viernes');
            if (viernes) viernes.classList.remove('hidden');

            if (enlacesPrograma[0]) enlacesPrograma[0].classList.add('activo');

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

        var form = document.getElementById('registro');
        if (!form) return;

        /* ---- CALCULO AUTOMATICO DE EDAD ---- */
        var inputFecha = document.getElementById('fecha_nacimiento');
        var inputEdad  = document.getElementById('edad');

        if(inputFecha){
            inputFecha.addEventListener('change', function(){
                var hoy     = new Date();
                var nacim   = new Date(this.value);
                var edad    = hoy.getFullYear() - nacim.getFullYear();
                var mes     = hoy.getMonth() - nacim.getMonth();
                if(mes < 0 || (mes === 0 && hoy.getDate() < nacim.getDate())) edad--;
                inputEdad.value = edad > 0 ? edad : '';
            });
        }

        /* ---- MOSTRAR TALLA CUANDO CANTIDAD > 0 ---- */
        document.querySelectorAll('.input-cantidad').forEach(function(inp){
            inp.addEventListener('change', function(){
                var card  = this.closest('.card-producto');
                var talla = card.querySelector('.producto-talla');
                if(talla){
                    talla.style.display = parseInt(this.value) > 0 ? 'block' : 'none';
                }
            });
        });

        /* ---- CALCULO DEL TOTAL ---- */
        var btnCalcular = document.getElementById('btn-calcular');
        if(btnCalcular){
            btnCalcular.addEventListener('click', function(){
                if(!validarTodo()) return;

                var paqueteRadio = document.querySelector('input[name="paquete"]:checked');
                if(!paqueteRadio){
                    mostrarError('error-paquete', 'Debes elegir un paquete');
                    return;
                }

                var totalPaquete = parseFloat(paqueteRadio.dataset.precio) || 0;
                var nombrePaq    = paqueteRadio.dataset.nombre;
                var items        = [];
                var total        = totalPaquete;

                items.push('Paquete: ' + nombrePaq + ' — Bs. ' + totalPaquete.toFixed(2));

                document.querySelectorAll('.input-cantidad').forEach(function(inp){
                    var cant = parseInt(inp.value) || 0;
                    if(cant > 0){
                        var precio = parseFloat(inp.dataset.precio) || 0;
                        var nombre = inp.dataset.nombre;
                        var subtotal = cant * precio;
                        total += subtotal;
                        items.push(cant + 'x ' + nombre + ' — Bs. ' + subtotal.toFixed(2));
                    }
                });

                var lista = document.getElementById('lista-productos');
                var suma  = document.getElementById('suma-total');
                lista.style.display = 'block';
                lista.innerHTML = '';
                items.forEach(function(i){ lista.innerHTML += '<p>' + i + '</p>'; });
                suma.innerHTML = 'Bs. ' + total.toFixed(2);

                document.getElementById('seccion-qr').style.display = 'block';
                document.getElementById('seccion-qr').scrollIntoView({behavior:'smooth'});
            });
        }

        /* ---- HABILITA BTN SUBIR ---- */
        var inputComp = document.getElementById('comprobante');
        var btnSubir  = document.getElementById('btn-subir');

        if(inputComp){
            inputComp.addEventListener('change', function(){
                btnSubir.disabled = this.files.length === 0;
            });
        }

        /* ---- SUBE COMPROBANTE ---- */
        if(btnSubir){
            btnSubir.addEventListener('click', function(){
                var archivo  = document.getElementById('comprobante').files[0];
                var nombre   = document.getElementById('nombre').value;
                var apellido = document.getElementById('apellido').value;

                if(!archivo){
                    mostrarError('error-comprobante', 'Selecciona un archivo');
                    return;
                }

                var formData = new FormData();
                formData.append('comprobante', archivo);
                formData.append('nombre_inscrito', nombre);
                formData.append('apellido_inscrito', apellido);

                btnSubir.disabled    = true;
                btnSubir.textContent = 'Subiendo...';

                fetch('guardar_inscripcion.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(r){ return r.json(); })
                .then(function(data){
                    var msg = document.getElementById('mensaje-subida');
                    msg.style.display = 'block';

                    if(data.ok){
                        msg.className = 'mensaje-exito';
                        msg.innerHTML = '<i class="fa-solid fa-check-circle"></i> ' + data.msg;
                        form.dataset.comprobante = data.archivo;
                        document.getElementById('btnRegistro').disabled = false;
                    } else {
                        msg.className = 'mensaje-error';
                        msg.innerHTML = '<i class="fa-solid fa-times-circle"></i> ' + data.msg;
                        btnSubir.disabled = false;
                        btnSubir.textContent = 'Subir Comprobante';
                    }
                });
            });
        }

        /* ---- SUBMIT FINAL ---- */
        var btnRegistro = document.getElementById('btnRegistro');
        if(btnRegistro){
            btnRegistro.addEventListener('click', function(e){
                e.preventDefault();
                if(!validarTodo()) return;

                var paqueteRadio = document.querySelector('input[name="paquete"]:checked');
                var datos = {
                    accion: 'registrar',
                    nombre: document.getElementById('nombre').value,
                    apellido: document.getElementById('apellido').value,
                    carnet: document.getElementById('carnet').value,
                    fecha_nacimiento: document.getElementById('fecha_nacimiento').value,
                    edad: document.getElementById('edad').value,
                    celular: document.getElementById('celular').value,
                    ministerio_id: document.getElementById('ministerio_id').value,
                    iglesia_id: document.getElementById('iglesia_id').value,
                    distrito_id: document.getElementById('distrito_id').value,
                    tipo_inscrito_id: document.getElementById('tipo_inscrito_id').value,
                    paquete_id: paqueteRadio ? paqueteRadio.value : '',
                    comprobante_archivo: form.dataset.comprobante || ''
                };

                var fd = new FormData();
                Object.keys(datos).forEach(function(k){ fd.append(k, datos[k]); });

                fetch('guardar_inscripcion.php', { method:'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(data){
                    if(data.ok){
                        alert(data.msg);
                        form.reset();
                    } else {
                        alert('Error: ' + data.msg);
                    }
                });
            });
        }

        function validarTodo(){
            return true;
        }

        function mostrarError(id, msg){
            var el = document.getElementById(id);
            if(el){ el.textContent = msg; el.style.display = 'block'; }
        }

        function limpiarTodosErrores(){}
    });

})();