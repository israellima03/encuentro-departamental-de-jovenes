<?php
require_once('../includes/funciones/bd_conexion.php');
header('Content-Type: application/json');

if(!isset($_POST['agregar-admin'])){
    echo json_encode(['ok' => false, 'msg' => 'Accion no valida']);
    exit;
}

$usuario  = trim($_POST['usuario']  ?? '');
$nombre   = trim($_POST['nombre']   ?? '');
$password = trim($_POST['password'] ?? '');
$roles    = $_POST['roles']         ?? [];

/* validacion basica servidor */
if(!$usuario || !$nombre || !$password){
    echo json_encode(['ok' => false, 'msg' => 'Faltan campos obligatorios']);
    exit;
}

if(strlen($password) < 8){
    echo json_encode(['ok' => false, 'msg' => 'La contrasena debe tener al menos 8 caracteres']);
    exit;
}

/* verificar si el usuario ya existe */
$stmtCheck = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ? LIMIT 1");
$stmtCheck->bind_param('s', $usuario);
$stmtCheck->execute();
$stmtCheck->store_result();
if($stmtCheck->num_rows > 0){
    echo json_encode(['ok' => false, 'msg' => 'El nombre de usuario ya esta en uso']);
    $stmtCheck->close();
    exit;
}
$stmtCheck->close();

/* hashear el password con bcrypt costo 12 */
$password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

/* datos opcionales de afiliacion */
$ministerio_id = intval($_POST['ministerio_id'] ?? 0) ?: null;
$iglesia_id    = intval($_POST['iglesia_id']    ?? 0) ?: null;
$distrito_id   = intval($_POST['distrito_id']  ?? 0) ?: null;

/* -- 1. INSERTAR USUARIO -- */
$stmtU = $conn->prepare("
    INSERT INTO usuarios (nombre, usuario, password, ministerio_id, iglesia_id, distrito_id)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmtU->bind_param('sssiii',
    $nombre, $usuario, $password_hash,
    $ministerio_id, $iglesia_id, $distrito_id
);

if(!$stmtU->execute()){
    echo json_encode(['ok' => false, 'msg' => 'Error al guardar: ' . $stmtU->error]);
    $stmtU->close();
    exit;
}

$usuario_id = $conn->insert_id;
$stmtU->close();

/* -- 2. INSERTAR ROLES -- */
if(!empty($roles)){
    $stmtR = $conn->prepare("INSERT INTO usuario_roles (usuario_id, rol_id) VALUES (?, ?)");
    foreach($roles as $rol_id){
        $rol_id = intval($rol_id);
        if($rol_id > 0){
            $stmtR->bind_param('ii', $usuario_id, $rol_id);
            $stmtR->execute();
        }
    }
    $stmtR->close();
}

echo json_encode([
    'ok'  => true,
    'msg' => 'Administrador "' . htmlspecialchars($nombre) . '" creado correctamente.'
]);
exit;

