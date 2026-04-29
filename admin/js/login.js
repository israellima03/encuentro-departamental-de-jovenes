(function () {
  'use strict';

  /* ── ELEMENTOS ── */
  var form      = document.getElementById('form-login');
  var btnLogin  = document.getElementById('btn-login');
  var btnToggle = document.getElementById('btn-toggle-pass');
  var inputPass = document.getElementById('password');
  var icoPass   = document.getElementById('ico-pass');
  var msgError  = document.getElementById('msg-error');
  var msgErrorTxt = document.getElementById('msg-error-txt');

  /* ── VER / OCULTAR CONTRASEÑA ── */
  if (btnToggle && inputPass) {
    btnToggle.addEventListener('click', function () {
      var esPw = inputPass.type === 'password';
      inputPass.type = esPw ? 'text' : 'password';
      if (icoPass) {
        icoPass.className = esPw ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
      }
    });
  }

  /* ── SUBMIT — envía el form normalmente a PHP ── */
  if (form) {
    form.addEventListener('submit', function (e) {
      /* NO hacemos e.preventDefault() — dejamos que el form 
         se envíe normalmente al servidor PHP por POST */

      var inputUser = document.getElementById('usuario');
      var ok = true;

      if (!inputUser || inputUser.value.trim() === '') {
        e.preventDefault();
        mostrarMsgError('El usuario es obligatorio');
        ok = false;
      }

      if (!inputPass || inputPass.value.trim() === '') {
        e.preventDefault();
        mostrarMsgError('La contraseña es obligatoria');
        ok = false;
      }

      if (ok && btnLogin) {
        /* mostrar spinner mientras carga */
        btnLogin.disabled = true;
        var btnTxt = btnLogin.querySelector('.btn-txt');
        var btnIco = btnLogin.querySelector('.btn-ico');
        var btnLoader = document.getElementById('btn-loader');
        if (btnTxt)    btnTxt.style.display    = 'none';
        if (btnIco)    btnIco.style.display    = 'none';
        if (btnLoader) btnLoader.style.display = 'inline-block';
      }
    });
  }

  /* ── HELPERS ── */
  function mostrarMsgError(msg) {
    if (!msgError) return;
    if (msgErrorTxt) msgErrorTxt.textContent = msg;
    msgError.style.display = 'flex';
  }

  /* limpiar error al escribir */
  document.querySelectorAll('.field-input').forEach(function (el) {
    el.addEventListener('input', function () {
      if (msgError) msgError.style.display = 'none';
    });
  });

  /* olvidé contraseña */
  var linkOlvide = document.querySelector('.link-olvide');
  if (linkOlvide) {
    linkOlvide.addEventListener('click', function (e) {
      e.preventDefault();
      alert('Contacta al administrador del sistema para restablecer tu contraseña.');
    });
  }

})();