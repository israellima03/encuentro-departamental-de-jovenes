<?php include_once 'includes/templates/header.php'; ?>

  <section class="seccion contenedor">
    <h2>Registro de usuarios</h2>
    <form id="registro" class="registro" action="index.html" method="post">

      <!-- DATOS PERSONALES -->
      <div id="datos-usuario" class="registro caja">
        <div class="campo">
          <label for="nombre">Nombre</label>
          <input type="text" id="nombre" name="nombre" placeholder="Tu Nombre">
        </div>
        <div class="campo">
          <label for="apellido">Apellido</label>
          <input type="text" id="apellido" name="apellido" placeholder="Tu Apellido">
        </div>
        <div class="campo">
          <label for="celular">Celular</label>
          <input type="text" id="celular" name="celular" placeholder="Tu Celular">
        </div>
        <div class="campo">
          <label for="carnet">Carnet de Identidad</label>
          <input type="text" id="carnet" name="carnet" placeholder="Tu Carnet">
        </div>
        <div class="campo">
          <label for="ministerio">Nombre del Ministerio</label>
          <input type="text" id="ministerio" name="ministerio" placeholder="Tu Ministerio">
        </div>
        <div class="campo">
          <label for="iglesia">Iglesia</label>
          <input type="text" id="iglesia" name="iglesia" placeholder="Tu Iglesia">
        </div>
        <div class="campo">
          <label for="distrito">Distrito</label>
          <input type="text" id="distrito" name="distrito" placeholder="Tu Distrito">
        </div>
        <div id="error"></div>
      </div><!--datos usuario-->

      <!-- PAQUETES - solo uno seleccionable -->
      <div id="paquetes" class="paquetes">
        <h3>Elige tu paquete</h3>
        <p class="texto-paquete">Selecciona solo un paquete</p>
        <ul class="lista-precios">
          <li>
            <div class="tabla-precio">
              <label class="paquete-label">
                <input type="radio" name="paquete" value="30" id="pase_dia">
                <h3>Pase solo encuentro</h3>
              </label>
              <p class="numero">30bs</p>
              <ul>
                <li>Recuerdo del encuentro</li>
                <li>Todas las conferencias</li>
                <li>Todos los Talleres</li>
              </ul>
            </div>
          </li>
          <li>
            <div class="tabla-precio">
              <label class="paquete-label">
                <input type="radio" name="paquete" value="300" id="pase_completo">
                <h3>Pase + bus ida y vuelta</h3>
              </label>
              <p class="numero">300bs</p>
              <ul>
                <li>Bus ida y vuelta</li>
                <li>Todas las conferencias</li>
                <li>Todos los Talleres</li>
              </ul>
            </div>
          </li>
          <li>
            <div class="tabla-precio">
              <label class="paquete-label">
                <input type="radio" name="paquete" value="400" id="pase_dosdias">
                <h3>Pase + habitacion</h3>
              </label>
              <p class="numero">400bs</p>
              <ul>
                <li>Habitacion para el encuentro</li>
                <li>Recuerdo del encuentro</li>
                <li>Todos los Talleres</li>
              </ul>
            </div>
          </li>
        </ul>
      </div>

      <!-- EXTRAS Y PAGO -->
      <div id="resumen" class="resumen">
        <h3>Extras y Pago</h3>
        <div class="caja">
          <div class="extras">
            <div class="orden">
              <label for="camisa_evento">Polera del evento — 90bs <small>(promocion 7% dto.)</small></label>
              <input type="number" min="0" id="camisa_evento" placeholder="0">
            </div>
            <div class="orden">
              <label for="etiquetas">Paquete de 10 Etiquetas — 10bs</label>
              <input type="number" min="0" id="etiquetas" placeholder="0">
            </div>
            <div class="order">
              <label for="regalo">Seleccione un regalo</label>
              <select id="regalo" required>
                <option value="">-- Seleccione un regalo --</option>
                <option value="ETI">Etiquetas</option>
                <option value="PUL">Pulseras</option>
                <option value="PLU">Plumas</option>
              </select>
            </div>
            <input type="button" id="calcular" class="button" value="Calcular total">
          </div>

          <div class="total">
            <p>Resumen:</p>
            <div id="lista-productos"></div>
            <p>Total a pagar:</p>
            <div id="suma-total"></div>
            <input id="btnRegistro" type="submit" class="button" value="Confirmar registro">
          </div>
        </div>
      </div>

    </form>
    
  </section>


 <?php include_once 'includes/templates/footer.php'; ?>