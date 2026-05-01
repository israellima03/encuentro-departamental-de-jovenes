<?php 
include_once 'includes/templates/header.php';
require_once('includes/funciones/bd_conexion.php');

$iglesias = [];
$res = $conn->query("
    SELECT i.id, i.nombre AS iglesia,
           d.id AS distrito_id, d.nombre AS distrito
    FROM iglesias i
    LEFT JOIN distritos d ON i.distrito_id = d.id
    ORDER BY i.nombre
");
while($r = $res->fetch_assoc()) $iglesias[] = $r;

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

$regalo_fijo_id     = 2;
$regalo_fijo_nombre = 'Boligrafo y folder';
$res_r = $conn->query("SELECT id, nombre FROM regalos WHERE id = $regalo_fijo_id LIMIT 1");
if($res_r && $row_r = $res_r->fetch_assoc()) $regalo_fijo_nombre = $row_r['nombre'];
?>

<section class="seccion contenedor">
  <h2>Inscripcion al Encuentro</h2>

  <div class="aviso-qr">
    <i class="fa-solid fa-qrcode"></i>
    El pago es <strong>unicamente por QR / Banca Movil</strong>. 
    Completa todos los datos, revisa el resumen, escanea el QR, sube tu comprobante y tu inscripcion quedara como <strong>PENDIENTE</strong> hasta que la tesorera la confirme.
  </div>

  <div id="bloque-formulario">
    <form id="form-registro" novalidate>

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
            <input type="text" id="carnet" placeholder="Ej: 1234567 o 1234567-1A">
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
            <input type="text" id="celular" placeholder="Ej: 68319277">
            <span class="campo-error" id="err-celular"></span>
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

          <div class="campo campo-ancho">
            <label>Iglesia <span class="req">*</span></label>
            <select id="iglesia_id">
              <option value="">-- Selecciona tu iglesia --</option>
              <?php foreach($iglesias as $ig): ?>
                <option value="<?php echo $ig['id']; ?>"
                        data-distrito-id="<?php echo $ig['distrito_id'] ?? ''; ?>"
                        data-distrito="<?php echo htmlspecialchars($ig['distrito'] ?? ''); ?>">
                  <?php echo htmlspecialchars($ig['iglesia']); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <span class="campo-error" id="err-iglesia"></span>
          </div>

          <input type="hidden" id="distrito_id" value="">
          <input type="hidden" id="ministerio_id" value="0">

          <div class="campo">
            <label>Distrito</label>
            <input type="text" id="distrito_nombre" placeholder="Se completa al elegir iglesia" readonly class="campo-derivado">
          </div>

        </div>
      </div>

      <div class="seccion-form">
        <h3 style="text-align:center;margin-bottom:8px;">Elige tu Paquete</h3>
        <p class="texto-paquete">Solo puedes elegir un paquete</p>
        <span class="campo-error" id="err-paquete" style="text-align:center;display:block;"></span>

        <ul class="lista-precios">
          <?php foreach($paquetes as $paq):
            $con_desc = !empty($paq['descuento_porcentaje']);
            $pf       = $con_desc ? $paq['precio_final'] : $paq['precio'];
            $agotado  = $paq['cupos_disponibles'] <= 0;
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
                  <?php
                    $bens = [
                      1 => ['Todas las conferencias','Recuerdo del encuentro','Tours Tarija','Encuentro deportivo'],
                      3 => ['Todas las conferencias','Recuerdo del encuentro','Tours Tarija','Encuentro deportivo','Alojamiento en iglesia'],
                    ];
                    $lista = $bens[$paq['id']] ?? ['Entrada al encuentro'];
                    foreach($lista as $b): ?>
                      <li><?php echo $b; ?></li>
                  <?php endforeach; ?>
                </ul>

                <?php if($agotado): ?>
                  <p class="texto-agotado">AGOTADO</p>
                <?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

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
                  <?php $tipo_prod = strtolower(trim($prod['tipo'])); ?>

                  <?php if($tipo_prod !== 'gorra'): ?>
                    <label>Género:</label>
                    <div class="genero-opciones">
                      <label class="genero-opt">
                        <input type="radio" name="genero_<?php echo $prod['id']; ?>"
                               class="radio-genero" value="hombre" checked
                               data-prod-id="<?php echo $prod['id']; ?>">
                        <i class="fa-solid fa-mars"></i> Hombre
                      </label>
                      <label class="genero-opt">
                        <input type="radio" name="genero_<?php echo $prod['id']; ?>"
                               class="radio-genero" value="mujer"
                               data-prod-id="<?php echo $prod['id']; ?>">
                        <i class="fa-solid fa-venus"></i> Mujer
                      </label>
                    </div>
                  <?php else: ?>
                    <p style="font-size:0.82em;color:#666;margin-bottom:6px;">
                      <i class="fa-solid fa-circle-info" style="color:#0089e4;"></i>
                      Talla unisex — válida para hombre y mujer
                    </p>
                  <?php endif; ?>

                  <label style="margin-top:8px;display:block;">Talla:</label>
                  <select class="select-talla"
                          data-id="<?php echo $prod['id']; ?>"
                          data-tipo="<?php echo htmlspecialchars($tipo_prod); ?>">
                    <option value="">-- Talla --</option>
                    <?php if($tipo_prod === 'gorra'): ?>
                      <option value="Pequeño">Pequeño</option>
                      <option value="Mediano">Mediano</option>
                      <option value="Grande">Grande</option>
                      <option value="Extra Grande">Extra Grande</option>
                    <?php else: ?>
                      <option value="XS">XS</option>
                      <option value="S">S</option>
                      <option value="M">M</option>
                      <option value="L">L</option>
                      <option value="XL">XL</option>
                      <option value="XXL">XXL</option>
                    <?php endif; ?>
                  </select>

                  <!-- tabla de medidas solo para no-gorras -->
                  <?php if($tipo_prod !== 'gorra'): ?>
                  <div class="tabla-medidas-wrap" style="display:none;">
                    <table class="tabla-medidas">
                      <thead>
                        <tr><th>Talla</th><th>Ancho cm</th><th>Alto cm</th></tr>
                      </thead>
                      <tbody class="tbody-medidas"></tbody>
                    </table>
                  </div>
                  <?php endif; ?>

                  <span class="campo-error error-talla"></span>
                </div>

              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="seccion-form caja">
        <h4><i class="fa-solid fa-gift"></i> Regalo del Encuentro</h4>
        <div class="aviso-regalo">
          <i class="fa-solid fa-box-open"></i>
          Todos los inscritos reciben: <strong><?php echo htmlspecialchars($regalo_fijo_nombre); ?></strong>
        </div>
        <input type="hidden" id="regalo_id" value="<?php echo $regalo_fijo_id; ?>">
      </div>

      <div class="seccion-form resumen">
        <h3>Resumen de tu Inscripcion</h3>
        <div class="caja" style="text-align:center;padding:25px;">
          <button type="button" id="btn-calcular" class="button" disabled>
            <i class="fa-solid fa-eye"></i> Ver Resumen y Continuar al Pago
          </button>
          <p class="texto-paquete" id="hint-calcular">Completa todos los campos para activar este boton</p>
        </div>
      </div>

    </form>
  </div>

  <div id="bloque-resumen" style="display:none;">
    <div class="resumen-completo caja">
      <h4><i class="fa-solid fa-clipboard-list"></i> Verifica tus Datos Antes de Pagar</h4>
      <div class="resumen-grid">
        <div class="resumen-col">
          <h5 class="resumen-subtitulo"><i class="fa-solid fa-user"></i> Datos Personales</h5>
          <table class="tabla-resumen">
            <tr><td class="lbl">Nombre completo</td><td id="res-nombre-completo">—</td></tr>
            <tr><td class="lbl">Carnet</td><td id="res-carnet">—</td></tr>
            <tr><td class="lbl">Fecha de nacimiento</td><td id="res-fecha">—</td></tr>
            <tr><td class="lbl">Edad</td><td id="res-edad">—</td></tr>
            <tr><td class="lbl">Celular</td><td id="res-celular">—</td></tr>
            <tr><td class="lbl">Tipo de inscrito</td><td id="res-tipo">—</td></tr>
          </table>
          <h5 class="resumen-subtitulo" style="margin-top:20px;"><i class="fa-solid fa-church"></i> Datos de Iglesia</h5>
          <table class="tabla-resumen">
            <tr><td class="lbl">Iglesia</td><td id="res-iglesia">—</td></tr>
            <tr><td class="lbl">Distrito</td><td id="res-distrito">—</td></tr>
          </table>
        </div>
        <div class="resumen-col">
          <h5 class="resumen-subtitulo"><i class="fa-solid fa-box-open"></i> Inscripcion</h5>
          <table class="tabla-resumen">
            <tr><td class="lbl">Paquete</td><td id="res-paquete">—</td></tr>
            <tr><td class="lbl">Precio paquete</td><td id="res-precio-paquete">—</td></tr>
            <tr><td class="lbl">Regalo incluido</td><td><?php echo htmlspecialchars($regalo_fijo_nombre); ?></td></tr>
          </table>
          <div id="res-productos-wrap" style="display:none;">
            <h5 class="resumen-subtitulo" style="margin-top:20px;"><i class="fa-solid fa-shirt"></i> Productos Adicionales</h5>
            <table class="tabla-resumen" id="res-productos-tabla"></table>
          </div>
          <div class="resumen-total-wrap">
            <span class="resumen-total-lbl">TOTAL A PAGAR</span>
            <span class="resumen-total-num" id="res-total">Bs. 0.00</span>
          </div>
        </div>
      </div>
      <div class="resumen-acciones">
        <button type="button" id="btn-editar" class="button hollow">
          <i class="fa-solid fa-pen-to-square"></i> Editar mis datos
        </button>
        <button type="button" id="btn-ir-pago" class="button">
          <i class="fa-solid fa-qrcode"></i> Todo esta correcto, ir al pago
        </button>
      </div>
    </div>
  </div>

  <div id="seccion-qr" style="display:none;">
    <div class="seccion-qr">
      <h3><i class="fa-solid fa-qrcode"></i> Realiza tu Pago por QR</h3>
      <div class="qr-contenido">
        <div class="qr-imagen-wrap">
          <p class="qr-instruccion"><i class="fa-solid fa-circle-info"></i> Descarga y Escanea este QR con tu app de banca movil, no olvides poner tu nombre en el comprobante</p>
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
  </div>

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