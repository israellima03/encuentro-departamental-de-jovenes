<!-- MODAL VER INSCRITO -->
  <div class="modal-overlay" id="modal-overlay">
    <div class="modal" id="modal-inscrito">
      <div class="modal-body" id="modal-body">
        <!-- Se rellena por JS -->
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" id="btn-modal-cancelar">Cerrar</button>
        <button class="btn-success" id="btn-modal-confirmar" style="display:none;">
          <i class="fa-solid fa-check"></i> Confirmar Pago
        </button>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div class="toast" id="toast"></div>

<?php
if(!isset($v)){
  $v = function($archivo){
    $ruta = $_SERVER['DOCUMENT_ROOT'] . '/admin/' . $archivo;
    return file_exists($ruta) ? filemtime($ruta) : time();
  };
}
?>
  <script src="js/admin.js?v=<?php echo $v('js/admin.js'); ?>"></script>

<?php if(!empty($js_pagina)): ?>
  <?php foreach($js_pagina as $js): ?>
  <script src="js/<?php echo htmlspecialchars($js); ?>?v=<?php echo $v('js/'.htmlspecialchars($js)); ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>

</body>
</html>