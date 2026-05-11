<?php
if(!isset($cfg))    $cfg     = get_config($conn);
if(!isset($redes))  $redes   = get_redes($conn);
$noticias  = get_noticias($conn);
$pie_titulo = $cfg['footer_titulo']      ?? 'Sobre Nosotros';
$pie_desc   = $cfg['footer_descripcion'] ?? '';

/* $v puede venir del header, si no, la definimos aquí */
if(!isset($v)){
  $v = function($archivo){
    $ruta = $_SERVER['DOCUMENT_ROOT'] . '/' . $archivo;
    return file_exists($ruta) ? filemtime($ruta) : time();
  };
}
?>

<footer class="site-footer">
  <div class="contenedor">
    <div class="footer-informacion">
      <h3><?php echo htmlspecialchars($pie_titulo); ?></h3>
      <p><?php echo htmlspecialchars($pie_desc); ?></p>
    </div>
    <div class="Ultimas-tweets">
      <h3>Últimas <span>Noticias</span></h3>
      <ul>
        <?php foreach($noticias as $n): ?>
          <li><?php echo htmlspecialchars($n); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="menu">
      <h3>Redes <span>Sociales</span></h3>
      <nav class="redes-sociales">
        <?php foreach($redes as $r): ?>
          <a href="<?php echo htmlspecialchars($r['url']); ?>" target="_blank" title="<?php echo htmlspecialchars($r['nombre']); ?>">
            <i class="<?php echo htmlspecialchars($r['icono']); ?>"></i>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>
  </div>
  <p class="copyright">
    Todos los derechos Reservados Lima technology cel 68319277
  </p>
</footer>

<script src="js/vendor/modernizr-3.8.0.min.js"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-3.4.1.min.js"><\/script>')</script>
<script src="js/plugins.js?v=<?php echo $v('js/plugins.js'); ?>"></script>
<script src="js/main.js?v=<?php echo $v('js/main.js'); ?>"></script>

<?php if(!empty($js_pagina)): ?>
  <?php foreach($js_pagina as $js): ?>
  <script src="js/<?php echo htmlspecialchars($js); ?>?v=<?php echo $v('js/'.htmlspecialchars($js)); ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>

<script>
  window.ga = function () { ga.q.push(arguments) }; ga.q = []; ga.l = +new Date;
  ga('create', 'UA-XXXXX-Y', 'auto'); ga('set','transport','beacon'); ga('send', 'pageview')
</script>
<script src="https://www.google-analytics.com/analytics.js" async></script>
</body>
</html>