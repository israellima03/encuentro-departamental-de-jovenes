/* ============================================================
   CONFERENCIA.JS — Preguntas anonimas del publico
   ============================================================ */

document.querySelectorAll('.form-pregunta').forEach(function(form){
    var textarea  = form.querySelector('.pregunta-texto');
    var contador  = form.querySelector('.pregunta-contador');
    var msgDiv    = form.querySelector('.pregunta-msg');
    var idEvento  = form.dataset.evento;

    /* ── CONTADOR DE CARACTERES ── */
    if(textarea && contador){
        textarea.addEventListener('input', function(){
            var len = this.value.length;
            contador.textContent = len + ' / 500';
            /* cambia color cuando se acerca al limite */
            contador.style.color = len > 450 ? '#da002b' : '#999';
        });
    }

    /* ── SUBMIT ── */
    form.addEventListener('submit', function(e){
        e.preventDefault();

        var pregunta = textarea ? textarea.value.trim() : '';
        var btn      = form.querySelector('.btn-enviar-pregunta');

        /* limpiar error previo */
        if(textarea) textarea.style.borderColor = '';

        /* validacion */
        if(!pregunta){
            if(textarea) textarea.style.borderColor = '#da002b';
            mostrarMsg(msgDiv, 'Escribe tu pregunta antes de enviar.', 'error');
            return;
        }

        /* estado loading */
        btn.disabled  = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando...';

        var fd = new FormData();
        fd.append('accion',    'enviar_pregunta');
        fd.append('id_evento', idEvento);
        fd.append('pregunta',  pregunta);

        fetch('guardar_pregunta.php', { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(data){
            btn.disabled  = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Enviar pregunta';

            if(data.ok){
                mostrarMsg(msgDiv, '¡Pregunta enviada de forma anonima!', 'ok');
                /* limpiar el textarea */
                if(textarea){
                    textarea.value             = '';
                    textarea.style.borderColor = '';
                }
                if(contador){
                    contador.textContent = '0 / 500';
                    contador.style.color = '#999';
                }
            } else {
                mostrarMsg(msgDiv, data.msg || 'Error al enviar. Intenta de nuevo.', 'error');
            }
        })
        .catch(function(){
            btn.disabled  = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Enviar pregunta';
            mostrarMsg(msgDiv, 'Error de conexion. Intenta de nuevo.', 'error');
        });
    });
});

/* ── HELPER MENSAJES ── */
function mostrarMsg(div, msg, tipo){
    if(!div) return;
    div.style.display = 'flex';
    div.className     = 'pregunta-msg pregunta-msg-' + tipo;
    div.innerHTML     =
        '<i class="fa-solid ' +
        (tipo === 'ok' ? 'fa-circle-check' : 'fa-circle-xmark') +
        '"></i> ' + msg;

    /* ocultar automaticamente despues de 5 segundos */
    setTimeout(function(){
        div.style.display = 'none';
    }, 5000);
}