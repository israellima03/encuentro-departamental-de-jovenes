<?php
require_once 'includes/funciones/bd_conexion.php';
header('Content-Type: application/json');

$accion = trim($_POST['accion'] ?? '');

if($accion !== 'enviar_pregunta'){
    echo json_encode(['ok'=>false,'msg'=>'Accion no valida']);
    exit;
}

/* primero leer los datos del POST */
$id_evento = intval($_POST['id_evento'] ?? 0);
$nombre    = 'Anonimo';
$pregunta  = trim($_POST['pregunta']    ?? '');

if(!$id_evento || !$pregunta){
    echo json_encode(['ok'=>false,'msg'=>'Escribe tu pregunta antes de enviar']);
    exit;
}

if(strlen($pregunta) > 500){
    echo json_encode(['ok'=>false,'msg'=>'La pregunta no puede superar 500 caracteres']);
    exit;
}

/* verificar si preguntas están activas para este evento */
$stmtA = $conn->prepare("SELECT preguntas_activas FROM eventos WHERE id_evento=? LIMIT 1");
$stmtA->bind_param('i', $id_evento);
$stmtA->execute();
$rowA = $stmtA->get_result()->fetch_assoc();
$stmtA->close();

if(!$rowA || !$rowA['preguntas_activas']){
    echo json_encode(['ok'=>false,'msg'=>'Las preguntas no estan activas para esta conferencia']);
    exit;
}

/* guardar pregunta */
$stmt = $conn->prepare("
    INSERT INTO preguntas_publico (id_evento, nombre_autor, pregunta, estado)
    VALUES (?, ?, ?, 'pendiente')
");
$stmt->bind_param('iss', $id_evento, $nombre, $pregunta);

if($stmt->execute()){
    echo json_encode(['ok'=>true,'msg'=>'¡Pregunta enviada de forma anonima!']);
} else {
    echo json_encode(['ok'=>false,'msg'=>'Error al guardar la pregunta']);
}
$stmt->close();