<?php
ini_set('display_errors', 0);
ob_start();
require_once('funciones/sesiones.php');
usuario_autentificado();
ob_clean();
header('Content-Type: application/json');
require_once('../includes/funciones/bd_conexion.php');

$accion = trim($_GET['accion'] ?? $_POST['accion'] ?? '');

/* ── helper subida de archivo ── */
function subirArchivo(string $campo, string $carpeta, array $permitidos, int $max_mb = 5){
  if(!isset($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) return null;
  $arch = $_FILES[$campo];
  $ext  = strtolower(pathinfo($arch['name'], PATHINFO_EXTENSION));
  if(!in_array($ext, $permitidos)) return ['error'=>'Tipo de archivo no permitido ('.$ext.')'];
  if($arch['size'] > $max_mb * 1024 * 1024) return ['error'=>'El archivo supera '.$max_mb.'MB'];
  $base = rtrim($carpeta,'/').'/';
  if(!is_dir($base)) mkdir($base, 0755, true);
  $nombre = preg_replace('/[^a-z0-9_]/', '_', strtolower(pathinfo($arch['name'], PATHINFO_FILENAME)));
  $nombre = $nombre.'_'.time().'.'.$ext;
  if(!move_uploaded_file($arch['tmp_name'], $base.$nombre)) return ['error'=>'No se pudo guardar el archivo'];
  return $nombre;
}

switch($accion){

  /* ══════════════════════════════════════
     EXPOSITORES
  ══════════════════════════════════════ */
  case 'listar_expositores':
    $res = $conn->query("SELECT * FROM expositores ORDER BY apellido, nombre");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'expositores'=>$rows]);
    break;

  case 'guardar_expositor':
    $id        = intval($_POST['id']        ?? 0);
    $nombre    = trim($_POST['nombre']      ?? '');
    $apellido  = trim($_POST['apellido']    ?? '');
    $rango     = trim($_POST['rango']       ?? '');
    $desc      = trim($_POST['descripcion'] ?? '');
    if(!$nombre || !$apellido || !$rango){
      echo json_encode(['ok'=>false,'msg'=>'Faltan datos obligatorios']); exit;
    }

    /* imagen — puede venir como archivo subido O como nombre existente */
    $imagen_actual = trim($_POST['imagen_actual'] ?? '');
    $imagen = $imagen_actual; /* por defecto mantener la actual */

    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK){
      $res_img = subirArchivo('imagen', '../img', ['jpg','jpeg','png','webp','gif']);
      if(is_array($res_img) && isset($res_img['error'])){
        echo json_encode(['ok'=>false,'msg'=>$res_img['error']]); exit;
      }
      $imagen = basename($res_img);
    }

    if($id > 0){
      $stmt = $conn->prepare("UPDATE expositores SET nombre=?,apellido=?,rango=?,descripcion=?,imagen=? WHERE id_expositor=?");
      $stmt->bind_param('sssssi',$nombre,$apellido,$rango,$desc,$imagen,$id);
    } else {
      $stmt = $conn->prepare("INSERT INTO expositores (nombre,apellido,rango,descripcion,imagen) VALUES(?,?,?,?,?)");
      $stmt->bind_param('sssss',$nombre,$apellido,$rango,$desc,$imagen);
    }
    $stmt->execute(); $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>$id?'Expositor actualizado':'Expositor creado','imagen'=>$imagen]);
    break;

  case 'eliminar_expositor':
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    $conn->query("DELETE FROM expositores WHERE id_expositor=$id");
    echo json_encode(['ok'=>true,'msg'=>'Expositor eliminado']);
    break;

  /* ══════════════════════════════════════
     TEMAS
  ══════════════════════════════════════ */
  case 'listar_temas':
    $res = $conn->query("SELECT * FROM temas ORDER BY titulo");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'temas'=>$rows]);
    break;

  case 'guardar_tema':
    $id     = intval($_POST['id']    ?? 0);
    $titulo = trim($_POST['titulo']  ?? '');
    if(!$titulo){ echo json_encode(['ok'=>false,'msg'=>'El título es obligatorio']); exit; }
    if($id > 0){
      $stmt = $conn->prepare("UPDATE temas SET titulo=? WHERE id_tema=?");
      $stmt->bind_param('si',$titulo,$id);
    } else {
      $stmt = $conn->prepare("INSERT INTO temas (titulo) VALUES(?)");
      $stmt->bind_param('s',$titulo);
    }
    $stmt->execute(); $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>$id?'Tema actualizado':'Tema creado']);
    break;

  case 'eliminar_tema':
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    $conn->query("DELETE FROM temas WHERE id_tema=$id");
    echo json_encode(['ok'=>true,'msg'=>'Tema eliminado']);
    break;

  /* ══════════════════════════════════════
     DÍAS Y GRUPOS
  ══════════════════════════════════════ */
  case 'listar_dias':
    $res = $conn->query("SELECT * FROM dias ORDER BY id_dia");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'dias'=>$rows]);
    break;

  case 'listar_grupos':
    $res = $conn->query("SELECT * FROM grupos_alabanza ORDER BY nombre_grupo");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'grupos'=>$rows]);
    break;
  case 'guardar_grupo':
    $id     = intval($_POST['id']     ?? 0);
    $nombre = trim($_POST['nombre']   ?? '');
    if(!$nombre){ echo json_encode(['ok'=>false,'msg'=>'El nombre es obligatorio']); exit; }
    if($id > 0){
      $stmt = $conn->prepare("UPDATE grupos_alabanza SET nombre_grupo=? WHERE id_grupo=?");
      $stmt->bind_param('si',$nombre,$id);
    } else {
      $stmt = $conn->prepare("INSERT INTO grupos_alabanza (nombre_grupo) VALUES(?)");
      $stmt->bind_param('s',$nombre);
    }
    $stmt->execute(); $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>$id?'Grupo actualizado':'Grupo creado']);
    break;

  case 'eliminar_grupo':
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    $conn->query("DELETE FROM grupos_alabanza WHERE id_grupo=$id");
    echo json_encode(['ok'=>true,'msg'=>'Grupo eliminado']);
    break;

  /* ══════════════════════════════════════
     EVENTOS
  ══════════════════════════════════════ */
  case 'listar_eventos':
    $filtro_dia  = intval($_GET['dia']  ?? 0);
    $filtro_tipo = trim($_GET['tipo']   ?? '');
    $where = '1=1';
    if($filtro_dia)  $where .= " AND e.id_dia=$filtro_dia";
    if($filtro_tipo) $where .= " AND e.tipo_evento='".addslashes($filtro_tipo)."'";
    $res = $conn->query("
      SELECT e.*, d.nombre AS dia_nombre,
             CONCAT(ex.nombre,' ',ex.apellido) AS expositor_nombre,
             t.titulo AS tema_titulo,
             CONCAT(m.nombre,' ',m.apellido) AS moderador_nombre,
             g.nombre_grupo AS grupo_nombre
      FROM eventos e
      LEFT JOIN dias d             ON d.id_dia        = e.id_dia
      LEFT JOIN expositores ex     ON ex.id_expositor = e.id_expositor
      LEFT JOIN temas t            ON t.id_tema        = e.id_tema
      LEFT JOIN moderadores m      ON m.id_moderador   = e.id_moderador
      LEFT JOIN grupos_alabanza g  ON g.id_grupo       = e.id_grupo
      WHERE $where
      ORDER BY e.id_dia, e.hora_inicio
    ");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'eventos'=>$rows]);
    break;

  case 'guardar_evento':
      $id           = intval($_POST['id']               ?? 0);
      $id_dia       = intval($_POST['id_dia']           ?? 0);
      $fecha        = trim($_POST['fecha']              ?? '');
      $tipo         = trim($_POST['tipo_evento']        ?? '');
      $hora_inicio  = trim($_POST['hora_inicio']        ?? '');
      $hora_fin     = trim($_POST['hora_fin']           ?? '');
      $id_expositor = intval($_POST['id_expositor']     ?? 0) ?: null;
      $id_tema      = intval($_POST['id_tema']          ?? 0) ?: null;
      $id_moderador = intval($_POST['id_moderador']     ?? 0) ?: null;
      $id_grupo     = intval($_POST['id_grupo']         ?? 0) ?: null;
      $preguntas    = intval($_POST['preguntas_activas'] ?? 0);

      if(!$id_dia || !$fecha || !$tipo || !$hora_inicio || !$hora_fin){
        echo json_encode(['ok'=>false,'msg'=>'Faltan datos obligatorios']); exit;
      }

      if($id > 0){
        $stmt = $conn->prepare("UPDATE eventos SET id_dia=?,fecha=?,tipo_evento=?,hora_inicio=?,hora_fin=?,id_expositor=?,id_tema=?,id_moderador=?,id_grupo=?,preguntas_activas=? WHERE id_evento=?");
        $stmt->bind_param('issssiiiiii',$id_dia,$fecha,$tipo,$hora_inicio,$hora_fin,$id_expositor,$id_tema,$id_moderador,$id_grupo,$preguntas,$id);
      } else {
        $stmt = $conn->prepare("INSERT INTO eventos (id_dia,fecha,tipo_evento,hora_inicio,hora_fin,id_expositor,id_tema,id_moderador,id_grupo,preguntas_activas) VALUES(?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('issssiiiii',$id_dia,$fecha,$tipo,$hora_inicio,$hora_fin,$id_expositor,$id_tema,$id_moderador,$id_grupo,$preguntas);
      }
      if(!$stmt->execute()){
        echo json_encode(['ok'=>false,'msg'=>'Error BD: '.$stmt->error]); exit;
      }
      $stmt->close();
      echo json_encode(['ok'=>true,'msg'=>$id?'Evento actualizado':'Evento creado']);
      break;

  case 'eliminar_evento':
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    $conn->query("DELETE FROM eventos WHERE id_evento=$id");
    echo json_encode(['ok'=>true,'msg'=>'Evento eliminado']);
    break;

  /* ══════════════════════════════════════
     MATERIAL — con subida real de archivos
  ══════════════════════════════════════ */
  case 'listar_material':
    $res = $conn->query("
      SELECT m.*, e.tipo_evento, e.hora_inicio, d.nombre AS dia_nombre
      FROM materiales_evento m
      LEFT JOIN eventos e ON e.id_evento = m.id_evento
      LEFT JOIN dias d    ON d.id_dia    = e.id_dia
      ORDER BY m.id
    ");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'materiales'=>$rows]);
    break;

  case 'guardar_material':
    $id        = intval($_POST['id']          ?? 0);
    $nombre    = trim($_POST['nombre']        ?? '');
    $id_evento = intval($_POST['id_evento']   ?? 0);
    $tipo      = trim($_POST['tipo']          ?? 'pdf');
    $desc      = trim($_POST['descripcion']   ?? '');
    $descarga  = intval($_POST['descarga_activa'] ?? 1);
    $url_actual = trim($_POST['url_actual']   ?? '');

    if(!$nombre || !$id_evento){
      echo json_encode(['ok'=>false,'msg'=>'Faltan datos obligatorios']); exit;
    }

    /* subir archivo si viene, si no mantener URL existente o texto ingresado */
    $url = trim($_POST['url'] ?? $url_actual);

    if(isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK){
      $permitidos = ['pdf','ppt','pptx','doc','docx','xls','xlsx','zip','rar','jpg','jpeg','png','webp','gif','mp4','mov'];
      $res_arch = subirArchivo('archivo', '../material', $permitidos, 50);
      if(is_array($res_arch) && isset($res_arch['error'])){
        echo json_encode(['ok'=>false,'msg'=>$res_arch['error']]); exit;
      }
      $url = $res_arch;
    }

    if(!$url){ echo json_encode(['ok'=>false,'msg'=>'Debes subir un archivo o ingresar una URL']); exit; }

    if($id > 0){
      $stmt = $conn->prepare("UPDATE materiales_evento SET nombre=?,id_evento=?,tipo=?,url=?,descripcion=?,descarga_activa=? WHERE id=?");
      $stmt->bind_param('sissisi',$nombre,$id_evento,$tipo,$url,$desc,$descarga,$id);
    } else {
      $stmt = $conn->prepare("INSERT INTO materiales_evento (nombre,id_evento,tipo,url,descripcion,descarga_activa) VALUES(?,?,?,?,?,?)");
      $stmt->bind_param('sisssi',$nombre,$id_evento,$tipo,$url,$desc,$descarga);
    }
    if(!$stmt->execute()){
      echo json_encode(['ok'=>false,'msg'=>'Error BD: '.$stmt->error]); exit;
    }
    $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>$id?'Material actualizado':'Material subido','url'=>$url]);
    break;

  case 'eliminar_material':
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    $conn->query("DELETE FROM materiales_evento WHERE id=$id");
    echo json_encode(['ok'=>true,'msg'=>'Material eliminado']);
    break;

  case 'toggle_descarga':
    $id    = intval($_POST['id']    ?? 0);
    $valor = intval($_POST['valor'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    $conn->query("UPDATE materiales_evento SET descarga_activa=$valor WHERE id=$id");
    echo json_encode(['ok'=>true,'msg'=>$valor?'Descarga habilitada':'Descarga bloqueada']);
    break;

  /* ══════════════════════════════════════
     MODERADORES
  ══════════════════════════════════════ */
  case 'listar_moderadores':
    $res = $conn->query("SELECT * FROM moderadores ORDER BY apellido, nombre");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'moderadores'=>$rows]);
    break;

  case 'guardar_moderador':
    $id       = intval($_POST['id']       ?? 0);
    $nombre   = trim($_POST['nombre']     ?? '');
    $apellido = trim($_POST['apellido']   ?? '');
    if(!$nombre || !$apellido){ echo json_encode(['ok'=>false,'msg'=>'Nombre y apellido son obligatorios']); exit; }
    if($id > 0){
      $stmt = $conn->prepare("UPDATE moderadores SET nombre=?,apellido=? WHERE id_moderador=?");
      $stmt->bind_param('ssi',$nombre,$apellido,$id);
    } else {
      $stmt = $conn->prepare("INSERT INTO moderadores (nombre,apellido) VALUES(?,?)");
      $stmt->bind_param('ss',$nombre,$apellido);
    }
    $stmt->execute(); $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>$id?'Moderador actualizado':'Moderador creado']);
    break;

  case 'eliminar_moderador':
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    $conn->query("DELETE FROM moderadores WHERE id_moderador=$id");
    echo json_encode(['ok'=>true,'msg'=>'Moderador eliminado']);
    break;

  default:
    echo json_encode(['ok'=>false,'msg'=>'Accion no valida']);
}