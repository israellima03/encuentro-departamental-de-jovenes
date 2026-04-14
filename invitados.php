
<?php include_once 'includes/templates/header.php'; ?>

<section class="seccion contenedor">
  <h2>Nuestros Invitados</h2>

  <?php
    $expositores = [];
    try {
      require_once('includes/funciones/bd_conexion.php');

      $sql = "SELECT id_expositor, nombre, apellido, rango, descripcion, imagen
              FROM expositores
              ORDER BY apellido, nombre";

      $resultado = $conn->query($sql);

      if (!$resultado) {
        throw new Exception('Error en consulta: ' . $conn->error);
      }

      while ($fila = $resultado->fetch_assoc()) {
        $expositores[] = $fila;
      }

      $conn->close();

    } catch (\Exception $e) {
      echo '<p class="error-bd">' . htmlspecialchars($e->getMessage()) . '</p>';
    }
  ?>

  <div class="grid-invitados">
    <?php foreach ($expositores as $exp): ?>

      <div class="card-invitado">

        <!-- FOTO -->
        <div class="card-foto-wrap">
          <?php if (!empty($exp['imagen'])): ?>
            <img
              src="<?php echo htmlspecialchars($exp['imagen']); ?>"
              alt="<?php echo htmlspecialchars($exp['nombre'] . ' ' . $exp['apellido']); ?>"
              class="card-foto"
            >
          <?php else: ?>
            <div class="card-foto-placeholder">
              <i class="fa-solid fa-user"></i>
            </div>
          <?php endif; ?>

          <!-- RANGO BADGE SOBRE LA FOTO -->
          <span class="card-rango-badge">
            <?php echo htmlspecialchars($exp['rango']); ?>
          </span>
        </div>

        <!-- CUERPO -->
        <div class="card-cuerpo">
          <h3 class="card-nombre">
            <?php echo htmlspecialchars($exp['nombre'] . ' ' . $exp['apellido']); ?>
          </h3>

          <div class="card-separador"></div>

          <?php if (!empty($exp['descripcion'])): ?>
            <p class="card-descripcion">
              <?php echo htmlspecialchars($exp['descripcion']); ?>
            </p>
          <?php endif; ?>
        </div>

      </div>

    <?php endforeach; ?>

    <?php if (empty($expositores)): ?>
      <p class="sin-datos">No hay expositores registrados aún.</p>
    <?php endif; ?>
  </div>

</section>

<?php include_once 'includes/templates/footer.php'; ?>