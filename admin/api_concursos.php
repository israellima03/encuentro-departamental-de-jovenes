<?php
require_once('funciones/sesiones.php');
usuario_autentificado();
header('Content-Type: application/json');
require_once('../includes/funciones/bd_conexion.php');
include_once 'funciones/funciones.php';

$accion     = trim($_GET['accion'] ?? $_POST['accion'] ?? '');
$usuario_id = intval($_SESSION['usuario_id'] ?? 0);
$puede_gestionar = puede(['Administrador','Lider departamental']);
$puede_inscribir = puede(['Administrador','Lider departamental','Lider distrital','tesorera']);

switch($accion){

  /* ── DEPORTIVO: listar equipos ── */
  case 'listar_equipos':
    $q = '%' . trim($_GET['q'] ?? '') . '%';
    $sql = "
      SELECT ed.id, ed.nombre_equipo, ed.fecha_registro,
             i.nombre, i.apellido, i.carnet, i.celular,
             ig.nombre AS iglesia, d.nombre AS distrito
      FROM equipos_deportivos ed
      INNER JOIN inscritos i ON i.id = ed.inscrito_id
      LEFT JOIN iglesias ig ON ig.id = i.iglesia_id
      LEFT JOIN distritos d ON d.id = i.distrito_id
      WHERE ed.nombre_equipo LIKE ? OR i.nombre LIKE ? OR i.apellido LIKE ?
      ORDER BY ed.fecha_registro DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $q, $q, $q);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(['ok'=>true,'equipos'=>$rows]);
    break;

  /* ── DEPORTIVO: editar nombre equipo ── */
  case 'editar_equipo':
    if(!$puede_gestionar){ echo json_encode(['ok'=>false,'msg'=>'Sin permisos']); exit; }
    $id     = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    if(!$id || strlen($nombre) < 3){ echo json_encode(['ok'=>false,'msg'=>'Nombre inválido']); exit; }
    /* verificar duplicado */
    $stmt = $conn->prepare("SELECT id FROM equipos_deportivos WHERE LOWER(nombre_equipo)=LOWER(?) AND id!=? LIMIT 1");
    $stmt->bind_param('si', $nombre, $id);
    $stmt->execute(); $stmt->store_result();
    if($stmt->num_rows > 0){ echo json_encode(['ok'=>false,'msg'=>'Ese nombre ya existe']); $stmt->close(); exit; }
    $stmt->close();
    $stmt = $conn->prepare("UPDATE equipos_deportivos SET nombre_equipo=? WHERE id=?");
    $stmt->bind_param('si', $nombre, $id);
    $stmt->execute(); $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>'Equipo actualizado']);
    break;

  /* ── DEPORTIVO: eliminar equipo ── */
  case 'eliminar_equipo':
    if(!$puede_gestionar){ echo json_encode(['ok'=>false,'msg'=>'Sin permisos']); exit; }
    $id = intval($_POST['id'] ?? 0);
    $conn->query("DELETE FROM equipos_deportivos WHERE id=$id");
    echo json_encode(['ok'=>true,'msg'=>'Equipo eliminado']);
    break;

  /* ── BÍBLICO: listar categorías ── */
  case 'listar_categorias':
    $rows = $conn->query("SELECT * FROM concurso_categorias WHERE activo=1 ORDER BY orden")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['ok'=>true,'categorias'=>$rows]);
    break;

  /* ── BÍBLICO: listar distritos ── */
  case 'listar_distritos':
    $rows = $conn->query("SELECT id, nombre FROM distritos ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['ok'=>true,'distritos'=>$rows]);
    break;

  /* ── BÍBLICO: buscar inscrito confirmado ── */
  case 'buscar_inscrito_bib':
    $q = '%' . trim($_GET['q'] ?? '') . '%';
    $stmt = $conn->prepare("
      SELECT i.id, i.nombre, i.apellido, i.carnet, i.celular,
             ig.nombre AS iglesia, d.nombre AS distrito, d.id AS distrito_id
      FROM inscritos i
      INNER JOIN inscripciones ins ON ins.inscrito_id = i.id AND ins.estado_pago = 'confirmado'
      LEFT JOIN iglesias ig ON ig.id = i.iglesia_id
      LEFT JOIN distritos d ON d.id = i.distrito_id
      WHERE i.nombre LIKE ? OR i.apellido LIKE ? OR i.carnet LIKE ?
      LIMIT 6
    ");
    $stmt->bind_param('sss', $q, $q, $q);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(['ok'=>true,'inscritos'=>$rows]);
    break;

  /* ── BÍBLICO: listar concursantes ── */
  case 'listar_concursantes':
    $q    = '%' . trim($_GET['q'] ?? '') . '%';
    $cat  = intval($_GET['categoria_id'] ?? 0);
    $dist = intval($_GET['distrito_id']  ?? 0);

    $where = '1=1';
    $params = []; $tipos = '';

    if(trim($_GET['q'] ?? '') !== ''){
      $where .= " AND (i.nombre LIKE ? OR i.apellido LIKE ? OR i.carnet LIKE ?)";
      $params[] = $q; $params[] = $q; $params[] = $q; $tipos .= 'sss';
    }
    if($cat){ $where .= " AND ci.categoria_id = ?"; $params[] = $cat; $tipos .= 'i'; }
    if($dist){ $where .= " AND d.id = ?"; $params[] = $dist; $tipos .= 'i'; }

    $sql = "
      SELECT ci.id, ci.equipo_nombre, ci.fecha_registro,
             i.nombre, i.apellido, i.carnet, i.celular,
             ig.nombre AS iglesia, d.nombre AS distrito, d.id AS distrito_id,
             cc.nombre AS categoria, cc.tipo AS cat_tipo, cc.max_por_distrito,
             u.nombre AS registrado_por
      FROM concurso_inscritos ci
      INNER JOIN inscritos i ON i.id = ci.inscrito_id
      LEFT JOIN iglesias ig ON ig.id = i.iglesia_id
      LEFT JOIN distritos d ON d.id = i.distrito_id
      LEFT JOIN concurso_categorias cc ON cc.id = ci.categoria_id
      LEFT JOIN usuarios u ON u.id = ci.registrado_por
      WHERE $where
      ORDER BY ci.fecha_registro DESC
    ";
    $stmt = $conn->prepare($sql);
    if(!empty($params)) $stmt->bind_param($tipos, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(['ok'=>true,'concursantes'=>$rows]);
    break;

  /* ── BÍBLICO: inscribir concursante ── */
  case 'inscribir_concursante':
    if(!$puede_inscribir){ echo json_encode(['ok'=>false,'msg'=>'Sin permisos']); exit; }
    $inscrito_id  = intval($_POST['inscrito_id'] ?? 0);
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $equipo       = trim($_POST['equipo_nombre'] ?? '');

    if(!$inscrito_id || !$categoria_id){ echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']); exit; }

    /* verificar que esté inscrito y confirmado */
    $stmt = $conn->prepare("SELECT id FROM inscripciones WHERE inscrito_id=? AND estado_pago='confirmado' LIMIT 1");
    $stmt->bind_param('i', $inscrito_id); $stmt->execute(); $stmt->store_result();
    if($stmt->num_rows === 0){ echo json_encode(['ok'=>false,'msg'=>'El inscrito no tiene pago confirmado']); $stmt->close(); exit; }
    $stmt->close();

    /* verificar que no esté ya en esta categoría */
    $stmt = $conn->prepare("SELECT id FROM concurso_inscritos WHERE inscrito_id=? AND categoria_id=? LIMIT 1");
    $stmt->bind_param('ii', $inscrito_id, $categoria_id); $stmt->execute(); $stmt->store_result();
    if($stmt->num_rows > 0){ echo json_encode(['ok'=>false,'msg'=>'Ya está inscrito en esta categoría']); $stmt->close(); exit; }
    $stmt->close();

    /* verificar máximo por distrito */
    $cat = $conn->query("SELECT max_por_distrito, tipo FROM concurso_categorias WHERE id=$categoria_id LIMIT 1")->fetch_assoc();
    $dist_id = $conn->query("SELECT distrito_id FROM inscritos WHERE id=$inscrito_id LIMIT 1")->fetch_assoc()['distrito_id'] ?? 0;
    if($cat && $cat['max_por_distrito'] && $dist_id){
      $count = $conn->query("
        SELECT COUNT(*) AS c FROM concurso_inscritos ci
        INNER JOIN inscritos i ON i.id = ci.inscrito_id
        WHERE ci.categoria_id = $categoria_id AND i.distrito_id = $dist_id
      ")->fetch_assoc()['c'];
      if($count >= $cat['max_por_distrito']){
        echo json_encode(['ok'=>false,'msg'=>'El distrito ya alcanzó el máximo de participantes en esta categoría ('.$cat['max_por_distrito'].')']);
        exit;
      }
    }

    $stmt = $conn->prepare("INSERT INTO concurso_inscritos (inscrito_id, categoria_id, equipo_nombre, registrado_por) VALUES (?,?,?,?)");
    $stmt->bind_param('iisi', $inscrito_id, $categoria_id, $equipo, $usuario_id);
    if($stmt->execute()){
      echo json_encode(['ok'=>true,'msg'=>'Concursante inscrito correctamente']);
    } else {
      echo json_encode(['ok'=>false,'msg'=>'Error al inscribir']);
    }
    $stmt->close();
    break;

  /* ── BÍBLICO: editar concursante ── */
  case 'editar_concursante':
    if(!$puede_inscribir){ echo json_encode(['ok'=>false,'msg'=>'Sin permisos']); exit; }
    $id           = intval($_POST['id'] ?? 0);
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $equipo       = trim($_POST['equipo_nombre'] ?? '');
    if(!$id || !$categoria_id){ echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']); exit; }
    $stmt = $conn->prepare("UPDATE concurso_inscritos SET categoria_id=?, equipo_nombre=? WHERE id=?");
    $stmt->bind_param('isi', $categoria_id, $equipo, $id);
    $stmt->execute(); $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>'Concursante actualizado']);
    break;

  /* ── BÍBLICO: eliminar concursante ── */
  case 'eliminar_concursante':
    if(!$puede_inscribir){ echo json_encode(['ok'=>false,'msg'=>'Sin permisos']); exit; }
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    /* eliminar puntuaciones primero */
    $conn->query("DELETE FROM concurso_puntuaciones WHERE concursante_id=$id");
    $res = $conn->query("DELETE FROM concurso_inscritos WHERE id=$id");
    if($res){
      echo json_encode(['ok'=>true,'msg'=>'Concursante eliminado']);
    } else {
      echo json_encode(['ok'=>false,'msg'=>'Error al eliminar: '.$conn->error]);
    }
    break;

  /* ── FASES: listar por categoría ── */
  case 'listar_fases':
    $cat = intval($_GET['categoria_id'] ?? 0);
    if(!$cat){ echo json_encode(['ok'=>false,'msg'=>'Categoría requerida']); exit; }
    $rows = $conn->query("SELECT * FROM concurso_fases WHERE categoria_id=$cat ORDER BY orden")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['ok'=>true,'fases'=>$rows]);
    break;

  /* ── FASES: crear fase ── */
  case 'crear_fase':
    if(!$puede_gestionar){ echo json_encode(['ok'=>false,'msg'=>'Sin permisos']); exit; }
    $cat    = intval($_POST['categoria_id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    if(!$cat || !$nombre){ echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']); exit; }
    $orden = $conn->query("SELECT COALESCE(MAX(orden),0)+1 AS o FROM concurso_fases WHERE categoria_id=$cat")->fetch_assoc()['o'];
    $stmt = $conn->prepare("INSERT INTO concurso_fases (nombre, categoria_id, orden) VALUES (?,?,?)");
    $stmt->bind_param('sii', $nombre, $cat, $orden);
    $stmt->execute(); $stmt->close();
    echo json_encode(['ok'=>true,'msg'=>'Fase creada']);
    break;

  /* ── PUNTUACIONES: listar por fase ── */
  case 'listar_puntuaciones':
    $fase = intval($_GET['fase_id'] ?? 0);
    if(!$fase){ echo json_encode(['ok'=>false,'msg'=>'Fase requerida']); exit; }

    /* traer todos los concursantes de la categoría de esa fase */
    $faseData = $conn->query("SELECT categoria_id FROM concurso_fases WHERE id=$fase LIMIT 1")->fetch_assoc();
    if(!$faseData){ echo json_encode(['ok'=>false,'msg'=>'Fase no encontrada']); exit; }
    $cat = $faseData['categoria_id'];

    $res = $conn->query("
      SELECT ci.id AS concursante_id, ci.equipo_nombre,
             i.nombre, i.apellido, i.carnet,
             ig.nombre AS iglesia, d.nombre AS distrito,
             COALESCE(cp.puntuacion, 0) AS puntuacion,
             cp.id AS punt_id
      FROM concurso_inscritos ci
      INNER JOIN inscritos i ON i.id = ci.inscrito_id
      LEFT JOIN iglesias ig ON ig.id = i.iglesia_id
      LEFT JOIN distritos d ON d.id = i.distrito_id
      LEFT JOIN concurso_puntuaciones cp ON cp.concursante_id = ci.id AND cp.fase_id = $fase
      WHERE ci.categoria_id = $cat
      ORDER BY puntuacion DESC, i.apellido ASC
    ");
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['ok'=>true,'puntuaciones'=>$rows]);
    break;

  /* ── PUNTUACIONES: guardar ── */
  case 'guardar_puntuacion':
    if(!$puede_gestionar && !puede(['Equipo departamental'])){ echo json_encode(['ok'=>false,'msg'=>'Sin permisos']); exit; }
    $fase_id        = intval($_POST['fase_id'] ?? 0);
    $concursante_id = intval($_POST['concursante_id'] ?? 0);
    $puntuacion     = floatval($_POST['puntuacion'] ?? 0);
    if(!$fase_id || !$concursante_id){ echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']); exit; }

    /* upsert */
    $existing = $conn->query("SELECT id FROM concurso_puntuaciones WHERE fase_id=$fase_id AND concursante_id=$concursante_id LIMIT 1")->fetch_assoc();
    if($existing){
      $conn->query("UPDATE concurso_puntuaciones SET puntuacion=$puntuacion, registrado_por=$usuario_id WHERE id={$existing['id']}");
    } else {
      $stmt = $conn->prepare("INSERT INTO concurso_puntuaciones (fase_id, concursante_id, puntuacion, registrado_por) VALUES (?,?,?,?)");
      $stmt->bind_param('iidi', $fase_id, $concursante_id, $puntuacion, $usuario_id);
      $stmt->execute(); $stmt->close();
    }
    echo json_encode(['ok'=>true,'msg'=>'Puntuación guardada']);
    break;

  /* ── CATEGORÍAS: listar todas ── */
  case 'listar_categorias_admin':
    if(!$puede_gestionar){ echo json_encode(['ok'=>false,'msg'=>'Sin permisos']); exit; }
    $rows = $conn->query("SELECT * FROM concurso_categorias ORDER BY orden, nombre")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['ok'=>true,'categorias'=>$rows]);
    break;

  /* ── CATEGORÍAS: guardar (crear o editar) ── */
  case 'guardar_categoria':
    if(!$puede_gestionar){ echo json_encode(['ok'=>false,'msg'=>'Sin permisos']); exit; }
    $id          = intval($_POST['id'] ?? 0);
    $nombre      = trim($_POST['nombre'] ?? '');
    $tipo        = trim($_POST['tipo'] ?? 'individual');
    $max_dist    = intval($_POST['max_por_distrito'] ?? 1);
    $max_equipo  = $_POST['max_por_equipo'] !== '' ? intval($_POST['max_por_equipo']) : null;
    $orden       = intval($_POST['orden'] ?? 0);
    $activo      = intval($_POST['activo'] ?? 1);

    if(!$nombre){ echo json_encode(['ok'=>false,'msg'=>'El nombre es obligatorio']); exit; }
    if(!in_array($tipo,['individual','grupal'])){ $tipo='individual'; }

    if($id){
      $stmt = $conn->prepare("UPDATE concurso_categorias SET nombre=?,tipo=?,max_por_distrito=?,max_por_equipo=?,orden=?,activo=? WHERE id=?");
      $stmt->bind_param('ssiiiii',$nombre,$tipo,$max_dist,$max_equipo,$orden,$activo,$id);
    } else {
      $stmt = $conn->prepare("INSERT INTO concurso_categorias (nombre,tipo,max_por_distrito,max_por_equipo,orden,activo) VALUES (?,?,?,?,?,?)");
      $stmt->bind_param('ssiiii',$nombre,$tipo,$max_dist,$max_equipo,$orden,$activo);
    }
    if($stmt->execute()){
      echo json_encode(['ok'=>true,'msg'=>$id?'Categoría actualizada':'Categoría creada']);
    } else {
      echo json_encode(['ok'=>false,'msg'=>'Error al guardar']);
    }
    $stmt->close();
    break;

  /* ── CATEGORÍAS: eliminar ── */
  case 'eliminar_categoria':
    if(!$puede_gestionar){ echo json_encode(['ok'=>false,'msg'=>'Sin permisos']); exit; }
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    /* verificar que no tenga concursantes */
    $res_c = $conn->query("SELECT COUNT(*) AS c FROM concurso_inscritos WHERE categoria_id=$id");
    if(!$res_c){
      echo json_encode(['ok'=>false,'msg'=>'Error al verificar concursantes: '.$conn->error]);
      exit;
    }
    $c = $res_c->fetch_assoc()['c'];
    if($c > 0){
      echo json_encode(['ok'=>false,'msg'=>'No puedes eliminar una categoría con concursantes inscritos ('.$c.')']);
      exit;
    }
    $conn->query("DELETE FROM concurso_fases WHERE categoria_id=$id");
    $conn->query("DELETE FROM concurso_categorias WHERE id=$id");
    echo json_encode(['ok'=>true,'msg'=>'Categoría eliminada']);
    break;
    
  default:
    echo json_encode(['ok'=>false,'msg'=>'Acción no válida']);
}