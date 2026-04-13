(function() {
    "use strict";

    /* ========================================
       MENU HAMBURGUESA - abre y cierra el menu
       en modo celular al hacer click
       ======================================== */
    var menuMovil = document.querySelector('.menu-movil');
    var navegacion = document.querySelector('.navegacion-principal');

    if (menuMovil) {
        menuMovil.addEventListener('click', function() {

            /* alterna la clase activo en el menu */
            navegacion.classList.toggle('activo');

            /* cambia el icono de hamburguesa a X */
            menuMovil.classList.toggle('activo');
        });
    }


    /* ========================================
       CONTADOR REGRESIVO - cuenta hacia el
       10 de julio de 2026 a las 14:00 horas
       se actualiza cada segundo
       ======================================== */
    var fechaEvento = new Date('2026-07-10T14:00:00');

    /* selecciona los elementos del contador en el HTML */
    var elementoDias     = document.getElementById('dias');
    var elementoHoras    = document.getElementById('horas');
    var elementoMinutos  = document.getElementById('minutos');
    var elementoSegundos = document.getElementById('segundos');
    function actualizarContador() {
        var ahora       = new Date();
        var diferencia  = fechaEvento - ahora;  /* milisegundos restantes */

        /* si ya paso el evento muestra ceros */
        if (diferencia <= 0) {
            if (elementoDias)     elementoDias.textContent     = '0';
            if (elementoHoras)    elementoHoras.textContent    = '0';
            if (elementoMinutos)  elementoMinutos.textContent  = '0';
            if (elementoSegundos) elementoSegundos.textContent = '0';
            return;
        }

        /* convierte milisegundos a dias, horas, minutos, segundos */
        var dias     = Math.floor(diferencia / (1000 * 60 * 60 * 24));
        var horas    = Math.floor((diferencia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutos  = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
        var segundos = Math.floor((diferencia % (1000 * 60)) / 1000);

        /* actualiza el HTML con los valores calculados */
        if (elementoDias)     elementoDias.textContent     = dias;
        if (elementoHoras)    elementoHoras.textContent    = horas;
        if (elementoMinutos)  elementoMinutos.textContent  = minutos;
        if (elementoSegundos) elementoSegundos.textContent = segundos;
    }

    /* ejecuta el contador inmediatamente y luego cada segundo */
    if (document.querySelector('.cuenta-regresiva')) {
        actualizarContador();
        setInterval(actualizarContador, 1000);
    }


    /* ========================================
       VALIDACIONES DEL FORMULARIO DE REGISTRO
       solo se activa si estamos en registro.html
       ======================================== */
    document.addEventListener('DOMContentLoaded', function() {

        var regalo = document.getElementById('regalo');
        if (!regalo) return; /* si no existe el form salimos */

        var nombre      = document.getElementById('nombre');
        var apellido    = document.getElementById('apellido');
        var celular     = document.getElementById('celular');
        var carnet      = document.getElementById('carnet');
        var ministerio  = document.getElementById('ministerio');
        var iglesia     = document.getElementById('iglesia');
        var distrito    = document.getElementById('distrito');
        var camisas     = document.getElementById('camisa_evento');
        var etiquetas   = document.getElementById('etiquetas');
        var calcular        = document.getElementById('calcular');
        var errorDiv        = document.getElementById('error');
        var botonRegistro   = document.getElementById('btnRegistro');
        var lista_productos = document.getElementById('lista-productos');
        var suma            = document.getElementById('suma-total');

        nombre.addEventListener('blur', validarTexto);
        apellido.addEventListener('blur', validarTexto);
        ministerio.addEventListener('blur', validarTexto);
        iglesia.addEventListener('blur', validarTexto);
        distrito.addEventListener('blur', validarTexto);
        celular.addEventListener('blur', validarCelular);
        carnet.addEventListener('blur', validarCarnet);
        calcular.addEventListener('click', calcularMontos);

        var radios = document.querySelectorAll('input[name="paquete"]');
        radios.forEach(function(radio) {
            radio.addEventListener('change', limpiarErrorPaquete);
        });

        function validarTexto() {
            if (this.value.trim() === '') {
                mostrarError(this, 'Este campo es obligatorio');
            } else if (this.value.trim().length < 2) {
                mostrarError(this, 'Debe tener al menos 2 caracteres');
            } else {
                limpiarError(this);
            }
        }

        function validarCelular() {
            var valor = this.value.trim();
            if (valor === '') {
                mostrarError(this, 'El celular es obligatorio');
                return;
            }
            if (!/^\d+$/.test(valor)) {
                mostrarError(this, 'El celular solo debe contener numeros');
                return;
            }
            if (valor.length < 7 || valor.length > 8) {
                mostrarError(this, 'El celular debe tener 7 u 8 digitos');
                return;
            }
            limpiarError(this);
        }

        function validarCarnet() {
            var valor = this.value.trim();
            if (valor === '') {
                mostrarError(this, 'El carnet es obligatorio');
                return;
            }
            if (valor.length < 5) {
                mostrarError(this, 'El carnet debe tener al menos 5 caracteres');
                return;
            }
            if (valor.length > 10) {
                mostrarError(this, 'El carnet no puede tener mas de 10 caracteres');
                return;
            }
            if (!/^\d{5,9}[-]?[a-zA-Z]?$/.test(valor)) {
                mostrarError(this, 'Formato invalido. Ejemplo: 1234567 o 1234567A');
                return;
            }
            limpiarError(this);
        }

        function validarTodo() {
            var valido = true;
            var camposTexto = [nombre, apellido, ministerio, iglesia, distrito];
            camposTexto.forEach(function(campo) {
                if (campo.value.trim() === '' || campo.value.trim().length < 2) {
                    mostrarError(campo, 'Este campo es obligatorio');
                    valido = false;
                }
            });
            var valorCelular = celular.value.trim();
            if (valorCelular === '' || !/^\d{7,8}$/.test(valorCelular)) {
                mostrarError(celular, 'Ingresa un celular valido de 7 u 8 digitos');
                valido = false;
            }
            var valorCarnet = carnet.value.trim();
            if (valorCarnet === '' || !/^\d{5,9}[-]?[a-zA-Z]?$/.test(valorCarnet)) {
                mostrarError(carnet, 'Ingresa un carnet valido');
                valido = false;
            }
            var paqueteElegido = document.querySelector('input[name="paquete"]:checked');
            if (!paqueteElegido) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = 'Debes elegir un paquete';
                valido = false;
            }
            return valido;
        }

        function calcularMontos(event) {
            event.preventDefault();
            if (!validarTodo()) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = 'Por favor completa todos los campos correctamente';
                return;
            }
            if (regalo.value === '') {
                alert('Debes elegir un regalo');
                regalo.focus();
                return;
            }
            var paqueteElegido = document.querySelector('input[name="paquete"]:checked');
            var valorPaquete   = parseInt(paqueteElegido.value, 10) || 0;
            var nombrePaquete  = paqueteElegido.closest('.tabla-precio').querySelector('h3').textContent;
            var cantCamisas    = parseInt(camisas.value, 10) || 0;
            var canEtiquetas   = parseInt(etiquetas.value, 10) || 0;
            var totalCamisas   = cantCamisas * 90 * 0.93;
            var totalEtiquetas = canEtiquetas * 10;
            var totalPagar     = valorPaquete + totalCamisas + totalEtiquetas;
            var listadoProductos = [];
            listadoProductos.push('Paquete: ' + nombrePaquete + ' — Bs. ' + valorPaquete);
            if (cantCamisas >= 1) {
                listadoProductos.push(cantCamisas + ' Polera(s) — Bs. ' + totalCamisas.toFixed(2) + ' (7% dto.)');
            }
            if (canEtiquetas >= 1) {
                listadoProductos.push(canEtiquetas + ' Paquete(s) Etiquetas — Bs. ' + totalEtiquetas);
            }
            listadoProductos.push('Regalo: ' + regalo.options[regalo.selectedIndex].text);
            lista_productos.style.display = 'block';
            lista_productos.innerHTML = '';
            listadoProductos.forEach(function(item) {
                lista_productos.innerHTML += '<p>' + item + '</p>';
            });
            suma.innerHTML = 'Bs. ' + totalPagar.toFixed(2);
        }

        function mostrarError(campo, mensaje) {
            campo.style.border = '2px solid #da002b';
            campo.style.backgroundColor = '#fff5f5';
            var spanError = campo.parentNode.querySelector('.campo-error');
            if (!spanError) {
                spanError = document.createElement('span');
                spanError.classList.add('campo-error');
                campo.parentNode.appendChild(spanError);
            }
            spanError.textContent = mensaje;
            spanError.style.color = '#da002b';
            spanError.style.fontSize = '0.8em';
            spanError.style.display = 'block';
            spanError.style.marginTop = '4px';
        }

        function limpiarError(campo) {
            campo.style.border = '1px solid #e1e1e1';
            campo.style.backgroundColor = '#fff';
            var spanError = campo.parentNode.querySelector('.campo-error');
            if (spanError) {
                spanError.textContent = '';
                spanError.style.display = 'none';
            }
        }

        function limpiarErrorPaquete() {
            errorDiv.style.display = 'none';
            errorDiv.innerHTML = '';
        }

        botonRegistro.addEventListener('click', function(event) {
            event.preventDefault();
            if (!validarTodo()) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = 'Completa todos los campos antes de pagar';
                return;
            }
            if (suma.innerHTML === '') {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = 'Primero debes calcular el total';
                return;
            }
            alert('Registro completado. Total a pagar: ' + suma.innerHTML);
            /* document.getElementById('registro').submit(); */
        });

    }); /* fin DOMContentLoaded */

})();