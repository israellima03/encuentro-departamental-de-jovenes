<?php
require_once('funciones/sesiones.php');
usuario_autentificado();
header('Content-Type: application/json');
require_once('../includes/funciones/bd_conexion.php');

$accion = trim($_GET['accion'] ?? $_POST['accion'] ?? '');

switch($accion){

  /* ══════════════════════════════
     PAQUETES
  ══════════════════════════════ */
  case 'listar_paquetes':
    $res = $conn->query("
      SELECT p.id, p.nombre, p.precio, p.cupo_total, p.cupos_disponibles,
             d.id AS descuento_id, d.nombre AS descuento_nombre, d.porcentaje
      FROM paquetes p
      LEFT JOIN paquete_descuentos pd ON pd.paquete_id = p.id
      LEFT JOIN descuentos d ON d.id = pd.descuento_id AND d.activo = 1
      ORDER BY p.precio
    ");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'paquetes'=>$rows]);
    break;

  case 'guardar_paquete':
    $id        = intval($_POST['id'] ?? 0);
    $nombre    = trim($_POST['nombre'] ?? '');
    $precio    = floatval($_POST['precio'] ?? 0);
    $cupos     = intval($_POST['cupos'] ?? 0);
    $cupos_d   = intval($_POST['cupos_disponibles'] ?? 0);
    $desc_id   = intval($_POST['descuento_id'] ?? 0);

    if(!$nombre){ echo json_encode(['ok'=>false,'msg'=>'Nombre requerido']); exit; }

    if($id > 0){
      $stmt = $conn->prepare("UPDATE paquetes SET nombre=?,precio=?,cupo_total=?,cupos_disponibles=? WHERE id=?");
      $stmt->bind_param('sdiii',$nombre,$precio,$cupos,$cupos_d,$id);
      $stmt->execute();
      $stmt->close();
      /* actualizar descuento asignado */
      $conn->query("DELETE FROM paquete_descuentos WHERE paquete_id=$id");
      if($desc_id > 0){
        $conn->query("INSERT INTO paquete_descuentos (paquete_id,descuento_id) VALUES($id,$desc_id)");
      }
      echo json_encode(['ok'=>true,'msg'=>'Paquete actualizado']);
    } else {
      $stmt = $conn->prepare("INSERT INTO paquetes (nombre,precio,cupo_total,cupos_disponibles) VALUES(?,?,?,?)");
      $stmt->bind_param('sdii',$nombre,$precio,$cupos,$cupos_d);
      $stmt->execute();
      $nuevo_id = $conn->insert_id;
      $stmt->close();
      if($desc_id > 0){
        $conn->query("INSERT INTO paquete_descuentos (paquete_id,descuento_id) VALUES($nuevo_id,$desc_id)");
      }
      echo json_encode(['ok'=>true,'msg'=>'Paquete creado','id'=>$nuevo_id]);
    }
    break;

  case 'eliminar_paquete':
      $id = intval($_POST['id'] ?? 0);
      $r = $conn->query("SELECT COUNT(*) AS c FROM inscripciones WHERE paquete_id=$id")->fetch_assoc();
      if($r && $r['c'] > 0){
          echo json_encode(['ok'=>false,'msg'=>'No se puede eliminar: tiene '.$r['c'].' inscripcion(es)']);
      } else {
          $conn->query("DELETE FROM paquete_descuentos WHERE paquete_id=$id");
          $conn->query("DELETE FROM paquetes WHERE id=$id");
          echo json_encode(['ok'=>true]);
      }
      break;
  /* ══════════════════════════════
     DESCUENTOS / PROMOCIONES
  ══════════════════════════════ */
  case 'listar_descuentos':
    $res = $conn->query("SELECT * FROM descuentos ORDER BY fecha_inicio DESC");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'descuentos'=>$rows]);
    break;

  case 'guardar_descuento':
      $id         = intval($_POST['id'] ?? 0);
      $nombre     = trim($_POST['nombre'] ?? '');
      $porcentaje = floatval($_POST['porcentaje'] ?? 0);
      $activo     = intval($_POST['activo'] ?? 1);
      $inicio     = trim($_POST['fecha_inicio'] ?? '') ?: null;
      $fin        = trim($_POST['fecha_fin'] ?? '') ?: null;

      if(!$nombre){ echo json_encode(['ok'=>false,'msg'=>'Nombre requerido']); exit; }

      if($id > 0){
        $stmt = $conn->prepare("UPDATE descuentos SET nombre=?,porcentaje=?,activo=?,fecha_inicio=?,fecha_fin=? WHERE id=?");
        $stmt->bind_param('ssissi',$nombre,$porcentaje,$activo,$inicio,$fin,$id);
        $stmt->execute(); $stmt->close();
        echo json_encode(['ok'=>true,'msg'=>'Promoción actualizada']);
      } else {
        $stmt = $conn->prepare("INSERT INTO descuentos (nombre,porcentaje,activo,fecha_inicio,fecha_fin) VALUES(?,?,?,?,?)");
        $stmt->bind_param('ssiss',$nombre,$porcentaje,$activo,$inicio,$fin);
        $stmt->execute();
        echo json_encode(['ok'=>true,'msg'=>'Promoción creada','id'=>$conn->insert_id]);
        $stmt->close();
      }
      break;
  case 'eliminar_descuento':
    $id = intval($_POST['id'] ?? 0);
    $conn->query("DELETE FROM paquete_descuentos WHERE descuento_id=$id");
    $conn->query("DELETE FROM descuentos WHERE id=$id");
    echo json_encode(['ok'=>true]);
    break;

  /* ══════════════════════════════
     TIPOS DE INSCRITO
  ══════════════════════════════ */
  case 'listar_tipos':
    $res = $conn->query("SELECT * FROM tipos_inscrito ORDER BY nombre");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'tipos'=>$rows]);
    break;

  case 'guardar_tipo':
    $id     = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    if(!$nombre){ echo json_encode(['ok'=>false,'msg'=>'Nombre requerido']); exit; }
    if($id > 0){
      $stmt = $conn->prepare("UPDATE tipos_inscrito SET nombre=? WHERE id=?");
      $stmt->bind_param('si',$nombre,$id);
      $stmt->execute(); $stmt->close();
      echo json_encode(['ok'=>true,'msg'=>'Tipo actualizado']);
    } else {
      $stmt = $conn->prepare("INSERT INTO tipos_inscrito (nombre) VALUES(?)");
      $stmt->bind_param('s',$nombre);
      $stmt->execute();
      echo json_encode(['ok'=>true,'msg'=>'Tipo creado','id'=>$conn->insert_id]);
      $stmt->close();
    }
    break;

  case 'eliminar_tipo':
      $id = intval($_POST['id'] ?? 0);
      /* verificar si tiene inscritos asignados */
      $r = $conn->query("SELECT COUNT(*) AS c FROM inscritos WHERE tipo_inscrito_id=$id")->fetch_assoc();
      if($r && $r['c'] > 0){
          echo json_encode(['ok'=>false,'msg'=>'No se puede eliminar: tiene '.$r['c'].' inscrito(s) asignados']);
      } else {
          $conn->query("DELETE FROM tipos_inscrito WHERE id=$id");
          echo json_encode(['ok'=>true]);
      }
      break;

  /* ══════════════════════════════
     IGLESIAS
  ══════════════════════════════ */
  case 'listar_iglesias':
    $res = $conn->query("
      SELECT i.id, i.nombre AS iglesia, d.id AS distrito_id, d.nombre AS distrito
      FROM iglesias i
      LEFT JOIN distritos d ON d.id = i.distrito_id
      ORDER BY d.nombre, i.nombre
    ");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'iglesias'=>$rows]);
    break;

  case 'listar_distritos':
    $res = $conn->query("SELECT id, nombre FROM distritos ORDER BY nombre");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'distritos'=>$rows]);
    break;

  case 'guardar_iglesia':
    $id          = intval($_POST['id'] ?? 0);
    $nombre      = trim($_POST['nombre'] ?? '');
    $distrito_id = intval($_POST['distrito_id'] ?? 0);
    if(!$nombre){ echo json_encode(['ok'=>false,'msg'=>'Nombre requerido']); exit; }
    $dis = $distrito_id > 0 ? $distrito_id : 'NULL';
    if($id > 0){
      $stmt = $conn->prepare("UPDATE iglesias SET nombre=?,distrito_id=? WHERE id=?");
      $stmt->bind_param('sii',$nombre,$distrito_id,$id);
      $stmt->execute(); $stmt->close();
      echo json_encode(['ok'=>true,'msg'=>'Iglesia actualizada']);
    } else {
      $stmt = $conn->prepare("INSERT INTO iglesias (nombre,distrito_id) VALUES(?,?)");
      $stmt->bind_param('si',$nombre,$distrito_id);
      $stmt->execute();
      echo json_encode(['ok'=>true,'msg'=>'Iglesia creada','id'=>$conn->insert_id]);
      $stmt->close();
    }
    break;

  case 'eliminar_iglesia':
    $id = intval($_POST['id'] ?? 0);
    /* verificar si tiene inscritos */
    $r = $conn->query("SELECT COUNT(*) AS c FROM inscritos WHERE iglesia_id=$id")->fetch_assoc();
    if($r['c'] > 0){
      echo json_encode(['ok'=>false,'msg'=>'No se puede eliminar: tiene inscritos asignados']);
    } else {
      $conn->query("DELETE FROM iglesias WHERE id=$id");
      echo json_encode(['ok'=>true]);
    }
    break;

  /* ══════════════════════════════
     EMERGENCIA — CONTROL INSCRIPCIONES
  ══════════════════════════════ */
  case 'estado_inscripciones':
    /* usar tabla config si existe, sino variable de sesión como fallback */
    $res = $conn->query("SHOW TABLES LIKE 'config_sistema'");
    if($res->num_rows === 0){
      /* crear tabla si no existe */
      $conn->query("CREATE TABLE config_sistema (
        clave VARCHAR(50) PRIMARY KEY,
        valor VARCHAR(255) NOT NULL
      )");
      $conn->query("INSERT INTO config_sistema (clave,valor) VALUES('inscripciones_activas','1')");
    }
    $r = $conn->query("SELECT valor FROM config_sistema WHERE clave='inscripciones_activas' LIMIT 1")->fetch_assoc();
    $activo = $r ? intval($r['valor']) : 1;
    echo json_encode(['ok'=>true,'activo'=>$activo]);
    break;

  case 'toggle_inscripciones':
    $nuevo = intval($_POST['activo'] ?? 1);
    $conn->query("
      INSERT INTO config_sistema (clave,valor) VALUES('inscripciones_activas','$nuevo')
      ON DUPLICATE KEY UPDATE valor='$nuevo'
    ");
    $msg = $nuevo ? 'Inscripciones activadas' : 'Inscripciones pausadas';
    echo json_encode(['ok'=>true,'activo'=>$nuevo,'msg'=>$msg]);
    break;
  
  case 'subir_qr':
      if(!isset($_FILES['qr']) || $_FILES['qr']['error'] !== UPLOAD_ERR_OK){
          echo json_encode(['ok'=>false,'msg'=>'Error al recibir el archivo']); exit;
      }
      $ext = strtolower(pathinfo($_FILES['qr']['name'], PATHINFO_EXTENSION));
      if(!in_array($ext, ['jpg','jpeg','png','webp'])){
          echo json_encode(['ok'=>false,'msg'=>'Solo JPG, PNG o WEBP']); exit;
      }
      $nombre = 'qr_pago_' . time() . '.' . $ext;
      $destino = '../img/' . $nombre;
      if(!move_uploaded_file($_FILES['qr']['tmp_name'], $destino)){
          echo json_encode(['ok'=>false,'msg'=>'No se pudo guardar']); exit;
      }
      $conn->query("
          INSERT INTO config_sistema (clave,valor) VALUES('qr_imagen','$nombre')
          ON DUPLICATE KEY UPDATE valor='$nombre'
      ");
      echo json_encode(['ok'=>true,'msg'=>'QR actualizado','archivo'=>$nombre]);
      break;

  default:
    echo json_encode(['ok'=>false,'msg'=>'Accion no valida']);
}