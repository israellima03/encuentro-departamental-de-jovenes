<?php include_once 'includes/templates/header.php'; ?>

  <section class="seccion-invitacion">
    <div class="inv-contenido">
      <span class="inv-tag">— Encuentro Departamental 2026 —</span>
      <h2 class="inv-titulo">La Iglesia Dios de la Profecía<br>te invita</h2>
      <p class="inv-texto">
        La Iglesia de Dios de la Profecía en Oruro extiende una cordial invitación a toda la juventud de las distintas iglesias a ser parte de este gran encuentro espiritual. Será un tiempo especial de unidad, adoración y crecimiento en la presencia de Dios, donde juntos podremos renovar nuestras fuerzas, fortalecer nuestra fe y compartir como una sola familia en Cristo.
      </p>
      <div class="inv-datos">
        <div class="inv-dato">
          <i class="fa-solid fa-calendar-days"></i>
          <span class="inv-dato-lbl">Fecha</span>
          <span class="inv-dato-val">10 de Julio, 2026</span>
        </div>
        <div class="inv-dato">
          <i class="fa-solid fa-location-dot"></i>
          <span class="inv-dato-lbl">Lugar</span>
          <span class="inv-dato-val">Tarija, Bolivia</span>
        </div>
        <div class="inv-dato">
          <i class="fa-solid fa-dove"></i>
          <span class="inv-dato-lbl">Lema</span>
          <span class="inv-dato-val">Sin Filtros</span>
        </div>
      </div>
      <a href="tipo_inscripciones.php" class="inv-btn">
      <i class="fa-solid fa-arrow-right"></i> Inscríbete ahora
      </a>
      <a href="#inscribir-equipo" class="inv-btn inv-btn-deportivo" onclick="abrirModalEquipo(event)">
        <i class="fa-solid fa-futbol"></i> ¡En Tarija habrá noches deportivas de fútbol y otros deportes! Inscribe tu equipo puede ser mixto tambien.
      </a>

      <!-- MODAL INSCRIPCIÓN EQUIPO -->
      <div id="modal-equipo-overlay" style="display:none;position:fixed;inset:0;background:rgba(3,4,94,0.7);z-index:9999;display:none;place-items:center;padding:10px;">
        <div id="modal-equipo" style="background:#fff;border-radius:16px;width:100%;max-width:480px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3);animation:modalIn .25s ease;">

          <div style="background:linear-gradient(135deg,#03045e,#0077b6);padding:14px 16px;display:flex;justify-content:space-between;align-items:center;">
            <h3 style="font-family:'Oswald',sans-serif;color:#fff;margin:0;font-size:1.2em;letter-spacing:1px;">
              <i class="fa-solid fa-futbol"></i> Inscribir Equipo
            </h3>
            <button onclick="cerrarModalEquipo()" style="background:rgba(255,255,255,0.15);border:none;color:#fff;width:30px;height:30px;border-radius:6px;cursor:pointer;font-size:16px;">✕</button>
          </div>

          <div style="padding:14px;">

            <div style="background:#fef9c3;border-left:4px solid #f59e0b;border-radius:6px;padding:12px 14px;margin-bottom:20px;font-size:13px;color:#78350f;">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <strong> Debes estar inscrito al encuentro</strong> para registrar tu equipo. y el equipo puede ser mixto.
            </div>

            <!-- BUSCADOR INSCRITO -->
            <div style="margin-bottom:16px;">
              <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#6b7280;display:block;margin-bottom:6px;">
                Busca tu nombre o carnet *
              </label>
              <input type="text" id="eq-buscar" placeholder="Ej: 12345678 o Juan Pérez"
                     style="width:100%;padding:10px 14px;border:1.5px solid #e4e8f0;border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
              <div id="eq-resultados" style="margin-top:6px;"></div>
              <input type="hidden" id="eq-inscrito-id">
              <div id="eq-inscrito-sel" style="display:none;margin-top:8px;background:#d1fae5;border-radius:8px;padding:10px 14px;font-size:13px;color:#065f46;font-weight:600;">
                <i class="fa-solid fa-circle-check"></i> <span id="eq-inscrito-nombre"></span>
              </div>
            </div>

            <!-- NOMBRE EQUIPO -->
            <div style="margin-bottom:16px;">
              <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#6b7280;display:block;margin-bottom:6px;">
                Nombre de tu equipo *
              </label>
              <input type="text" id="eq-nombre" maxlength="50" placeholder="Ej: Los Guerreros"
                     style="width:100%;padding:10px 14px;border:1.5px solid #e4e8f0;border-radius:8px;font-size:13px;font-family:inherit;outline:none;">
              <div id="eq-nombre-msg" style="font-size:12px;margin-top:4px;display:none;"></div>
              <p style="font-size:11px;color:#9ca3af;margin-top:4px;">
                <i class="fa-solid fa-circle-info"></i> Los equipos con nombres inapropiados serán eliminados.
              </p>
            </div>

            <div id="eq-error" style="display:none;background:#fee2e2;border-left:4px solid #ef4444;border-radius:6px;padding:10px 14px;font-size:13px;color:#991b1b;margin-bottom:14px;"></div>

            <button id="eq-btn-registrar" onclick="registrarEquipo()"
                    style="width:100%;background:#03045e;color:#fff;border:none;border-radius:8px;padding:13px;font-family:'Oswald',sans-serif;font-size:1em;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;transition:background .2s;">
              <i class="fa-solid fa-futbol"></i> Inscribir mi equipo
            </button>
          </div>
        </div>
      </div>

      <!-- MODAL ÉXITO EQUIPO -->
      <div id="modal-equipo-exito" style="display:none;position:fixed;inset:0;background:rgba(3,4,94,0.7);z-index:9999;place-items:center;padding:20px;">
        <div style="background:#fff;border-radius:16px;width:100%;max-width:420px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3);text-align:center;">
          <div style="background:linear-gradient(135deg,#10b981,#065f46);padding:28px 20px;">
            <i class="fa-solid fa-futbol" style="font-size:3em;color:#fff;display:block;margin-bottom:10px;"></i>
            <h3 style="font-family:'Oswald',sans-serif;color:#fff;margin:0;font-size:1.4em;">¡Equipo Inscrito!</h3>
          </div>
          <div style="padding:24px;">
            <p style="font-size:14px;color:#374151;margin-bottom:6px;">Tu equipo <strong id="eq-exito-nombre"></strong> fue registrado exitosamente.</p>
            <p style="font-size:13px;color:#6b7280;margin-bottom:20px;">Únete al grupo de WhatsApp para coordinar y compartir con tus amigos.</p>
            <a href="https://chat.whatsapp.com/H5ar8bbEUO0Dy0pNXxKkvn" target="_blank"
               style="display:inline-flex;align-items:center;gap:10px;background:#25d366;color:#fff;text-decoration:none;padding:13px 28px;border-radius:50px;font-family:'Oswald',sans-serif;font-size:1em;letter-spacing:1px;margin-bottom:14px;">
              <i class="fa-brands fa-whatsapp"></i> Unirme al grupo
            </a>
            <br>
            <button onclick="cerrarModalExitoEquipo()"
                    style="background:none;border:none;color:#9ca3af;font-size:13px;cursor:pointer;margin-top:6px;">
              Cerrar
            </button>
          </div>
        </div>
      </div>

      <script>
      /* ── MODAL EQUIPO ── */
      function abrirModalEquipo(e){
        e.preventDefault();
        var m = document.getElementById('modal-equipo-overlay');
       m.style.display = 'grid';
        document.body.style.overflow = 'hidden';
      }

      function cerrarModalEquipo(){
        document.getElementById('modal-equipo-overlay').style.display = 'none';
        document.body.style.overflow = '';
        /* limpiar */
        document.getElementById('eq-buscar').value = '';
        document.getElementById('eq-nombre').value = '';
        document.getElementById('eq-inscrito-id').value = '';
        document.getElementById('eq-inscrito-sel').style.display = 'none';
        document.getElementById('eq-resultados').innerHTML = '';
        document.getElementById('eq-error').style.display = 'none';
        document.getElementById('eq-nombre-msg').style.display = 'none';
      }

      function cerrarModalExitoEquipo(){
        document.getElementById('modal-equipo-exito').style.display = 'none';
        document.body.style.overflow = '';
      }

      /* buscador inscrito */
      var eqTimer;
      document.addEventListener('DOMContentLoaded', function(){
        var inp = document.getElementById('eq-buscar');
        if(!inp) return;
        inp.addEventListener('input', function(){
          clearTimeout(eqTimer);
          var q = this.value.trim();
          document.getElementById('eq-inscrito-id').value = '';
          document.getElementById('eq-inscrito-sel').style.display = 'none';
          if(q.length < 2){ document.getElementById('eq-resultados').innerHTML = ''; return; }
          eqTimer = setTimeout(function(){
            var fd = new FormData();
            fd.append('accion', 'buscar_inscrito_equipo');
            fd.append('q', q);
            fetch('guardar_inscripcion.php', {method:'POST', body:fd})
            .then(function(r){ return r.json(); })
            .then(function(data){
              var res = document.getElementById('eq-resultados');
              if(!data.ok || !data.inscritos.length){
                res.innerHTML = '<p style="font-size:12px;color:#9ca3af;margin-top:4px;"><i class="fa-solid fa-circle-info"></i> No encontrado. Debes estar inscrito al encuentro.</p>';
                return;
              }
              res.innerHTML = data.inscritos.map(function(i){
                return '<div onclick="seleccionarInscritoEquipo('+i.id+',\''+i.nombre+' '+i.apellido+'\')" '+
                  'style="padding:9px 12px;border:1px solid #e4e8f0;border-radius:8px;margin-bottom:4px;cursor:pointer;font-size:13px;transition:background .15s;" '+
                  'onmouseover="this.style.background=\'#f0f4ff\'" onmouseout="this.style.background=\'\'">'+
                  '<i class="fa-solid fa-user" style="color:#0077b6;margin-right:6px;"></i>'+
                  i.nombre+' '+i.apellido+' — <small style="color:#9ca3af;">'+i.carnet+'</small></div>';
              }).join('');
            });
          }, 350);
        });

        /* validar nombre en tiempo real */
        var inpNombre = document.getElementById('eq-nombre');
        if(inpNombre){
          inpNombre.addEventListener('input', function(){
            var msg = document.getElementById('eq-nombre-msg');
            var v = this.value.trim();
            if(v.length < 3){
              msg.style.display = 'block';
              msg.style.color = '#ef4444';
              msg.textContent = 'Mínimo 3 caracteres';
              return;
            }
            /* verificar disponibilidad */
            var fd = new FormData();
            fd.append('accion', 'verificar_nombre_equipo');
            fd.append('nombre', v);
            fetch('guardar_inscripcion.php', {method:'POST', body:fd})
            .then(function(r){ return r.json(); })
            .then(function(data){
              msg.style.display = 'block';
              if(data.disponible){
                msg.style.color = '#10b981';
                msg.innerHTML = '<i class="fa-solid fa-check"></i> Nombre disponible';
              } else {
                msg.style.color = '#ef4444';
                msg.innerHTML = '<i class="fa-solid fa-xmark"></i> Ese nombre ya está registrado';
              }
            });
          });
        }
      });

      function seleccionarInscritoEquipo(id, nombre){
        document.getElementById('eq-inscrito-id').value = id;
        document.getElementById('eq-buscar').value = nombre;
        document.getElementById('eq-inscrito-nombre').textContent = nombre;
        document.getElementById('eq-inscrito-sel').style.display = 'block';
        document.getElementById('eq-resultados').innerHTML = '';
      }

      function registrarEquipo(){
        var inscritoId = document.getElementById('eq-inscrito-id').value;
        var nombre     = document.getElementById('eq-nombre').value.trim();
        var errDiv     = document.getElementById('eq-error');
        var btn        = document.getElementById('eq-btn-registrar');
      
        errDiv.style.display = 'none';

        if(!inscritoId){
          errDiv.style.display = 'block';
          errDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Debes seleccionar un inscrito válido del buscador.';
          return;
        }
        if(nombre.length < 3){
          errDiv.style.display = 'block';
          errDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> El nombre del equipo debe tener al menos 3 caracteres.';
          return;
        }
      
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Registrando...';

        var fd = new FormData();
        fd.append('accion',      'registrar_equipo');
        fd.append('inscrito_id', inscritoId);
        fd.append('nombre',      nombre);

        fetch('guardar_inscripcion.php', {method:'POST', body:fd})
        .then(function(r){ return r.json(); })
        .then(function(data){
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-futbol"></i> Inscribir mi equipo';
          if(data.ok){
            cerrarModalEquipo();
            document.getElementById('eq-exito-nombre').textContent = nombre;
            document.getElementById('modal-equipo-exito').style.display = 'grid';
            document.body.style.overflow = 'hidden';
          } else {
            errDiv.style.display = 'block';
            errDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> ' + data.msg;
          }
        })
        .catch(function(){
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-futbol"></i> Inscribir mi equipo';
          errDiv.style.display = 'block';
          errDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Error de conexión. Intenta de nuevo.';
        });
      }
      </script>



    </div>
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
          if(!isset($conn)) require_once('includes/funciones/bd_conexion.php');

          $sql = "SELECT e.*, 
                         t.titulo        AS tema,
                         ex.nombre       AS expositor_nombre,
                         ex.apellido     AS expositor_apellido,
                         ex.rango        AS expositor_rango,
                         m.nombre        AS moderador_nombre,
                         m.apellido      AS moderador_apellido
                  FROM eventos e
                  LEFT JOIN temas        t  ON e.id_tema      = t.id_tema
                  LEFT JOIN expositores  ex ON e.id_expositor = ex.id_expositor
                  LEFT JOIN moderadores  m  ON e.id_moderador = m.id_moderador
                  ORDER BY e.id_dia, e.hora_inicio";

          $resultado = $conn->query($sql);

          $eventos = [ 1 => [], 2 => [], 3 => [] ];
          while($row = $resultado->fetch_assoc()){
            $eventos[$row['id_dia']][] = $row;
          }
          ?>

          <!-- VIERNES -->
          <div id="viernes" class="info-curso">
            <?php foreach($eventos[1] as $evento): ?>
              <div class="detalle-evento">

                <div class="evento-header">
                  <span class="evento-tipo"><?php echo htmlspecialchars($evento['tipo_evento']); ?></span>
                  <span class="evento-hora">
                    <i class="fa-solid fa-clock"></i>
                    <?php echo substr($evento['hora_inicio'],0,5); ?> — <?php echo substr($evento['hora_fin'],0,5); ?>
                  </span>
                </div>

                <?php if (!empty($evento['tema'])): ?>
                  <p class="evento-tema">
                    <i class="fa-solid fa-book-open"></i>
                    <?php echo htmlspecialchars($evento['tema']); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($evento['expositor_nombre'])): ?>
                  <p class="evento-expositor">
                    <i class="fa-solid fa-user-tie"></i>
                    <span class="exp-rango"><?php echo htmlspecialchars($evento['expositor_rango']); ?></span>
                    <?php echo htmlspecialchars($evento['expositor_nombre'].' '.$evento['expositor_apellido']); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($evento['moderador_nombre'])): ?>
                  <p class="evento-moderador">
                    <i class="fa-solid fa-microphone"></i>
                    Moderador. <?php echo htmlspecialchars($evento['moderador_nombre'].' '.$evento['moderador_apellido']); ?>
                  </p>
                <?php endif; ?>

              </div>
            <?php endforeach; ?>
            <a href="calendario.php" class="button">Ver calendario completo</a>
          </div>

          <!-- SABADO -->
          <div id="sabado" class="info-curso">
            <?php foreach($eventos[2] as $evento): ?>
              <div class="detalle-evento">
                <div class="evento-header">
                  <span class="evento-tipo"><?php echo htmlspecialchars($evento['tipo_evento']); ?></span>
                  <span class="evento-hora">
                    <i class="fa-solid fa-clock"></i>
                    <?php echo substr($evento['hora_inicio'],0,5); ?> — <?php echo substr($evento['hora_fin'],0,5); ?>
                  </span>
                </div>
                <?php if (!empty($evento['tema'])): ?>
                  <p class="evento-tema"><i class="fa-solid fa-book-open"></i><?php echo htmlspecialchars($evento['tema']); ?></p>
                <?php endif; ?>
                <?php if (!empty($evento['expositor_nombre'])): ?>
                  <p class="evento-expositor">
                    <i class="fa-solid fa-user-tie"></i>
                    <span class="exp-rango"><?php echo htmlspecialchars($evento['expositor_rango']); ?></span>
                    <?php echo htmlspecialchars($evento['expositor_nombre'].' '.$evento['expositor_apellido']); ?>
                  </p>
                <?php endif; ?>
                <?php if (!empty($evento['moderador_nombre'])): ?>
                  <p class="evento-moderador">
                    <i class="fa-solid fa-microphone"></i>
                    Moderador. <?php echo htmlspecialchars($evento['moderador_nombre'].' '.$evento['moderador_apellido']); ?>
                  </p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
            <a href="calendario.php" class="button">Ver calendario completo</a>
          </div>

          <!-- DOMINGO -->
          <div id="domingo" class="info-curso">
            <?php foreach($eventos[3] as $evento): ?>
              <div class="detalle-evento">
                <div class="evento-header">
                  <span class="evento-tipo"><?php echo htmlspecialchars($evento['tipo_evento']); ?></span>
                  <span class="evento-hora">
                    <i class="fa-solid fa-clock"></i>
                    <?php echo substr($evento['hora_inicio'],0,5); ?> — <?php echo substr($evento['hora_fin'],0,5); ?>
                  </span>
                </div>
                <?php if (!empty($evento['tema'])): ?>
                  <p class="evento-tema"><i class="fa-solid fa-book-open"></i><?php echo htmlspecialchars($evento['tema']); ?></p>
                <?php endif; ?>
                <?php if (!empty($evento['expositor_nombre'])): ?>
                  <p class="evento-expositor">
                    <i class="fa-solid fa-user-tie"></i>
                    <span class="exp-rango"><?php echo htmlspecialchars($evento['expositor_rango']); ?></span>
                    <?php echo htmlspecialchars($evento['expositor_nombre'].' '.$evento['expositor_apellido']); ?>
                  </p>
                <?php endif; ?>
                <?php if (!empty($evento['moderador_nombre'])): ?>
                  <p class="evento-moderador">
                    <i class="fa-solid fa-microphone"></i>
                    Moderador <?php echo htmlspecialchars($evento['moderador_nombre'].' '.$evento['moderador_apellido']); ?>
                  </p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
            <a href="calendario.php" class="button">Ver calendario completo</a>
          </div>

        </div>
      </div>
    </div>
  </section>


  <section class="invitados seccion">
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
              <img src="img/<?php echo htmlspecialchars($exp['imagen']); ?>" 
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
  <section class="precios seccion seccion-fondo-azul">
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
                   ROUND(p.precio - (p.precio * COALESCE(d.porcentaje,0) / 100), 0) AS precio_final
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
                <p class="precio-original">Bs. <?php echo number_format($paq['precio'],0); ?></p>
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
                <p class="numero">Bs. <?php echo number_format($pf,0); ?></p>
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

  <?php
  $aloj1_nombre = $cfg['mapa_aloj1_nombre'] ?? 'Alojamiento 1';
  $aloj1_link   = $cfg['mapa_aloj1_link']   ?? '#';
  $aloj2_nombre = $cfg['mapa_aloj2_nombre'] ?? 'Alojamiento 2';
  $aloj2_link   = $cfg['mapa_aloj2_link']   ?? '#';
  $evento_link  = $cfg['mapa_evento_link']  ?? '#';
  ?>
  <section class="seccion-mapa seccion-fondo-azul">
    <h2>Ubicación del Evento</h2>
    <p class="texto-mapa">
      <i class="fa-solid fa-location-dot"></i>
      Haz click en el mapa para abrir en Google Maps
    </p>
    <div class="contenedor-mapa">
      <a href="<?php echo htmlspecialchars($evento_link); ?>" target="_blank" class="link-mapa">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3203.750919665405!2d-64.72678242144536!3d-21.541682100000003!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9406475233b071dd%3A0xdbb1f8dbcd3f54bc!2sPunto%20de%20Venta%20Loter%C3%ADa%20%22Kiosko%20Ex.%20Terminal%22!5e1!3m2!1ses-419!2sbo!4v1776045310858!5m2!1ses-419!2sbo"
                width="100%" height="100%" style="border:0;pointer-events:none;" allowfullscreen="" loading="lazy"></iframe>
        <div class="overlay-mapa">
          <i class="fa-solid fa-location-dot"></i>
          <p>Click para abrir en Google Maps</p>
        </div>
      </a>
    </div>

    <!-- LUGARES DE ALOJAMIENTO -->
    <div class="lugares-alojamiento">
      <a href="<?php echo htmlspecialchars($aloj1_link); ?>" target="_blank" class="lugar-item">
        <i class="fa-solid fa-bed"></i>
        <span><?php echo htmlspecialchars($aloj1_nombre); ?></span>
        <small><i class="fa-solid fa-arrow-up-right-from-square"></i> Ver en Maps</small>
      </a>
      <a href="<?php echo htmlspecialchars($aloj2_link); ?>" target="_blank" class="lugar-item">
        <i class="fa-solid fa-bed"></i>
        <span><?php echo htmlspecialchars($aloj2_nombre); ?></span>
        <small><i class="fa-solid fa-arrow-up-right-from-square"></i> Ver en Maps</small>
      </a>
    </div>
  </section>


