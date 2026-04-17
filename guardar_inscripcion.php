<?php
require_once 'includes/funciones/bd_conexion.php';
header('Content-Type: application/json');
date_default_timezone_set('America/La_Paz');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('TESORERA_EMAIL', 'limacondoriisrael@gmail.com');
define('CARPETA_COMP',   'comprobantes/');

if (!is_dir(CARPETA_COMP)) mkdir(CARPETA_COMP, 0755, true);

$accion = trim($_POST['accion'] ?? '');

switch($accion) {

    /* ================================================
       VERIFICAR SI YA ESTA INSCRITO
       ================================================ */
    case 'verificar':
        $carnet  = limpiar($_POST['carnet']  ?? '');
        $celular = limpiar($_POST['celular'] ?? '');

        if(!$carnet && !$celular){
            echo json_encode(['inscrito' => false]);
            exit;
        }

        $stmt = $conn->prepare("
            SELECT i.id, i.nombre, i.apellido, ins.estado_pago
            FROM inscritos i
            INNER JOIN inscripciones ins ON ins.inscrito_id = i.id
            WHERE i.carnet = ? OR i.celular = ?
            LIMIT 1
        ");
        $stmt->bind_param('ss', $carnet, $celular);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if($fila){
            echo json_encode([
                'inscrito' => true,
                'nombre'   => $fila['nombre'] . ' ' . $fila['apellido'],
                'estado'   => $fila['estado_pago']
            ]);
        } else {
            echo json_encode(['inscrito' => false]);
        }
        exit;

    /* ================================================
       BUSCAR ESTADO (buscador publico)
       ================================================ */
    case 'buscar_estado':
        $busqueda = limpiar($_POST['busqueda'] ?? '');

        if(!$busqueda){
            echo json_encode(['ok' => false, 'msg' => 'Ingresa un carnet o celular']);
            exit;
        }

        $stmt = $conn->prepare("
            SELECT i.nombre, i.apellido, i.carnet, i.celular,
                   ins.estado_pago, ins.fecha_pago, p.nombre AS paquete
            FROM inscritos i
            INNER JOIN inscripciones ins ON ins.inscrito_id = i.id
            INNER JOIN paquetes p ON ins.paquete_id = p.id
            WHERE i.carnet = ? OR i.celular = ?
            LIMIT 1
        ");
        $stmt->bind_param('ss', $busqueda, $busqueda);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if($fila){
            echo json_encode(['ok' => true, 'datos' => $fila]);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'No se encontro ningun inscrito con ese carnet o celular']);
        }
        exit;

    /* ================================================
       SUBIR COMPROBANTE
       ================================================ */
    case 'subir_comprobante':
        if(!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK){
            echo json_encode(['ok' => false, 'msg' => 'Error al recibir el archivo']);
            exit;
        }

        $archivo   = $_FILES['comprobante'];
        $ext       = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','webp','pdf'];

        if(!in_array($ext, $permitidos)){
            echo json_encode(['ok' => false, 'msg' => 'Solo se permiten imagenes JPG, PNG, WEBP o PDF']);
            exit;
        }

        if($archivo['size'] > 5 * 1024 * 1024){
            echo json_encode(['ok' => false, 'msg' => 'El archivo no puede superar 5MB']);
            exit;
        }

        $nombre_base = sanitizar(($_POST['nombre_inscrito'] ?? 'x') . '_' . ($_POST['apellido_inscrito'] ?? 'x'));
        $nombre_arch = 'comp_' . $nombre_base . '_' . time() . '.' . $ext;
        $destino     = CARPETA_COMP . $nombre_arch;

        if(!move_uploaded_file($archivo['tmp_name'], $destino)){
            echo json_encode(['ok' => false, 'msg' => 'No se pudo guardar el archivo en el servidor']);
            exit;
        }

        echo json_encode(['ok' => true, 'archivo' => $nombre_arch, 'msg' => 'Comprobante subido correctamente']);
        exit;

    /* ================================================
       REGISTRAR INSCRITO + INSCRIPCION
       ================================================ */
    case 'registrar':
        $nombre       = limpiar($_POST['nombre']           ?? '');
        $apellido     = limpiar($_POST['apellido']         ?? '');
        $carnet       = limpiar($_POST['carnet']           ?? '');
        $fecha_nac    = limpiar($_POST['fecha_nacimiento'] ?? '');
        $edad         = intval($_POST['edad']              ?? 0);
        $celular      = limpiar($_POST['celular']          ?? '');
        $min_id       = intval($_POST['ministerio_id']     ?? 0);
        $igl_id       = intval($_POST['iglesia_id']        ?? 0);
        $dis_id       = intval($_POST['distrito_id']       ?? 0);
        $tipo_id      = intval($_POST['tipo_inscrito_id']  ?? 0);
        $paquete_id   = intval($_POST['paquete_id']        ?? 0);
        $regalo_id    = intval($_POST['regalo_id']         ?? 0);
        $comprobante  = limpiar($_POST['comprobante_arch'] ?? '');
        $prod_json    = $_POST['productos_json']           ?? '[]';

        /* validacion basica servidor */
        if(!$nombre || !$apellido || !$carnet || !$celular || !$paquete_id || !$comprobante){
            echo json_encode(['ok' => false, 'msg' => 'Faltan datos obligatorios']);
            exit;
        }

        /* verificar duplicado */
        $stmt = $conn->prepare("SELECT id FROM inscritos WHERE carnet = ? OR celular = ? LIMIT 1");
        $stmt->bind_param('ss', $carnet, $celular);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0){
            echo json_encode(['ok' => false, 'msg' => 'Ya existe una inscripcion con ese carnet o celular']);
            $stmt->close();
            exit;
        }
        $stmt->close();

        /* obtener precio paquete con descuento */
        $stmtP = $conn->prepare("
            SELECT p.precio,
                   ROUND(p.precio - (p.precio * COALESCE(d.porcentaje,0) / 100), 2) AS precio_final
            FROM paquetes p
            LEFT JOIN paquete_descuentos pd ON p.id = pd.paquete_id
            LEFT JOIN descuentos d ON pd.descuento_id = d.id AND d.activo = 1
            WHERE p.id = ? LIMIT 1
        ");
        $stmtP->bind_param('i', $paquete_id);
        $stmtP->execute();
        $paqData      = $stmtP->get_result()->fetch_assoc();
        $precio_orig  = $paqData['precio']       ?? 0;
        $precio_final = $paqData['precio_final'] ?? $precio_orig;
        $descuento    = $precio_orig - $precio_final;
        $stmtP->close();

        /* calcular total con productos */
        $total    = $precio_final;
        $productos = json_decode($prod_json, true) ?: [];
        foreach($productos as $prod){
            $pid  = intval($prod['id']       ?? 0);
            $cant = intval($prod['cantidad'] ?? 0);
            if($pid > 0 && $cant > 0){
                $stmtPr = $conn->prepare("SELECT precio FROM productos WHERE id = ?");
                $stmtPr->bind_param('i', $pid);
                $stmtPr->execute();
                $pr = $stmtPr->get_result()->fetch_assoc();
                if($pr) $total += $pr['precio'] * $cant;
                $stmtPr->close();
            }
        }

        /* INSERTAR INSCRITO */
        $stmtI = $conn->prepare("
            INSERT INTO inscritos
            (nombre, apellido, carnet, fecha_nacimiento, edad, celular,
             ministerio_id, iglesia_id, distrito_id, tipo_inscrito_id)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ");
        $stmtI->bind_param('ssssissiii',
            $nombre, $apellido, $carnet, $fecha_nac, $edad, $celular,
            $min_id, $igl_id, $dis_id, $tipo_id
        );

        if(!$stmtI->execute()){
            echo json_encode(['ok' => false, 'msg' => 'Error al guardar inscrito: ' . $stmtI->error]);
            $stmtI->close();
            exit;
        }
        $inscrito_id = $conn->insert_id;
        $stmtI->close();

        /* INSERTAR INSCRIPCION */
        $stmtIn = $conn->prepare("
            INSERT INTO inscripciones
            (inscrito_id, paquete_id, precio_original, descuento_aplicado,
             precio_final, metodo_pago, estado_pago, comprobante_qr, fecha_pago)
            VALUES (?,?,?,?,?,'qr','pendiente',?,NOW())
        ");
        $stmtIn->bind_param('iiddds',
            $inscrito_id, $paquete_id,
            $precio_orig, $descuento, $total,
            $comprobante
        );

        if(!$stmtIn->execute()){
            echo json_encode(['ok' => false, 'msg' => 'Error al guardar inscripcion: ' . $stmtIn->error]);
            $stmtIn->close();
            exit;
        }
        $inscripcion_id = $conn->insert_id;
        $stmtIn->close();

        /* INSERTAR PRODUCTOS */
        foreach($productos as $prod){
            $pid   = intval($prod['id']       ?? 0);
            $cant  = intval($prod['cantidad'] ?? 0);
            $talla = limpiar($prod['talla']   ?? '');

            if($pid > 0 && $cant > 0){

                $stmtProd = $conn->prepare("
                    INSERT INTO inscripcion_productos 
                   (inscripcion_id, producto_id, cantidad, talla)
                    VALUES (?,?,?,?)
                ");

                if($stmtProd){
                    $stmtProd->bind_param('iiis', $inscripcion_id, $pid, $cant, $talla);
                    $stmtProd->execute();
                   $stmtProd->close();
                }

                // descontar cupos
                $conn->query("
                    UPDATE productos 
                    SET cupos_disponibles = cupos_disponibles - $cant 
                    WHERE id = $pid AND cupos_disponibles >= $cant
                ");
            }
        }

        /* INSERTAR REGALO */
        if($regalo_id > 0){
            $stmtR = $conn->prepare("INSERT INTO inscripcion_regalos (inscripcion_id, regalo_id) VALUES (?,?)");
            $stmtR->bind_param('ii', $inscripcion_id, $regalo_id);
            $stmtR->execute();
            $stmtR->close();
            $conn->query("UPDATE regalos SET cupos_disponibles = cupos_disponibles - 1 WHERE id = $regalo_id AND cupos_disponibles > 0");
        }

        /* DESCONTAR CUPO DEL PAQUETE */
        $conn->query("UPDATE paquetes SET cupos_disponibles = cupos_disponibles - 1 WHERE id = $paquete_id AND cupos_disponibles > 0");

        /* CORREO A LA TESORERA */
        $asunto = '=?UTF-8?B?' . base64_encode('Nuevo comprobante — Encuentro Departamental') . '?=';
        $cuerpo  = "Nueva inscripcion recibida:\n\n";
        $cuerpo .= "Nombre:      $nombre $apellido\n";
        $cuerpo .= "Carnet:      $carnet\n";
        $cuerpo .= "Celular:     $celular\n";
        $cuerpo .= "Total:       Bs. " . number_format($total,2) . "\n";
        $cuerpo .= "Comprobante: $comprobante\n";
        $cuerpo .= "Fecha:       " . date('d/m/Y H:i') . "\n\n";
        $cuerpo .= "Ingresa al sistema para verificar el comprobante y confirmar la inscripcion.\n";
        $cabeceras = "From: noreply@encuentro.com\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        @mail(TESORERA_EMAIL, $asunto, $cuerpo, $cabeceras);
                /* =========================================
           ENVIO REAL DE CORREO (PHPMailer)
          ========================================= */

     

        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';

        try {

            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'limacondoriisrael@gmail.com';
            $mail->Password = 'updb xpfj qhcf sndb'; // ⚠️ AQUI TU CLAVE
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('limacondoriisrael@gmail.com', 'Sistema Encuentro');
            $mail->addAddress(TESORERA_EMAIL);

            $mail->Subject = 'Nuevo comprobante — Encuentro';

            $rutaArchivo = CARPETA_COMP . $comprobante;

            // activar HTML
            $mail->isHTML(true);

            // 📸 si existe imagen
            if(file_exists($rutaArchivo)){

                
                $mail->Body = nl2br($cuerpo) . 
                    '<br><br><b>Comprobante adjunto en este correo:</b>';

                // también adjunto
                $mail->addAttachment($rutaArchivo);
            } else {
                $mail->Body = nl2br($cuerpo);
            }

            $mail->send();

        } catch (Exception $e) {
            // opcional: guardar error temporal
            echo json_encode([
                   'ok' => false,
                   'msg' => 'Error correo: ' . $mail->ErrorInfo
               ]);
               exit;
                   }

        echo json_encode([
            'ok'  => true,
            'msg' => "Tu inscripcion fue recibida con exito. Estado: PENDIENTE. La tesorera revisara tu comprobante y confirmara tu inscripcion. Para saber si fue confirmada consulta mas abajo o pregunta a tu lider local o distrital."
        ]);
        exit;

    default:
        echo json_encode(['ok' => false, 'msg' => 'Accion no valida']);
        exit;
}

function limpiar($v){ return htmlspecialchars(strip_tags(trim($v))); }
function sanitizar($n){
    $n = strtolower($n);
    $n = str_replace([' ','á','é','í','ó','ú','ñ'],['_','a','e','i','o','u','n'],$n);
    return preg_replace('/[^a-z0-9_]/','',$n);
}