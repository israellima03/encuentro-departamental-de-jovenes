<?php include_once 'includes/templates/header.php'; ?>

<section class="seccion contenedor">
  <h2>Pre Calendario de Eventos</h2>

  <?php 
    try {
      require_once('includes/funciones/bd_conexion.php');

      /* consulta con JOIN para traer todos los datos relacionados */
      $sql  = "SELECT 
                  e.id_evento, 
                  e.fecha, 
                  e.hora_inicio, 
                  e.hora_fin, 
                  e.tipo_evento,
                  d.nombre        AS nombre_dia,
                  t.titulo        AS tema,
                  ex.nombre       AS expositor_nombre,
                  ex.apellido     AS expositor_apellido,
                  ex.rango        AS expositor_rango,
                  g.nombre_grupo  AS grupo,
                  m.nombre        AS moderador_nombre,
                  m.apellido      AS moderador_apellido
               FROM eventos e
               LEFT JOIN dias          d  ON e.id_dia        = d.id_dia
               LEFT JOIN temas         t  ON e.id_tema        = t.id_tema
               LEFT JOIN expositores   ex ON e.id_expositor   = ex.id_expositor
               LEFT JOIN grupos_alabanza g ON e.id_grupo      = g.id_grupo
               LEFT JOIN moderadores   m  ON e.id_moderador   = m.id_moderador
               ORDER BY e.fecha, e.hora_inicio";

      $resultado = $conn->query($sql);

      if (!$resultado) {
        throw new Exception('Error en consulta: ' . $conn->error);
      }

      /* agrupa los eventos por fecha */
      $calendario = array();
      while ($fila = $resultado->fetch_assoc()) {
        $fecha = $fila['fecha']; /* clave del array es la fecha */
        $calendario[$fecha][] = $fila;
      }

    } catch(\Exception $e) {
      echo '<p class="error-bd">' . $e->getMessage() . '</p>';
    }
  ?>

  <div class="calendario">

    <?php foreach ($calendario as $fecha => $eventos): ?>

      <!-- CABECERA DEL DIA -->
      <div class="dia-calendario">
        <h3 class="titulo-fecha">
          <i class="fa-solid fa-calendar-day"></i>
          <?php
            /* formatea la fecha en español */
            $timestamp = strtotime($fecha);
            $dias_semana = ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'];
            $meses       = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                            'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
            $num_dia     = date('w', $timestamp);
            $num_mes     = date('n', $timestamp);
            echo $dias_semana[$num_dia] . ', ' . date('d', $timestamp) . ' de ' . $meses[$num_mes] . ' de ' . date('Y', $timestamp);
          ?>
        </h3>

        <!-- LISTA DE EVENTOS DEL DIA -->
        <div class="lista-eventos-dia">
          <?php foreach ($eventos as $evento): 

            /* icono segun tipo de evento */
            $iconos = [
                'Conferencia'   => 'fa-solid fa-cross',
                'Taller'        => 'fa-solid fa-book-open',
                'Vigilia'       => 'fa-solid fa-moon',
                'Paseo'         => 'fa-solid fa-person-hiking',
                'Paseo(Tours)'  => 'fa-solid fa-bus',
                'Carrera'       => 'fa-solid fa-person-running',
                'Clausura'      => 'fa-solid fa-flag-checkered',
                'Predica'       => 'fa-solid fa-bible',
                'Concurso'      => 'fa-solid fa-trophy',
                'Deportivo'     => 'fa-solid fa-futbol',
                'Premiacion'    => 'fa-solid fa-medal',
            ];
            $icono      = $iconos[$evento['tipo_evento']] ?? 'fa-solid fa-calendar-check';
            $clase_tipo = strtolower(
                str_replace(['(', ')', ' '], ['-', '', '-'], $evento['tipo_evento']));
          ?>

            <div class="tarjeta-evento tipo-<?php echo $clase_tipo; ?>">

              <!-- CABECERA CON COLOR Y HORA -->
              <div class="tarjeta-cabecera">
                <span class="hora-bloque">
                  <i class="fa-solid fa-clock"></i>
                  <?php echo substr($evento['hora_inicio'], 0, 5); ?> — <?php echo substr($evento['hora_fin'], 0, 5); ?>
                </span>
                <span class="tipo-badge">
                  <?php echo $evento['tipo_evento']; ?>
                </span>
              </div>

              <!-- ICONO GRANDE DEL TIPO -->
              <div class="tarjeta-icono">
                <i class="<?php echo $icono; ?>"></i>
              </div>

              <!-- CUERPO -->
              <div class="tarjeta-cuerpo">

                <!-- TEMA -->
                <?php if ($evento['tema']): ?>
                  <h4 class="evento-tema"><?php echo $evento['tema']; ?></h4>
                  <div class="separador-evento"></div>
                <?php endif; ?>

                <!-- DETALLES -->
                <div class="evento-detalles">

                  <?php if ($evento['moderador_nombre']): ?>
                    <div class="evento-detalle">
                      <i class="fa-solid fa-microphone"></i>
                      <div>
                        <span class="detalle-etiqueta">Moderador</span>
                        <span class="detalle-valor">
                          <?php echo $evento['moderador_nombre'] . ' ' . $evento['moderador_apellido']; ?>
                        </span>
                      </div>
                    </div>
                  <?php endif; ?>

                  <?php if ($evento['grupo']): ?>
                    <div class="evento-detalle">
                      <i class="fa-solid fa-music"></i>
                      <div>
                        <span class="detalle-etiqueta">Alabanza</span>
                        <span class="detalle-valor"><?php echo $evento['grupo']; ?></span>
                      </div>
                    </div>
                  <?php endif; ?>

                  <?php if ($evento['expositor_nombre']): ?>
                    <div class="evento-detalle">
                      <i class="fa-solid fa-user-tie"></i>
                      <div>
                        <span class="detalle-etiqueta"><?php echo $evento['expositor_rango']; ?></span>
                        <span class="detalle-valor">
                          <?php echo $evento['expositor_nombre'] . ' ' . $evento['expositor_apellido']; ?>
                        </span>
                      </div>
                    </div>
                  <?php endif; ?>

                </div>
              </div>

            </div>

          <?php endforeach; ?>

        </div><!-- fin lista-eventos-dia -->
      </div><!-- fin dia-calendario -->

    <?php endforeach; ?>

  </div><!-- fin calendario -->

  <?php $conn->close(); ?>
</section>

<?php include_once 'includes/templates/footer.php'; ?>