<section class="seccion seccion-cuenta-regresiva">
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

  <!-- ══ COMISIONES ══ -->
  <section class="seccion-comisiones">
    <div class="contenedor">
      <span class="com-tag">— Organización —</span>
      <h2 class="com-titulo">Comisiones del Encuentro</h2>

      <?php
        if(!isset($conn)) require_once('includes/funciones/bd_conexion.php');
        $res_com = $conn->query("
          SELECT c.id, c.nombre, c.icono,
                 GROUP_CONCAT(e.nombre ORDER BY e.id SEPARATOR '||') AS nombres,
                 GROUP_CONCAT(e.celular ORDER BY e.id SEPARATOR '||') AS celulares
          FROM comisiones c
          LEFT JOIN comision_encargados e ON e.comision_id = c.id
          WHERE c.activo = 1
          GROUP BY c.id
          ORDER BY c.orden
        ");
      ?>

      <div class="com-grid">
        <?php while($com = $res_com->fetch_assoc()): ?>
          <div class="com-card">
            <div class="com-card-header">
              <div class="com-icono">
                <i class="<?php echo htmlspecialchars($com['icono']); ?>"></i>
              </div>
              <h3 class="com-nombre"><?php echo htmlspecialchars($com['nombre']); ?></h3>
            </div>

            <?php if($com['nombres']): ?>
              <ul class="com-encargados">
                <?php
                  $nombres   = explode('||', $com['nombres']);
                  $celulares = explode('||', $com['celulares']);
                  foreach($nombres as $k => $nom):
                    $cel = $celulares[$k] ?? '';
                    $wa  = 'https://wa.me/591' . preg_replace('/[^0-9]/', '', $cel);
                ?>
                  <li class="com-encargado">
                    <div class="com-enc-info">
                      <i class="fa-solid fa-user"></i>
                      <span><?php echo htmlspecialchars($nom); ?></span>
                    </div>
                    <?php if($cel): ?>
                      <a href="<?php echo $wa; ?>" target="_blank" class="com-wa-btn" title="WhatsApp">
                        <i class="fa-brands fa-whatsapp"></i>
                        <?php echo htmlspecialchars($cel); ?>
                      </a>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="com-sin-enc">Por definir</p>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>
  
<?php include_once 'includes/templates/footer.php'; ?>