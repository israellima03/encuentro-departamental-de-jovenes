<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
require_once('funciones/sesiones.php');
usuario_autentificado();
ob_clean();
header('Content-Type: application/json');
require_once('../includes/funciones/bd_conexion.php');
include_once 'funciones/funciones.php';

/* verificar acceso manualmente */
if(!puede(['Administrador','Lider departamental','tesorera'])){
    echo json_encode(['ok'=>false,'msg'=>'Sin permisos']);
    exit;
}

$accion     = trim($_GET['accion'] ?? $_POST['accion'] ?? '');
$usuario_id = intval($_SESSION['usuario_id'] ?? 0);

switch($accion){

  /* ── LISTAR COMISIONES ── */
  case 'listar':
    $q      = '%' . trim($_GET['q'] ?? '') . '%';
    $estado = trim($_GET['estado'] ?? '');

    $where = "WHERE (c.nombre LIKE ?)";
    $params = [$q]; $tipos = 's';

    if($estado !== ''){
      $where .= " AND c.activo = ?";
      $params[] = intval($estado); $tipos .= 'i';
    }

    $sql = "
      SELECT c.*,
             COUNT(ce.id) AS total_encargados
      FROM comisiones c
      LEFT JOIN comision_encargados ce ON ce.comision_id = c.id
      $where
      GROUP BY c.id
      ORDER BY c.orden ASC, c.nombre ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($tipos, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(['ok'=>true,'comisiones'=>$rows]);
    break;

  /* ── GUARDAR COMISIÓN (crear o editar) ── */
  case 'guardar':
    $id          = intval($_POST['id'] ?? 0);
    $nombre      = trim($_POST['nombre'] ?? '');
    $icono       = trim($_POST['icono'] ?? 'fa-users');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $orden       = intval($_POST['orden'] ?? 0);
    $activo      = intval($_POST['activo'] ?? 1);

    if(!$nombre){ echo json_encode(['ok'=>false,'msg'=>'El nombre es obligatorio']); exit; }

    if($id){
      $stmt = $conn->prepare("UPDATE comisiones SET nombre=?,icono=?,orden=?,activo=? WHERE id=?");
      $stmt->bind_param('ssiii', $nombre, $icono, $orden, $activo, $id);
    } else {
      $stmt = $conn->prepare("INSERT INTO comisiones (nombre,icono,orden,activo) VALUES(?,?,?,?)");
      $stmt->bind_param('ssii', $nombre, $icono, $orden, $activo);
    }
    if($stmt->execute()){
      echo json_encode(['ok'=>true,'msg'=>$id?'Comisión actualizada':'Comisión creada']);
    } else {
      echo json_encode(['ok'=>false,'msg'=>'Error al guardar']);
    }
    $stmt->close();
    break;

  /* ── ELIMINAR COMISIÓN ── */
  case 'eliminar':
    $id = intval($_POST['id'] ?? 0);
    $conn->query("DELETE FROM comision_encargados WHERE comision_id=$id");
    $conn->query("DELETE FROM comisiones WHERE id=$id");
    echo json_encode(['ok'=>true,'msg'=>'Comisión eliminada']);
    break;

  /* ── LISTAR ENCARGADOS ── */
  case 'listar_encargados':
    $comision_id = intval($_GET['comision_id'] ?? 0);
    if(!$comision_id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    $res = $conn->query("
      SELECT id, nombre, celular
      FROM comision_encargados
      WHERE comision_id = $comision_id
      ORDER BY id ASC
    ");
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['ok'=>true,'encargados'=>$rows]);
    break;

  /* ── AGREGAR ENCARGADO ── */
  case 'agregar_encargado':
    $comision_id = intval($_POST['comision_id'] ?? 0);
    $nombre      = trim($_POST['nombre'] ?? '');
    $celular     = trim($_POST['celular'] ?? '');

    if(!$comision_id){ echo json_encode(['ok'=>false,'msg'=>'Comisión requerida']); exit; }
    if(!$nombre){ echo json_encode(['ok'=>false,'msg'=>'El nombre es obligatorio']); exit; }

    $stmt = $conn->prepare("INSERT INTO comision_encargados (comision_id,nombre,celular) VALUES(?,?,?)");
    $stmt->bind_param('iss', $comision_id, $nombre, $celular);
    if($stmt->execute()){
      echo json_encode(['ok'=>true,'msg'=>'Encargado agregado']);
    } else {
      echo json_encode(['ok'=>false,'msg'=>'Error al agregar']);
    }
    $stmt->close();
    break;

  /* ── ELIMINAR ENCARGADO ── */
  case 'eliminar_encargado':
    $id = intval($_POST['id'] ?? 0);
    $conn->query("DELETE FROM comision_encargados WHERE id=$id");
    echo json_encode(['ok'=>true,'msg'=>'Encargado eliminado']);
    break;

 

  default:
    echo json_encode(['ok'=>false,'msg'=>'Acción no válida']);
}