<?php 
include_once 'includes/templates/header.php';
require_once('includes/funciones/bd_conexion.php');

/* ============================================
   CARGA DE COMBOS Y DATOS
   ============================================ */
$ministerios = [];
$res = $conn->query("SELECT id, nombre FROM ministerios ORDER BY nombre");
while($r = $res->fetch_assoc()) $ministerios[] = $r;

$iglesias = [];
$res = $conn->query("SELECT id, nombre FROM iglesias ORDER BY nombre");
while($r = $res->fetch_assoc()) $iglesias[] = $r;

$distritos = [];
$res = $conn->query("SELECT id, nombre FROM distritos ORDER BY nombre");
while($r = $res->fetch_assoc()) $distritos[] = $r;

$tipos = [];
$res = $conn->query("SELECT id, nombre FROM tipos_inscrito ORDER BY id");
while($r = $res->fetch_assoc()) $tipos[] = $r;

$paquetes = [];
$res = $conn->query("
    SELECT p.id, p.nombre, p.precio, p.cupo_total, p.cupos_disponibles,
           d.nombre        AS descuento_nombre,
           d.porcentaje    AS descuento_porcentaje,
           d.fecha_fin,
           ROUND(p.precio - (p.precio * COALESCE(d.porcentaje,0) / 100), 2) AS precio_final
    FROM paquetes p
    LEFT JOIN paquete_descuentos pd ON p.id = pd.paquete_id
    LEFT JOIN descuentos d ON pd.descuento_id = d.id AND d.activo = 1
    ORDER BY p.precio
");
while($r = $res->fetch_assoc()) $paquetes[] = $r;

$productos = [];
$res = $conn->query("SELECT id, nombre, precio, tipo, cupos_disponibles, imagen FROM productos WHERE cupos_disponibles > 0 ORDER BY nombre");
while($r = $res->fetch_assoc()) $productos[] = $r;

$regalos = [];
$res = $conn->query("SELECT id, nombre FROM regalos WHERE cupos_disponibles > 0 ORDER BY nombre");
while($r = $res->fetch_assoc()) $regalos[] = $r;
?>

<section class="seccion contenedor">
  <h2>Inscripcion al Encuentro</h2>

  <div class="aviso-qr">
    <i class="fa-solid fa-qrcode"></i>
    El pago es <strong>unicamente por QR / Banca Movil</strong>. 
    Completa todos los datos, escanea el QR, sube tu comprobante y tu inscripcion quedara como <strong>PENDIENTE</strong> hasta que la tesorera la confirme.
  </div>

  <form id="form-registro" novalidate>

    <!-- ====================== DATOS PERSONALES ====================== -->
    <div class="seccion-form caja">
      <h4><i class="fa-solid fa-user"></i> Datos Personales</h4>
      <div class="grid-campos">

        <div class="campo">
          <label>Nombre <span class="req">*</span></label>
          <input type="text" id="nombre" placeholder="Tu nombre">
          <span class="campo-error" id="err-nombre"></span>
        </div>

        <div class="campo">
          <label>Apellido <span class="req">*</span></label>
          <input type="text" id="apellido" placeholder="Tu apellido">
          <span class="campo-error" id="err-apellido"></span>
        </div>

        <div class="campo">
          <label>Carnet de Identidad <span class="req">*</span></label>
          <input type="text" id="carnet" placeholder="Ej: 1234567 o 1234567A">
          <span class="campo-error" id="err-carnet"></span>
        </div>

        <div class="campo">
          <label>Fecha de Nacimiento <span class="req">*</span></label>
          <input type="date" id="fecha_nacimiento">
          <span class="campo-error" id="err-fecha"></span>
        </div>

        <div class="campo">
          <label>Edad</label>
          <input type="text" id="edad" placeholder="Se calcula automaticamente" readonly>
        </div>

        <div class="campo">
          <label>Celular <span class="req">*</span></label>
          <input type="text" id="celular" placeholder="Ej: 70000000">
          <span class="campo-error" id="err-celular"></span>
        </div>

        <div class="campo">
          <label>Ministerio <span class="req">*</span></label>
          <select id="ministerio_id">
            <option value="">-- Selecciona tu ministerio --</option>
            <?php foreach($ministerios as $m): ?>
              <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?></option>
            <?php endforeach; ?>
          </select>
          <span class="campo-error" id="err-ministerio"></span>
        </div>

        <div class="campo">
          <label>Iglesia <span class="req">*</span></label>
          <select id="iglesia_id">
            <option value="">-- Selecciona tu iglesia --</option>
            <?php foreach($iglesias as $i): ?>
              <option value="<?php echo $i['id']; ?>"><?php echo htmlspecialchars($i['nombre']); ?></option>
            <?php endforeach; ?>
          </select>
          <span class="campo-error" id="err-iglesia"></span>
        </div>

        <div class="campo">
          <label>Distrito <span class="req">*</span></label>
          <select id="distrito_id">
            <option value="">-- Selecciona tu distrito --</option>
            <?php foreach($distritos as $d): ?>
              <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['nombre']); ?></option>
            <?php endforeach; ?>
          </select>
          <span class="campo-error" id="err-distrito"></span>
        </div>

        <div class="campo">
          <label>Tipo de Inscrito <span class="req">*</span></label>
          <select id="tipo_inscrito_id">
            <option value="">-- Selecciona tu tipo --</option>
            <?php foreach($tipos as $t): ?>
              <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
            <?php endforeach; ?>
          </select>
          <span class="campo-error" id="err-tipo"></span>
        </div>

      </div><!-- grid-campos -->
    </div>

    <!-- ====================== PAQUETES ====================== -->
    <div class="seccion-form">
      <h3 style="text-align:center;margin-bottom:8px;">Elige tu Paquete</h3>
      <p class="texto-paquete">Solo puedes elegir un paquete</p>
      <span class="campo-error" id="err-paquete" style="text-align:center;display:block;"></span>

      <ul class="lista-precios">
        <?php foreach($paquetes as $paq):
          $con_desc    = !empty($paq['descuento_porcentaje']);
          $pf          = $con_desc ? $paq['precio_final'] : $paq['precio'];
          $agotado     = $paq['cupos_disponibles'] <= 0;
        ?>
          <li>
            <div class="tabla-precio <?php echo $agotado ? 'agotado' : ''; ?>">

              <label class="paquete-label">
                <input type="radio" name="paquete"
                       value="<?php echo $paq['id']; ?>"
                       data-precio="<?php echo $pf; ?>"
                       data-nombre="<?php echo htmlspecialchars($paq['nombre']); ?>"
                       <?php echo $agotado ? 'disabled' : ''; ?>>
                <h3><?php echo htmlspecialchars($paq['nombre']); ?></h3>
              </label>

              <?php if($con_desc): ?>
                <p class="precio-original">Bs. <?php echo number_format($paq['precio'],2); ?></p>
                <p class="numero">Bs. <?php echo number_format($pf,2); ?></p>
                <div class="badge-descuento">
                  <i class="fa-solid fa-tag"></i>
                  <?php echo htmlspecialchars($paq['descuento_nombre']); ?> —
                  <?php echo $paq['descuento_porcentaje']; ?>% OFF
                </div>
                <p class="fecha-descuento">
                  <i class="fa-solid fa-calendar"></i>
                  Hasta: <?php echo date('d/m/Y', strtotime($paq['fecha_fin'])); ?>
                </p>
              <?php else: ?>
                <p class="numero">Bs. <?php echo number_format($pf,2); ?></p>
              <?php endif; ?>

              <p class="cupos-disponibles">
                <i class="fa-solid fa-users"></i>
                <?php echo $paq['cupos_disponibles']; ?> / <?php echo $paq['cupo_total']; ?> cupos
              </p>

              <ul class="lista-beneficios">
                <li>Recuerdo del encuentro</li>
                <li>Todas las conferencias</li>
                <li>Todos los talleres</li>
              </ul>

              <?php if($agotado): ?>
                <p class="texto-agotado">AGOTADO</p>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- ====================== PRODUCTOS ====================== -->
    <?php if(!empty($productos)): ?>
    <div class="seccion-form seccion-productos">
      <h3>Productos Adicionales</h3>
      <p class="texto-paquete">Opcional — poleras, gorras y mas</p>
      <div class="grid-productos">
        <?php foreach($productos as $prod): ?>
          <div class="card-producto">
            <div class="producto-imagen">
              <?php if(!empty($prod['imagen'])): ?>
                <img src="img/<?php echo htmlspecialchars($prod['imagen']); ?>" alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
              <?php else: ?>
                <div class="producto-sin-imagen"><i class="fa-solid fa-shirt"></i></div>
              <?php endif; ?>
            </div>
            <div class="producto-info">
              <h4><?php echo htmlspecialchars($prod['nombre']); ?></h4>
              <p class="producto-precio">Bs. <?php echo number_format($prod['precio'],2); ?></p>
              <p class="producto-cupos"><i class="fa-solid fa-box"></i> <?php echo $prod['cupos_disponibles']; ?> disponibles</p>
              <div class="producto-cantidad">
                <label>Cantidad:</label>
                <input type="number" min="0" max="<?php echo $prod['cupos_disponibles']; ?>"
                       class="input-cantidad" value="0"
                       data-id="<?php echo $prod['id']; ?>"
                       data-nombre="<?php echo htmlspecialchars($prod['nombre']); ?>"
                       data-precio="<?php echo $prod['precio']; ?>">
              </div>
              <div class="producto-talla" style="display:none;">
                <label>Talla:</label>
                <select class="select-talla" data-id="<?php echo $prod['id']; ?>">
                  <option value="">-- Talla --</option>
                  <option value="XS">XS</option>
                  <option value="S">S</option>
                  <option value="M">M</option>
                  <option value="L">L</option>
                  <option value="XL">XL</option>
                  <option value="XXL">XXL</option>
                </select>
                <span class="campo-error error-talla"></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ====================== REGALO ====================== -->
    <div class="seccion-form caja">
      <h4><i class="fa-solid fa-gift"></i> Selecciona tu Regalo</h4>
      <div class="campo" style="max-width:400px;">
        <label>Regalo <span class="req">*</span></label>
        <select id="regalo_id">
          <option value="">-- Selecciona un regalo --</option>
          <?php foreach($regalos as $r): ?>
            <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['nombre']); ?></option>
          <?php endforeach; ?>
        </select>
        <span class="campo-error" id="err-regalo"></span>
      </div>
    </div>

    <!-- ====================== RESUMEN ====================== -->
    <div class="seccion-form resumen">
      <h3>Resumen de tu Inscripcion</h3>
      <div class="caja">
        <div class="extras">
          <button type="button" id="btn-calcular" class="button" disabled>
            <i class="fa-solid fa-calculator"></i> Calcular Total y Ver QR
          </button>
          <p class="texto-paquete" id="hint-calcular">Completa todos los campos para activar este boton</p>
        </div>
        <div class="total">
          <p>Detalle:</p>
          <div id="lista-productos"></div>
          <p>Total a pagar:</p>
          <div id="suma-total"></div>
        </div>
      </div>
    </div>

    <!-- ====================== QR ====================== -->
    <div id="seccion-qr" class="seccion-qr" style="display:none;">
      <h3><i class="fa-solid fa-qrcode"></i> Realiza tu Pago por QR</h3>
      <div class="qr-contenido">

        <div class="qr-imagen-wrap">
          <p class="qr-instruccion"><i class="fa-solid fa-circle-info"></i> Escanea este QR con tu app de banca movil</p>
          <img src="img/comprobante1.jpeg" alt="QR de pago" class="qr-imagen">
          <a href="img/comprobante1.jpeg" download="QR-Encuentro.jpeg" class="button hollow">
            <i class="fa-solid fa-download"></i> Descargar QR
          </a>
        </div>

        <div class="qr-subir">
          <p class="qr-instruccion"><i class="fa-solid fa-upload"></i> Luego sube aqui tu comprobante:</p>

          <div class="campo">
            <label>Comprobante de pago <span class="req">*</span></label>
            <input type="file" id="comprobante" accept="image/*,.pdf">
            <span class="campo-error" id="err-comprobante"></span>
          </div>

          <button type="button" id="btn-subir" class="button" disabled>
            <i class="fa-solid fa-upload"></i> Subir Comprobante
          </button>

          <div id="msg-subida" style="display:none;"></div>

          <button type="button" id="btn-registrar" class="button" disabled style="margin-top:15px;width:100%;">
            <i class="fa-solid fa-check"></i> Confirmar Inscripcion
          </button>
        </div>

      </div>
    </div>

  </form><!-- fin form-registro -->

  <!-- ====================== BUSCADOR DE ESTADO ====================== -->
  <div class="seccion-form caja buscador-estado" style="margin-top:50px;">
    <h4><i class="fa-solid fa-magnifying-glass"></i> Consulta el estado de tu inscripcion</h4>
    <p style="text-align:center;color:#666;font-size:0.9em;">Ingresa tu carnet o celular para ver si tu inscripcion fue confirmada</p>
    <div class="campo" style="max-width:400px;margin:0 auto;">
      <label>Carnet o Celular</label>
      <input type="text" id="buscar-inscrito" placeholder="Ej: 1234567 o 70000000">
    </div>
    <div style="text-align:center;margin-top:10px;">
      <button type="button" id="btn-buscar" class="button hollow">
        <i class="fa-solid fa-search"></i> Consultar Estado
      </button>
    </div>
    <div id="resultado-busqueda" style="margin-top:20px;"></div>
  </div>

</section>

<?php include_once 'includes/templates/footer.php'; ?>