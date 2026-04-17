<?php 
include_once 'includes/templates/header.php';
require_once('includes/funciones/bd_conexion.php');

/* ============================================
   CARGA DE DATOS PARA LOS COMBOS Y PAQUETES
   ============================================ */

/* ministerios */
$ministerios = [];
$res = $conn->query("SELECT id, nombre FROM ministerios ORDER BY nombre");
while($r = $res->fetch_assoc()) $ministerios[] = $r;

/* iglesias */
$iglesias = [];
$res = $conn->query("SELECT id, nombre FROM iglesias ORDER BY nombre");
while($r = $res->fetch_assoc()) $iglesias[] = $r;

/* distritos */
$distritos = [];
$res = $conn->query("SELECT id, nombre FROM distritos ORDER BY nombre");
while($r = $res->fetch_assoc()) $distritos[] = $r;

/* tipos de inscrito */
$tipos = [];
$res = $conn->query("SELECT id, nombre FROM tipos_inscrito ORDER BY id");
while($r = $res->fetch_assoc()) $tipos[] = $r;

/* paquetes con descuento activo */
$paquetes = [];
$res = $conn->query("
    SELECT p.id, p.nombre, p.precio, p.cupo_total, p.cupos_disponibles,
           d.nombre AS descuento_nombre,
           d.porcentaje AS descuento_porcentaje,
           d.fecha_inicio, d.fecha_fin,
           ROUND(p.precio - (p.precio * d.porcentaje / 100), 2) AS precio_con_descuento
    FROM paquetes p
    LEFT JOIN paquete_descuentos pd ON p.id = pd.paquete_id
    LEFT JOIN descuentos d ON pd.descuento_id = d.id AND d.activo = 1
    ORDER BY p.precio
");
while($r = $res->fetch_assoc()) $paquetes[] = $r;

/* productos */
$productos = [];
$res = $conn->query("SELECT id, nombre, precio, tipo, cupos_disponibles, imagen FROM productos ORDER BY nombre");
while($r = $res->fetch_assoc()) $productos[] = $r;

/* regalos */
$regalos = [];
$res = $conn->query("SELECT id, nombre FROM regalos WHERE cupos_disponibles > 0 ORDER BY nombre");
while($r = $res->fetch_assoc()) $regalos[] = $r;
?>

<section class="seccion contenedor">
  <h2>Inscripcion al Encuentro</h2>

  <!-- AVISO QR -->
  <div class="aviso-qr">
    <i class="fa-solid fa-circle-info"></i>
    El pago se realiza via <strong>QR / Banca Movil</strong>. 
    Luego de completar el formulario podras ver el QR y subir tu comprobante.
  </div>

  <form id="registro" class="registro" novalidate>

    <!-- ========================================
         DATOS PERSONALES
         ======================================== -->
    <div id="datos-usuario" class="registro caja">
      <h4><i class="fa-solid fa-user"></i> Datos Personales</h4>

      <div class="campo">
        <label for="nombre">Nombre <span class="requerido">*</span></label>
        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre">
        <span class="campo-error" id="error-nombre"></span>
      </div>

      <div class="campo">
        <label for="apellido">Apellido <span class="requerido">*</span></label>
        <input type="text" id="apellido" name="apellido" placeholder="Tu apellido">
        <span class="campo-error" id="error-apellido"></span>
      </div>

      <div class="campo">
        <label for="carnet">Carnet de Identidad <span class="requerido">*</span></label>
        <input type="text" id="carnet" name="carnet" placeholder="Ej: 1234567 o 1234567A">
        <span class="campo-error" id="error-carnet"></span>
      </div>

      <div class="campo">
        <label for="fecha_nacimiento">Fecha de Nacimiento <span class="requerido">*</span></label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
        <span class="campo-error" id="error-fecha"></span>
      </div>

      <div class="campo">
        <label for="edad">Edad</label>
        <input type="text" id="edad" name="edad" placeholder="Se calcula automaticamente" readonly>
      </div>

      <div class="campo">
        <label for="celular">Celular <span class="requerido">*</span></label>
        <input type="text" id="celular" name="celular" placeholder="Ej: 70000000">
        <span class="campo-error" id="error-celular"></span>
      </div>

      <div class="campo">
        <label for="ministerio_id">Ministerio <span class="requerido">*</span></label>
        <select id="ministerio_id" name="ministerio_id">
          <option value="">-- Selecciona tu ministerio --</option>
          <?php foreach($ministerios as $m): ?>
            <option value="<?php echo $m['id']; ?>">
              <?php echo htmlspecialchars($m['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <span class="campo-error" id="error-ministerio"></span>
      </div>

      <div class="campo">
        <label for="iglesia_id">Iglesia <span class="requerido">*</span></label>
        <select id="iglesia_id" name="iglesia_id">
          <option value="">-- Selecciona tu iglesia --</option>
          <?php foreach($iglesias as $i): ?>
            <option value="<?php echo $i['id']; ?>">
              <?php echo htmlspecialchars($i['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <span class="campo-error" id="error-iglesia"></span>
      </div>

      <div class="campo">
        <label for="distrito_id">Distrito <span class="requerido">*</span></label>
        <select id="distrito_id" name="distrito_id">
          <option value="">-- Selecciona tu distrito --</option>
          <?php foreach($distritos as $d): ?>
            <option value="<?php echo $d['id']; ?>">
              <?php echo htmlspecialchars($d['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <span class="campo-error" id="error-distrito"></span>
      </div>

      <div class="campo">
        <label for="tipo_inscrito_id">Tipo de Inscrito <span class="requerido">*</span></label>
        <select id="tipo_inscrito_id" name="tipo_inscrito_id">
          <option value="">-- Selecciona tu tipo --</option>
          <?php foreach($tipos as $t): ?>
            <option value="<?php echo $t['id']; ?>">
              <?php echo htmlspecialchars($t['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <span class="campo-error" id="error-tipo"></span>
      </div>

      <div id="error-general"></div>
    </div>

    <!-- ========================================
         PAQUETES DESDE LA BASE DE DATOS
         ======================================== -->
    <div id="paquetes" class="paquetes">
      <h3>Elige tu Paquete</h3>
      <p class="texto-paquete">Selecciona solo un paquete</p>
      <span class="campo-error" id="error-paquete"></span>

      <ul class="lista-precios">
        <?php foreach($paquetes as $paq): 
          $tiene_descuento = !empty($paq['descuento_porcentaje']);
          $precio_final    = $tiene_descuento ? $paq['precio_con_descuento'] : $paq['precio'];
        ?>
          <li>
            <div class="tabla-precio <?php echo $paq['cupos_disponibles'] <= 0 ? 'agotado' : ''; ?>">

              <label class="paquete-label">
                <input type="radio" 
                       name="paquete" 
                       value="<?php echo $paq['id']; ?>"
                       data-precio="<?php echo $precio_final; ?>"
                       data-nombre="<?php echo htmlspecialchars($paq['nombre']); ?>"
                       <?php echo $paq['cupos_disponibles'] <= 0 ? 'disabled' : ''; ?>>
                <h3><?php echo htmlspecialchars($paq['nombre']); ?></h3>
              </label>

              <!-- precio original -->
              <?php if($tiene_descuento): ?>
                <p class="precio-original">Bs. <?php echo number_format($paq['precio'], 2); ?></p>
                <p class="numero">Bs. <?php echo number_format($precio_final, 2); ?></p>
                <div class="badge-descuento">
                  <i class="fa-solid fa-tag"></i>
                  <?php echo $paq['descuento_nombre']; ?> — 
                  <?php echo $paq['descuento_porcentaje']; ?>% OFF
                </div>
                <p class="fecha-descuento">
                  <i class="fa-solid fa-calendar"></i>
                  Hasta: <?php echo date('d/m/Y', strtotime($paq['fecha_fin'])); ?>
                </p>
              <?php else: ?>
                <p class="numero">Bs. <?php echo number_format($paq['precio'], 2); ?></p>
              <?php endif; ?>

              <!-- cupos -->
              <p class="cupos-disponibles">
                <i class="fa-solid fa-users"></i>
                <?php echo $paq['cupos_disponibles']; ?> / <?php echo $paq['cupo_total']; ?> cupos
              </p>

              <!-- lista de beneficios -->
              <ul class="lista-beneficios">
                <li>Recuerdo del encuentro</li>
                <li>Todas las conferencias</li>
                <li>Todos los talleres</li>
              </ul>

              <?php if($paq['cupos_disponibles'] <= 0): ?>
                <p class="texto-agotado">AGOTADO</p>
              <?php endif; ?>

            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- ========================================
         PRODUCTOS
         ======================================== -->
    <div id="seccion-productos" class="seccion-productos">
      <h3>Productos Adicionales</h3>
      <p class="texto-paquete">Selecciona los productos que deseas</p>

      <div class="grid-productos">
        <?php foreach($productos as $prod): ?>
          <div class="card-producto">

            <!-- imagen del producto -->
            <div class="producto-imagen">
              <?php if(!empty($prod['imagen'])): ?>
                <img src="<?php echo htmlspecialchars($prod['imagen']); ?>" 
                     alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
              <?php else: ?>
                <div class="producto-sin-imagen">
                  <i class="fa-solid fa-shirt"></i>
                </div>
              <?php endif; ?>
            </div>

            <div class="producto-info">
              <h4><?php echo htmlspecialchars($prod['nombre']); ?></h4>
              <p class="producto-precio">Bs. <?php echo number_format($prod['precio'], 2); ?></p>
              <p class="producto-cupos">
                <i class="fa-solid fa-box"></i>
                <?php echo $prod['cupos_disponibles']; ?> disponibles
              </p>

              <!-- cantidad -->
              <div class="producto-cantidad">
                <label>Cantidad:</label>
                <input type="number" 
                       min="0" 
                       max="<?php echo $prod['cupos_disponibles']; ?>"
                       class="input-cantidad"
                       data-id="<?php echo $prod['id']; ?>"
                       data-nombre="<?php echo htmlspecialchars($prod['nombre']); ?>"
                       data-precio="<?php echo $prod['precio']; ?>"
                       value="0">
              </div>

              <!-- talla si es polera o gorra -->
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
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ========================================
         REGALO
         ======================================== -->
    <div id="seccion-regalo" class="seccion-regalo caja">
      <h3>Selecciona tu Regalo</h3>
      <div class="campo">
        <label for="regalo_id">Regalo <span class="requerido">*</span></label>
        <select id="regalo_id" name="regalo_id">
          <option value="">-- Selecciona un regalo --</option>
          <?php foreach($regalos as $r): ?>
            <option value="<?php echo $r['id']; ?>">
              <?php echo htmlspecialchars($r['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <span class="campo-error" id="error-regalo"></span>
      </div>
    </div>

    <!-- ========================================
         RESUMEN Y TOTAL
         ======================================== -->
    <div id="resumen" class="resumen">
      <h3>Resumen de tu Inscripcion</h3>
      <div class="caja">

        <div class="extras">
          <button type="button" id="btn-calcular" class="button">
            <i class="fa-solid fa-calculator"></i> Calcular Total
          </button>
        </div>

        <div class="total">
          <p>Detalle:</p>
          <div id="lista-productos"></div>
          <p>Total a pagar:</p>
          <div id="suma-total"></div>
        </div>

      </div>
    </div>

    <!-- ========================================
         SECCION QR - oculta hasta completar todo
         ======================================== -->
    <div id="seccion-qr" class="seccion-qr" style="display:none;">
      <h3><i class="fa-solid fa-qrcode"></i> Pago por QR</h3>
      <div class="qr-contenido">

        <div class="qr-imagen-wrap">
          <img src="img/comprobante1.jpeg" alt="QR de pago" id="img-qr" class="qr-imagen">
          <a href="img/comprobante1.jpeg" download="QR-Pago-Encuentro.jpeg" class="button hollow">
            <i class="fa-solid fa-download"></i> Descargar QR
          </a>
        </div>

        <div class="qr-subir">
          <p class="qr-instruccion">
            <i class="fa-solid fa-circle-info"></i>
            Realiza el pago y sube tu comprobante aqui:
          </p>

          <div class="campo">
            <label for="comprobante">Subir comprobante <span class="requerido">*</span></label>
            <input type="file" 
                   id="comprobante" 
                   name="comprobante" 
                   accept="image/*,.pdf">
            <span class="campo-error" id="error-comprobante"></span>
          </div>

          <button type="button" id="btn-subir" class="button" disabled>
            <i class="fa-solid fa-upload"></i> Subir Comprobante
          </button>

          <div id="mensaje-subida" style="display:none;"></div>

          <!-- boton final - se activa tras subir comprobante -->
          <button type="submit" id="btnRegistro" class="button" disabled>
            <i class="fa-solid fa-check"></i> Confirmar Inscripcion
          </button>
        </div>

      </div>
    </div>

  </form>
</section>

<?php include_once 'includes/templates/footer.php'; ?>