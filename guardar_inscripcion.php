<?php
require_once('includes/funciones/bd_conexion.php');

header('Content-Type: application/json');

/* ============================================
   SUBIDA DEL COMPROBANTE
   ============================================ */
if(isset($_FILES['comprobante'])){

    $nombre    = trim($_POST['nombre_inscrito'] ?? 'sin_nombre');
    $apellido  = trim($_POST['apellido_inscrito'] ?? 'sin_apellido');
    $archivo   = $_FILES['comprobante'];

    /* limpia el nombre para el archivo */
    $nombre_limpio   = preg_replace('/[^a-zA-Z0-9]/', '_', $nombre);
    $apellido_limpio = preg_replace('/[^a-zA-Z0-9]/', '_', $apellido);
    $extension       = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_archivo  = $nombre_limpio . '_' . $apellido_limpio . '_' . time() . '.' . $extension;

    $carpeta = 'comprobantes/';
    if(!is_dir($carpeta)) mkdir($carpeta, 0755, true);

    $destino = $carpeta . $nombre_archivo;

    /* tipos permitidos */
    $tipos_permitidos = ['image/jpeg','image/png','image/jpg','application/pdf'];
    if(!in_array($archivo['type'], $tipos_permitidos)){
        echo json_encode(['ok' => false, 'msg' => 'Solo se permiten imagenes o PDF']);
        exit;
    }

    if(move_uploaded_file($archivo['tmp_name'], $destino)){

        /* envia email a la tesorera */
        $tesorera_email = 'israellimacondori32@gmail.com'; /* cambia por el email real */
        $asunto = 'Nueva inscripcion pendiente de verificacion';
        $cuerpo = "La inscrita: {$nombre} {$apellido}\n";
        $cuerpo .= "acaba de subir un comprobante de pago.\n\n";
        $cuerpo .= "Comprobante: {$nombre_archivo}\n\n";
        $cuerpo .= "Por favor verifica y confirma su inscripcion en el sistema.\n";
        $cabeceras = 'From: sistema@encuentro.com';

        mail($tesorera_email, $asunto, $cuerpo, $cabeceras);

        echo json_encode([
            'ok'      => true, 
            'archivo' => $nombre_archivo,
            'msg'     => 'Comprobante subido correctamente'
        ]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Error al subir el archivo']);
    }
    exit;
}

/* ============================================
   GUARDAR INSCRIPCION EN LA BD
   ============================================ */
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar'){

    $nombre          = trim($_POST['nombre'] ?? '');
    $apellido        = trim($_POST['apellido'] ?? '');
    $carnet          = trim($_POST['carnet'] ?? '');
    $fecha_nac       = $_POST['fecha_nacimiento'] ?? '';
    $edad            = (int)($_POST['edad'] ?? 0);
    $celular         = trim($_POST['celular'] ?? '');
    $ministerio_id   = (int)($_POST['ministerio_id'] ?? 0);
    $iglesia_id      = (int)($_POST['iglesia_id'] ?? 0);
    $distrito_id     = (int)($_POST['distrito_id'] ?? 0);
    $tipo_id         = (int)($_POST['tipo_inscrito_id'] ?? 0);
    $paquete_id      = (int)($_POST['paquete_id'] ?? 0);
    $comprobante     = $_POST['comprobante_archivo'] ?? '';

    /* validacion basica del lado servidor */
    if(!$nombre || !$apellido || !$carnet || !$paquete_id){
        echo json_encode(['ok' => false, 'msg' => 'Faltan datos obligatorios']);
        exit;
    }

    /* obtiene precio del paquete */
    $res_paq = $conn->query("
        SELECT p.precio,
               ROUND(p.precio - (p.precio * d.porcentaje / 100), 2) AS precio_final
        FROM paquetes p
        LEFT JOIN paquete_descuentos pd ON p.id = pd.paquete_id
        LEFT JOIN descuentos d ON pd.descuento_id = d.id AND d.activo = 1
        WHERE p.id = $paquete_id
    ");
    $paq_data    = $res_paq->fetch_assoc();
    $precio_orig = $paq_data['precio'];
    $precio_fin  = $paq_data['precio_final'] ?? $precio_orig;
    $descuento   = $precio_orig - $precio_fin;

    /* inserta inscrito */
    $stmt = $conn->prepare("
        INSERT INTO inscritos 
        (nombre, apellido, carnet, fecha_nacimiento, edad, celular,
         ministerio_id, iglesia_id, distrito_id, tipo_inscrito_id)
        VALUES (?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->bind_param('ssssisiii',
        $nombre, $apellido, $carnet, $fecha_nac,
        $edad, $celular,
        $ministerio_id, $iglesia_id, $distrito_id, $tipo_id
    );
    $stmt->execute();
    $inscrito_id = $conn->insert_id;
    $stmt->close();

    /* inserta inscripcion */
    $stmt2 = $conn->prepare("
        INSERT INTO inscripciones
        (inscrito_id, paquete_id, precio_original, descuento_aplicado, 
         precio_final, metodo_pago, estado_pago, comprobante_qr, fecha_pago)
        VALUES (?,?,?,?,?,'qr','pendiente',?,NOW())
    ");
    $stmt2->bind_param('iiddds',
        $inscrito_id, $paquete_id,
        $precio_orig, $descuento, $precio_fin,
        $comprobante
    );
    $stmt2->execute();
    $stmt2->close();

    echo json_encode([
        'ok'  => true,
        'msg' => 'Tu inscripcion fue registrada. Tu pago sera verificado por la tesorera. Para saber si tu inscripcion fue confirmada pregunta a tu lider local o distrital.'
    ]);
    exit;
}