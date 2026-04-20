<?php include_once 'includes/templates/header.php'; ?>

  <section class="seccion contenedor">
    <h2>La Iglesia Dios De La Profecia te invita a ser parte de este encuentro </h2>
    <p>
      La Iglesia de Dios de la Profecía en Oruro extiende una cordial invitación a toda la juventud de las distintas iglesias a ser parte de este gran encuentro espiritual. Será un tiempo especial de unidad, adoración y crecimiento en la presencia de Dios, donde juntos podremos renovar nuestras fuerzas, fortalecer nuestra fe y compartir como una sola familia en Cristo. No te quedes fuera de esta experiencia única que marcará tu vida. ¡Ven y sé parte de lo que Dios tiene preparado para ti!
    </p>
  </section>

  <section class="programa">
    <div class="contenedor-video">
      <video autoplay loop poster="img/video-fondo2.jpg">
        <source src="videos/tarija-video.mp4" type="video/mp4">
        <source src="videos/tarija-video.webm" type="video/webm">
      </video>
    </div>

    <div class="contenido-programa">
      <div class="contenedor">
        <div class="programa-evento">

          <h2>programa del encuentro</h2>

          <!-- MENU -->
          <nav class="menu-programa">
            <a href="#viernes"><i class="fa-solid fa-music"></i>Viernes</a>
            <a href="#sabado"><i class="fa-solid fa-cross"></i>Sabado</a>
            <a href="#domingo"><i class="fa-solid fa-book-bible"></i>Domingo</a>
          </nav>

          <?php 
          require_once('includes/funciones/bd_conexion.php');

          $sql = "SELECT e.*, 
                         g.nombre_grupo,
                         t.titulo        AS tema,
                         ex.nombre       AS expositor_nombre,
                         ex.apellido     AS expositor_apellido,
                         ex.rango        AS expositor_rango
                  FROM eventos e
                  LEFT JOIN grupos_alabanza g  ON e.id_grupo    = g.id_grupo
                  LEFT JOIN temas           t  ON e.id_tema      = t.id_tema
                  LEFT JOIN expositores     ex ON e.id_expositor = ex.id_expositor
                  ORDER BY e.id_dia, e.hora_inicio";

          $resultado = $conn->query($sql);

          $eventos = [
            1 => [], // viernes
            2 => [], // sabado
            3 => []  // domingo
          ];

          while($row = $resultado->fetch_assoc()){
            $eventos[$row['id_dia']][] = $row;
          }
          ?>

          <!-- VIERNES -->
          <div id="viernes" class="info-curso">
            <?php foreach($eventos[1] as $evento): ?>
              <div class="detalle-evento">
                <h3><?php echo htmlspecialchars($evento['tipo_evento']); ?></h3>

                <p><i class="fa-solid fa-clock"></i>
                  <?php echo substr($evento['hora_inicio'],0,5); ?> hrs
                </p>

                <p><i class="fa-solid fa-calendar"></i>
                  <?php echo $evento['fecha']; ?>
                </p>

                <?php if (!empty($evento['tema'])): ?>
                  <p><i class="fa-solid fa-book-open"></i>
                    <?php echo htmlspecialchars($evento['tema']); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($evento['expositor_nombre'])): ?>
                  <p><i class="fa-solid fa-user-tie"></i>
                    <?php echo htmlspecialchars($evento['expositor_rango'] . ' ' . $evento['expositor_nombre'] . ' ' . $evento['expositor_apellido']); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($evento['nombre_grupo'])): ?>
                  <p><i class="fa-solid fa-music"></i>
                    <?php echo htmlspecialchars($evento['nombre_grupo']); ?>
                  </p>
                <?php endif; ?>

              </div>
            <?php endforeach; ?>
            <a href="calendario.php" class="button">Ver todos</a>
          </div>

          <!-- SABADO -->
          <div id="sabado" class="info-curso">
            <?php foreach($eventos[2] as $evento): ?>
              <div class="detalle-evento">
                <h3><?php echo htmlspecialchars($evento['tipo_evento']); ?></h3>

                <p><i class="fa-solid fa-clock"></i>
                  <?php echo substr($evento['hora_inicio'],0,5); ?> hrs
                </p>

                <p><i class="fa-solid fa-calendar"></i>
                  <?php echo $evento['fecha']; ?>
                </p>

                <?php if (!empty($evento['tema'])): ?>
                  <p><i class="fa-solid fa-book-open"></i>
                    <?php echo htmlspecialchars($evento['tema']); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($evento['expositor_nombre'])): ?>
                  <p><i class="fa-solid fa-user-tie"></i>
                    <?php echo htmlspecialchars($evento['expositor_rango'] . ' ' . $evento['expositor_nombre'] . ' ' . $evento['expositor_apellido']); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($evento['nombre_grupo'])): ?>
                  <p><i class="fa-solid fa-music"></i>
                    <?php echo htmlspecialchars($evento['nombre_grupo']); ?>
                  </p>
                <?php endif; ?>

              </div>
            <?php endforeach; ?>
            <a href="calendario.php" class="button">Ver todos</a>
          </div>

          <!-- DOMINGO -->
          <div id="domingo" class="info-curso">
            <?php foreach($eventos[3] as $evento): ?>
              <div class="detalle-evento">
                <h3><?php echo htmlspecialchars($evento['tipo_evento']); ?></h3>

                <p><i class="fa-solid fa-clock"></i>
                  <?php echo substr($evento['hora_inicio'],0,5); ?> hrs
                </p>

                <p><i class="fa-solid fa-calendar"></i>
                  <?php echo $evento['fecha']; ?>
                </p>

                <?php if (!empty($evento['tema'])): ?>
                  <p><i class="fa-solid fa-book-open"></i>
                    <?php echo htmlspecialchars($evento['tema']); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($evento['expositor_nombre'])): ?>
                  <p><i class="fa-solid fa-user-tie"></i>
                    <?php echo htmlspecialchars($evento['expositor_rango'] . ' ' . $evento['expositor_nombre'] . ' ' . $evento['expositor_apellido']); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($evento['nombre_grupo'])): ?>
                  <p><i class="fa-solid fa-music"></i>
                    <?php echo htmlspecialchars($evento['nombre_grupo']); ?>
                  </p>
                <?php endif; ?>

              </div>
            <?php endforeach; ?>
            <a href="calendario.php" class="button">Ver todos</a>
          </div>

        </div>
      </div>
    </div>
  </section>


  <section class="invitados contenedor seccion">
    <h2>Nuestros Invitados</h2>
    <?php
      require_once('includes/funciones/bd_conexion.php');
      $res_exp = $conn->query("SELECT nombre, apellido, rango, imagen FROM expositores ORDER BY apellido");
    ?>
    <ul class="lista-invitados">
      <?php while($exp = $res_exp->fetch_assoc()): ?>
        <li>
          <div class="invitado">
            <?php if(!empty($exp['imagen'])): ?>
              <img src="<?php echo htmlspecialchars($exp['imagen']); ?>" 
                   alt="<?php echo htmlspecialchars($exp['nombre']); ?>">
            <?php else: ?>
              <img src="invitado1.jpg" alt="invitado">
            <?php endif; ?>
            <p>
              <?php echo htmlspecialchars($exp['rango'] . ' ' . $exp['nombre'] . ' ' . $exp['apellido']); ?>
            </p>
          </div>
        </li>
      <?php endwhile; ?>
    </ul>
  </section>


  <div class="contador parallax">
    <div class="contenedor">
      <ul class="resumen-evento">
        <li><p class="numero">4</p> Invitados</li>
        <li><p class="numero">6</p> Confencias</li>
        <li><p class="numero">3</p> Dias</li>
        <li><p class="numero">1</p> Encuentro deportivo</li>
        
      </ul>

    </div>

  </div>

  <!--precios-->
  <section class="precios seccion">
    <h2>Precios</h2>

    <div class="aviso-inscripcion">
      <i class="fa-solid fa-circle-info"></i>
      Puedes inscribirte <strong>personalmente con QR</strong> o si es en 
      <strong>efectivo</strong> acercate a tu lider local.
      Para mas informacion presiona el boton 
      <a href="tipo_inscripciones.php" class="link-inscripcion">Inscripciones</a> en la barra.
    </div>

    <div class="contenedor">
      <?php
        /* paquetes con descuento activo desde la BD */
        $res_paq = $conn->query("
            SELECT p.id, p.nombre, p.precio, p.cupo_total, p.cupos_disponibles,
                   d.nombre        AS desc_nombre,
                   d.porcentaje    AS desc_porcentaje,
                   d.fecha_fin,
                   ROUND(p.precio - (p.precio * COALESCE(d.porcentaje,0) / 100), 2) AS precio_final
            FROM paquetes p
           LEFT JOIN paquete_descuentos pd ON p.id = pd.paquete_id
            LEFT JOIN descuentos d ON pd.descuento_id = d.id AND d.activo = 1
            ORDER BY p.precio
        ");

        /* beneficios de cada paquete segun su id */
        $beneficios = [
            1 => [
                'Entrada a todas las conferencias',
                'Entrada al encuentro deportivo',
                'Entrada al tour / paseo',
                'Recuerdo del encuentro',
            ],
            3 => [
                'Entrada a todas las conferencias',
                'Entrada al encuentro deportivo',
                'Entrada al tour / paseo',
                'Alojamiento en iglesia local',
                'Recuerdo del encuentro',
            ],
            2 => [
                'Entrada a todas las conferencias',
                'Entrada al encuentro deportivo',
                'Entrada al tour / paseo',
                'Alojamiento privado',
                'Recuerdo del encuentro',
            ],
        ];
      ?>

      <ul class="lista-precios">
        <?php while($paq = $res_paq->fetch_assoc()):
          $con_desc   = !empty($paq['desc_porcentaje']);
          $pf         = $con_desc ? $paq['precio_final'] : $paq['precio'];
          $agotado    = $paq['cupos_disponibles'] <= 0;
          $porc_cupos = $paq['cupo_total'] > 0
                        ? round(($paq['cupos_disponibles'] / $paq['cupo_total']) * 100)
                        : 0;
          $color_cupo = $porc_cupos > 50 ? 'cupo-alto' : ($porc_cupos > 20 ? 'cupo-medio' : 'cupo-bajo');
          $bens       = $beneficios[$paq['id']] ?? ['Entrada al encuentro'];
        ?>
          <li>
            <div class="tabla-precio <?php echo $agotado ? 'agotado' : ''; ?>">

              <!-- nombre del paquete -->
              <h3><?php echo htmlspecialchars($paq['nombre']); ?></h3>

              <!-- precio con o sin descuento -->
              <?php if($con_desc): ?>
                <p class="precio-original">Bs. <?php echo number_format($paq['precio'],2); ?></p>
                <p class="numero">Bs. <?php echo number_format($pf,2); ?></p>
                <div class="badge-descuento">
                  <i class="fa-solid fa-tag"></i>
                  <?php echo htmlspecialchars($paq['desc_nombre']); ?> —
                  <?php echo $paq['desc_porcentaje']; ?>% OFF
                </div>
                <p class="fecha-descuento">
                  <i class="fa-solid fa-calendar"></i>
                  Promocion hasta: <?php echo date('d/m/Y', strtotime($paq['fecha_fin'])); ?>
                </p>
              <?php else: ?>
                <p class="numero">Bs. <?php echo number_format($pf,2); ?></p>
              <?php endif; ?>

              <!-- cupos disponibles resaltados -->
              <div class="barra-cupos <?php echo $color_cupo; ?>">
                <div class="barra-cupos-relleno" style="width:<?php echo $porc_cupos; ?>%"></div>
              </div>
              <p class="texto-cupos <?php echo $color_cupo; ?>">
                <i class="fa-solid fa-users"></i>
                <?php if($agotado): ?>
                  <strong>AGOTADO</strong>
                <?php else: ?>
                  <strong><?php echo $paq['cupos_disponibles']; ?></strong> cupos disponibles
                  de <?php echo $paq['cupo_total']; ?>
                <?php endif; ?>
              </p>

              <!-- beneficios -->
              <ul>
                <?php foreach($bens as $ben): ?>
                  <li><?php echo $ben; ?></li>
                <?php endforeach; ?>
              </ul>

              <!-- boton -->
              <?php if($agotado): ?>
                <a href="#" class="button hollow" style="opacity:.5;pointer-events:none;">Agotado</a>
              <?php else: ?>
                <a href="tipo_inscripciones.php" class="button hollow">Inscribirme</a>
              <?php endif; ?>

            </div>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>
  </section>

  <section class="seccion-mapa">
    <h2>Ubicacion del evento</h2>
    <p class="texto-mapa">
      <i class="fa-solid fa-location-dot"></i> 
      Haz click en el mapa para abrir en Google Maps y obtener direcciones
    </p>
    <div class="contenedor-mapa">
      <a href="https://maps.google.com/?q=-21.541682,-64.726782" target="_blank" class="link-mapa">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3203.750919665405!2d-64.72678242144536!3d-21.541682100000003!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9406475233b071dd%3A0xdbb1f8dbcd3f54bc!2sPunto%20de%20Venta%20Loter%C3%ADa%20%22Kiosko%20Ex.%20Terminal%22!5e1!3m2!1ses-419!2sbo!4v1776045310858!5m2!1ses-419!2sbo" 
          width="100%" 
          height="100%" 
          style="border:0;pointer-events:none;" 
          allowfullscreen="" 
          loading="lazy">
        </iframe>
        <div class="overlay-mapa">
          <i class="fa-solid fa-location-dot"></i>
          <p>Click para abrir en Google Maps</p>
        </div>
      </a>
    </div>
  </section>

  <div class="newsletter parallax">
    <div class="contenido contenedor">
      <p>Registrate para el</p>
      <h3>Encuentro departamental</h3>
      <a href="tipo_inscripciones.php" class="button trasparente">Registro</a>
    </div>
  </div>

  <section class="seccion">
    <h2>Faltan</h2>
    <div class="cuenta-regresiva contenedor">
      <ul class="">
        <li><p class="numero" id="dias">0</p> dias</li>
        <li><p class="numero" id="horas">0</p> horas</li>
        <li><p class="numero" id="minutos">0</p> minutos</li>
        <li><p class="numero" id="segundos">0</p> segundos</li>
      </ul>
    </div>
  </section>
  
<?php include_once 'includes/templates/footer.php'; ?>