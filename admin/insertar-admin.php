<?php
ini_set('display_errors', 0);
ob_start();
require_once('funciones/sesiones.php');
usuario_autentificado();
verificar_acceso(['Administrador']);
ob_clean();
header('Content-Type: application/json');
require_once('../includes/funciones/bd_conexion.php');

if(!isset($_POST['agregar-admin'])){
    echo json_encode(['ok'=>false,'msg'=>'Accion no valida']); exit;
}

$usuario  = trim($_POST['usuario']  ?? '');
$nombre   = trim($_POST['nombre']   ?? '');
$password = trim($_POST['password'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$roles    = $_POST['roles']         ?? [];
$iglesia_id  = intval($_POST['iglesia_id']  ?? 0) ?: null;
$distrito_id = intval($_POST['distrito_id'] ?? 0) ?: null;

if(!$usuario || !$nombre || !$password){
    echo json_encode(['ok'=>false,'msg'=>'Faltan campos obligatorios']); exit;
}
if(strlen($password) < 8){
    echo json_encode(['ok'=>false,'msg'=>'La contraseña debe tener al menos 8 caracteres']); exit;
}

/* verificar usuario duplicado */
$chk = $conn->prepare("SELECT id FROM usuarios WHERE usuario=? LIMIT 1");
$chk->bind_param('s',$usuario); $chk->execute(); $chk->store_result();
if($chk->num_rows > 0){
    echo json_encode(['ok'=>false,'msg'=>'El nombre de usuario ya está en uso']); exit;
}
$chk->close();

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);

$stmt = $conn->prepare("
    INSERT INTO usuarios (nombre, usuario, password, telefono, iglesia_id, distrito_id)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param('ssssii', $nombre, $usuario, $hash, $telefono, $iglesia_id, $distrito_id);

if(!$stmt->execute()){
    echo json_encode(['ok'=>false,'msg'=>'Error al guardar: '.$stmt->error]); exit;
}
$usuario_id = $conn->insert_id;
$stmt->close();

/* insertar roles */
if(!empty($roles)){
    $stmtR = $conn->prepare("INSERT INTO usuario_roles (usuario_id, rol_id) VALUES (?,?)");
    foreach($roles as $rol_id){
        $rid = intval($rol_id);
        if($rid > 0){ $stmtR->bind_param('ii',$usuario_id,$rid); $stmtR->execute(); }
    }
    $stmtR->close();
}

echo json_encode(['ok'=>true,'msg'=>'Administrador "'.htmlspecialchars($nombre).'" creado correctamente.']);