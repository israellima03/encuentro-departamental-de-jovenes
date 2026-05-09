<?php
ini_set('display_errors', 0);
ob_start();
require_once('funciones/sesiones.php');
usuario_autentificado();
ob_clean();
header('Content-Type: application/json');
require_once('../includes/funciones/bd_conexion.php');

$accion = trim($_GET['accion'] ?? $_POST['accion'] ?? '');

switch($accion){

  case 'listar':
    $res = $conn->query("
      SELECT u.id, u.nombre, u.usuario, u.telefono,
             d.id AS distrito_id, d.nombre AS distrito,
             ig.id AS iglesia_id, ig.nombre AS iglesia,
             GROUP_CONCAT(r.id ORDER BY r.id SEPARATOR ',') AS roles_ids,
             GROUP_CONCAT(r.nombre ORDER BY r.nombre SEPARATOR ', ') AS roles
      FROM usuarios u
      LEFT JOIN distritos d      ON d.id  = u.distrito_id
      LEFT JOIN iglesias ig      ON ig.id = u.iglesia_id
      LEFT JOIN usuario_roles ur ON ur.usuario_id = u.id
      LEFT JOIN roles r          ON r.id  = ur.rol_id
      GROUP BY u.id ORDER BY u.nombre
    ");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['ok'=>true,'admins'=>$rows]);
    break;

  case 'editar_admin':
    $id       = intval($_POST['id']       ?? 0);
    $nombre   = trim($_POST['nombre']     ?? '');
    $telefono = trim($_POST['telefono']   ?? '');
    $roles    = $_POST['roles']           ?? [];

    if(!$id || !$nombre){
      echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']); exit;
    }

    $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, telefono=? WHERE id=?");
    $stmt->bind_param('ssi', $nombre, $telefono, $id);
    $stmt->execute(); $stmt->close();

    /* actualizar roles */
    $conn->query("DELETE FROM usuario_roles WHERE usuario_id=$id");
    if(!empty($roles)){
      $stmtR = $conn->prepare("INSERT INTO usuario_roles (usuario_id,rol_id) VALUES (?,?)");
      foreach($roles as $rol_id){
        $rid = intval($rol_id);
        if($rid > 0){ $stmtR->bind_param('ii',$id,$rid); $stmtR->execute(); }
      }
      $stmtR->close();
    }

    echo json_encode(['ok'=>true,'msg'=>'Administrador actualizado correctamente']);
    break;

  case 'eliminar_admin':
    $id = intval($_POST['id'] ?? 0);
    if(!$id){ echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }
    if($id === intval($_SESSION['usuario_id'] ?? 0)){
      echo json_encode(['ok'=>false,'msg'=>'No puedes eliminarte a ti mismo']); exit;
    }
    $conn->query("DELETE FROM usuario_roles WHERE usuario_id=$id");
    $conn->query("DELETE FROM usuarios WHERE id=$id");
    echo json_encode(['ok'=>true,'msg'=>'Administrador eliminado']);
    break;

  case 'verificar_usuario':
    $usuario = trim($_GET['usuario'] ?? '');
    $excluir = intval($_GET['excluir'] ?? 0);
    if(!$usuario){ echo json_encode(['disponible'=>true]); exit; }
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario=? AND id!=? LIMIT 1");
    $stmt->bind_param('si', $usuario, $excluir);
    $stmt->execute(); $stmt->store_result();
    echo json_encode(['disponible'=> $stmt->num_rows === 0]);
    $stmt->close();
    break;

  default:
    echo json_encode(['ok'=>false,'msg'=>'Accion no valida']);
}