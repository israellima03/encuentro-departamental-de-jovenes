<?php
ini_set('display_errors', 0);
ob_start();
require_once('funciones/sesiones.php');
usuario_autentificado();
verificar_acceso(['Administrador','Lider departamental']);
ob_clean();
header('Content-Type: application/json');
require_once('../includes/funciones/bd_conexion.php');

$accion = trim($_POST['accion'] ?? $_GET['accion'] ?? '');

switch($accion){

  /* ══ CONFIG SITIO ══ */
  case 'guardar_config':
    $pares = $_POST['pares'] ?? [];
    if(empty($pares)){ echo json_encode(['ok'=>false,'msg'=>'Sin datos']); exit; }
    $stmt = $conn->prepare("INSERT INTO config_sitio (clave,valor) VALUES(?,?) ON DUPLICATE KEY UPDATE valor=VALUES(valor)");
    foreach($pares as $clave => $valor){
      $clave = trim($clave); $valor = trim($valor);
      $stmt->bind_param('ss',$clave,$valor);
      $stmt->execute();
    }
    $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>'Configuración guardada correctamente']);
    break;

  /* ══ NOTICIAS ══ */
  case 'guardar_noticia':
    $id    = intval($_POST['id']    ?? 0);
    $texto = trim($_POST['texto']   ?? '');
    $orden = intval($_POST['orden'] ?? 0);
    if(!$texto){ echo json_encode(['ok'=>false,'msg'=>'El texto es obligatorio']); exit; }
    if($id > 0){
      $stmt = $conn->prepare("UPDATE noticias_footer SET texto=?,orden=? WHERE id=?");
      $stmt->bind_param('sii',$texto,$orden,$id);
    } else {
      $stmt = $conn->prepare("INSERT INTO noticias_footer (texto,orden) VALUES(?,?)");
      $stmt->bind_param('si',$texto,$orden);
    }
    $stmt->execute(); $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>$id?'Noticia actualizada':'Noticia creada']);
    break;

  case 'eliminar_noticia':
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    $conn->query("DELETE FROM noticias_footer WHERE id=$id");
    echo json_encode(['ok'=>true,'msg'=>'Noticia eliminada']);
    break;

  case 'listar_noticias':
    $res = $conn->query("SELECT * FROM noticias_footer ORDER BY orden ASC");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'noticias'=>$rows]);
    break;

  /* ══ REDES SOCIALES ══ */
  case 'guardar_red':
    $id     = intval($_POST['id']     ?? 0);
    $nombre = trim($_POST['nombre']   ?? '');
    $icono  = trim($_POST['icono']    ?? '');
    $url    = trim($_POST['url']      ?? '');
    $orden  = intval($_POST['orden']  ?? 0);
    $activo = intval($_POST['activo'] ?? 1);
    if(!$nombre || !$url){ echo json_encode(['ok'=>false,'msg'=>'Nombre y URL son obligatorios']); exit; }
    if($id > 0){
      $stmt = $conn->prepare("UPDATE redes_sociales SET nombre=?,icono=?,url=?,orden=?,activo=? WHERE id=?");
      $stmt->bind_param('sssiii',$nombre,$icono,$url,$orden,$activo,$id);
    } else {
      $stmt = $conn->prepare("INSERT INTO redes_sociales (nombre,icono,url,orden,activo) VALUES(?,?,?,?,?)");
      $stmt->bind_param('sssii',$nombre,$icono,$url,$orden,$activo);
    }
    $stmt->execute(); $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>$id?'Red actualizada':'Red creada']);
    break;

  case 'eliminar_red':
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    $conn->query("DELETE FROM redes_sociales WHERE id=$id");
    echo json_encode(['ok'=>true,'msg'=>'Red eliminada']);
    break;

  case 'listar_redes':
    $res = $conn->query("SELECT * FROM redes_sociales ORDER BY orden ASC");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'redes'=>$rows]);
    break;

  /* ══ UBICACIONES ══ */
  case 'guardar_ubicacion':
    $id     = intval($_POST['id']     ?? 0);
    $nombre = trim($_POST['nombre']   ?? '');
    $tipo   = trim($_POST['tipo']     ?? 'alojamiento');
    $link   = trim($_POST['link_maps']?? '');
    $embed  = trim($_POST['embed_url']?? '');
    $orden  = intval($_POST['orden']  ?? 0);
    $activo = intval($_POST['activo'] ?? 1);
    if(!$nombre || !$link){ echo json_encode(['ok'=>false,'msg'=>'Nombre y link son obligatorios']); exit; }
    if(!in_array($tipo,['evento','alojamiento','otro'])) $tipo='alojamiento';
    if($id > 0){
      $stmt = $conn->prepare("UPDATE ubicaciones SET nombre=?,tipo=?,link_maps=?,embed_url=?,orden=?,activo=? WHERE id=?");
      $stmt->bind_param('sssssii',$nombre,$tipo,$link,$embed,$orden,$activo,$id);
    } else {
      $stmt = $conn->prepare("INSERT INTO ubicaciones (nombre,tipo,link_maps,embed_url,orden,activo) VALUES(?,?,?,?,?,?)");
      $stmt->bind_param('ssssii',$nombre,$tipo,$link,$embed,$orden,$activo);
    }
    $stmt->execute(); $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>$id?'Ubicación actualizada':'Ubicación creada']);
    break;

  case 'eliminar_ubicacion':
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    $conn->query("DELETE FROM ubicaciones WHERE id=$id");
    echo json_encode(['ok'=>true,'msg'=>'Ubicación eliminada']);
    break;

  case 'listar_ubicaciones':
    $res = $conn->query("SELECT * FROM ubicaciones ORDER BY orden ASC");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'ubicaciones'=>$rows]);
    break;

  default:
    echo json_encode(['ok'=>false,'msg'=>'Acción no válida']);
}