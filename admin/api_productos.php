<?php
require_once('funciones/sesiones.php');
usuario_autentificado();
header('Content-Type: application/json');
require_once('../includes/funciones/bd_conexion.php');

$accion = trim($_GET['accion'] ?? $_POST['accion'] ?? '');

switch($accion){

  /* ── LISTAR PRODUCTOS ── */
  case 'listar_productos':
    $res = $conn->query("
      SELECT id, nombre, precio, tipo, cupos AS cupo_total, cupos_disponibles, imagen
      FROM productos ORDER BY nombre
    ");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'productos'=>$rows]);
    break;

  /* ── EDITAR PRODUCTO (cupos, imagen) ── */
  case 'editar_producto':
    $id              = intval($_POST['id'] ?? 0);
    $cupos           = intval($_POST['cupos'] ?? 0);
    $cupos_disp      = intval($_POST['cupos_disponibles'] ?? 0);
    $nombre          = trim($_POST['nombre'] ?? '');
    $precio          = floatval($_POST['precio'] ?? 0);

    /* subir imagen si viene */
    $imagen_sql = '';
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK){
      $ext  = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
      $permitidos = ['jpg','jpeg','png','webp'];
      if(in_array($ext, $permitidos)){
        $nombre_img = 'prod_' . $id . '_' . time() . '.' . $ext;
        $destino    = '../img/' . $nombre_img;
        if(move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)){
          $imagen_sql = ", imagen = '$nombre_img'";
        }
      }
    }

    $stmt = $conn->prepare("
      UPDATE productos SET nombre=?, precio=?, cupos=?, cupos_disponibles=? $imagen_sql
      WHERE id=?
    ");
    $stmt->bind_param('sdiii', $nombre, $precio, $cupos, $cupos_disp, $id);
    $stmt->execute();
    echo json_encode(['ok'=>true,'msg'=>'Producto actualizado']);
    $stmt->close();
    break;

  /* ── CREAR PRODUCTO ── */
  case 'crear_producto':
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $tipo   = trim($_POST['tipo'] ?? '');
    $cupos  = intval($_POST['cupos'] ?? 0);
    if(!$nombre){ echo json_encode(['ok'=>false,'msg'=>'Nombre requerido']); exit; }
    $stmt = $conn->prepare("INSERT INTO productos (nombre,precio,tipo,cupos,cupos_disponibles) VALUES(?,?,?,?,?)");
    $stmt->bind_param('sdsii',$nombre,$precio,$tipo,$cupos,$cupos);
    $stmt->execute();
    echo json_encode(['ok'=>true,'msg'=>'Producto creado','id'=>$conn->insert_id]);
    $stmt->close();
    break;

  /* ── LISTAR PEDIDOS ── */
  case 'listar_pedidos':
    $res = $conn->query("
      SELECT
        ip.id AS pedido_id,
        i.id  AS inscrito_id,
        i.nombre, i.apellido, i.carnet,
        ins.estado_pago,
        ins.id AS inscripcion_id,
        pr.id  AS producto_id,
        pr.nombre AS producto,
        pr.precio AS precio_unit,
        ip.talla, ip.genero, ip.cantidad,
        ROUND(pr.precio * ip.cantidad, 2) AS subtotal
      FROM inscripcion_productos ip
      INNER JOIN inscripciones ins ON ins.id  = ip.inscripcion_id
      INNER JOIN inscritos      i   ON i.id   = ins.inscrito_id
      INNER JOIN productos      pr  ON pr.id  = ip.producto_id
      ORDER BY i.apellido, i.nombre
    ");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'pedidos'=>$rows]);
    break;

  /* ── LISTAR TALLAS DE UN PRODUCTO ── */
  case 'listar_tallas':
    $pid = intval($_GET['producto_id'] ?? 0);
    $res = $conn->query("SELECT * FROM producto_tallas WHERE producto_id=$pid ORDER BY genero,talla");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'tallas'=>$rows]);
    break;

  /* ── GUARDAR TALLA ── */
  case 'guardar_talla':
    $id      = intval($_POST['id'] ?? 0);
    $pid     = intval($_POST['producto_id'] ?? 0);
    $talla   = trim($_POST['talla'] ?? '');
    $genero  = trim($_POST['genero'] ?? 'hombre');
    $ancho   = floatval($_POST['ancho_cm'] ?? 0);
    $alto    = floatval($_POST['alto_cm']  ?? 0);

    if($id > 0){
      $stmt = $conn->prepare("UPDATE producto_tallas SET talla=?,genero=?,ancho_cm=?,alto_cm=? WHERE id=?");
      $stmt->bind_param('ssddi',$talla,$genero,$ancho,$alto,$id);
    } else {
      $stmt = $conn->prepare("INSERT INTO producto_tallas (producto_id,talla,genero,ancho_cm,alto_cm) VALUES(?,?,?,?,?)");
      $stmt->bind_param('issdd',$pid,$talla,$genero,$ancho,$alto);
    }
    $stmt->execute();
    echo json_encode(['ok'=>true,'msg'=>'Talla guardada']);
    $stmt->close();
    break;

  /* ── ELIMINAR TALLA ── */
  case 'eliminar_talla':
    $id = intval($_POST['id'] ?? 0);
    $conn->query("DELETE FROM producto_tallas WHERE id=$id");
    echo json_encode(['ok'=>true]);
    break;

  /* ── BUSCAR INSCRITOS CONFIRMADOS ── */
  case 'buscar_inscritos':
    $q    = '%' . trim($_GET['q'] ?? '') . '%';
    $stmt = $conn->prepare("
      SELECT i.id, i.nombre, i.apellido, i.carnet, ins.id AS inscripcion_id
      FROM inscritos i
      INNER JOIN inscripciones ins ON ins.inscrito_id = i.id
      WHERE ins.estado_pago = 'confirmado'
        AND (i.nombre LIKE ? OR i.apellido LIKE ? OR i.carnet LIKE ?)
      LIMIT 10
    ");
    $stmt->bind_param('sss',$q,$q,$q);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(['ok'=>true,'inscritos'=>$rows]);
    break;

  /* ── AÑADIR PEDIDO A INSCRITO ── */
  case 'añadir_pedido':
    $inscripcion_id = intval($_POST['inscripcion_id'] ?? 0);
    $producto_id    = intval($_POST['producto_id']    ?? 0);
    $talla          = trim($_POST['talla']    ?? '');
    $genero         = trim($_POST['genero']   ?? 'hombre');
    $cantidad       = intval($_POST['cantidad'] ?? 1);

    if(!$inscripcion_id || !$producto_id || $cantidad < 1){
      echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']); exit;
    }

    /* verificar cupos */
    $r = $conn->query("SELECT cupos_disponibles FROM productos WHERE id=$producto_id LIMIT 1")->fetch_assoc();
    if(!$r || $r['cupos_disponibles'] < $cantidad){
      echo json_encode(['ok'=>false,'msg'=>'Sin cupos disponibles']); exit;
    }

    $stmt = $conn->prepare("INSERT INTO inscripcion_productos (inscripcion_id,producto_id,cantidad,talla,genero) VALUES(?,?,?,?,?)");
    $stmt->bind_param('iiiss',$inscripcion_id,$producto_id,$cantidad,$talla,$genero);
    if($stmt->execute()){
      $conn->query("UPDATE productos SET cupos_disponibles=cupos_disponibles-$cantidad WHERE id=$producto_id");

      /* actualizar precio_productos en inscripciones */
      $conn->query("
        UPDATE inscripciones SET precio_productos = (
          SELECT COALESCE(SUM(p.precio * ip.cantidad),0)
          FROM inscripcion_productos ip
          INNER JOIN productos p ON p.id = ip.producto_id
          WHERE ip.inscripcion_id = $inscripcion_id
        ),
        precio_final = precio_paquete + (
          SELECT COALESCE(SUM(p.precio * ip.cantidad),0)
          FROM inscripcion_productos ip
          INNER JOIN productos p ON p.id = ip.producto_id
          WHERE ip.inscripcion_id = $inscripcion_id
        ) - COALESCE(descuento_aplicado,0)
        WHERE id = $inscripcion_id
      ");
      echo json_encode(['ok'=>true,'msg'=>'Pedido añadido correctamente']);
    } else {
      echo json_encode(['ok'=>false,'msg'=>'Error al guardar']);
    }
    $stmt->close();
    break;

  /* ── ELIMINAR PEDIDO ── */
  /* ── EDITAR PEDIDO ── */
  case 'editar_pedido':
    $id       = intval($_POST['id']       ?? 0);
    $cantidad = intval($_POST['cantidad'] ?? 0);
    $talla    = trim($_POST['talla']      ?? '');

    if(!$id || $cantidad < 1){
      echo json_encode(['ok'=>false,'msg'=>'Datos invalidos']); exit;
    }

    /* obtener cantidad anterior para ajustar cupos */
    $ant = $conn->query("SELECT cantidad, producto_id FROM inscripcion_productos WHERE id=$id LIMIT 1")->fetch_assoc();
    if(!$ant){ echo json_encode(['ok'=>false,'msg'=>'Pedido no encontrado']); exit; }

    $diff = $cantidad - intval($ant['cantidad']);
    $pid  = intval($ant['producto_id']);

    /* verificar cupos si aumenta cantidad */
    if($diff > 0){
      $r = $conn->query("SELECT cupos_disponibles FROM productos WHERE id=$pid LIMIT 1")->fetch_assoc();
      if(!$r || $r['cupos_disponibles'] < $diff){
        echo json_encode(['ok'=>false,'msg'=>'Sin cupos disponibles']); exit;
      }
    }

    $stmt = $conn->prepare("UPDATE inscripcion_productos SET cantidad=?, talla=? WHERE id=?");
    $stmt->bind_param('isi', $cantidad, $talla, $id);
    if($stmt->execute()){
      /* ajustar cupos del producto */
      $conn->query("UPDATE productos SET cupos_disponibles = cupos_disponibles - $diff WHERE id=$pid");

      /* recalcular precio en inscripciones */
      $iid_r = $conn->query("SELECT inscripcion_id FROM inscripcion_productos WHERE id=$id LIMIT 1")->fetch_assoc();
      if($iid_r){
        $iid = intval($iid_r['inscripcion_id']);
        $conn->query("
          UPDATE inscripciones SET precio_productos = (
            SELECT COALESCE(SUM(p.precio * ip.cantidad),0)
            FROM inscripcion_productos ip
            INNER JOIN productos p ON p.id = ip.producto_id
            WHERE ip.inscripcion_id = $iid
          ),
          precio_final = precio_paquete + (
            SELECT COALESCE(SUM(p.precio * ip.cantidad),0)
            FROM inscripcion_productos ip
            INNER JOIN productos p ON p.id = ip.producto_id
            WHERE ip.inscripcion_id = $iid
          ) - COALESCE(descuento_aplicado,0)
          WHERE id = $iid
        ");
      }
      echo json_encode(['ok'=>true,'msg'=>'Pedido actualizado']);
    } else {
      echo json_encode(['ok'=>false,'msg'=>'Error al actualizar']);
    }
    $stmt->close();
    break;

  default:
    echo json_encode(['ok'=>false,'msg'=>'Accion no valida']);
}