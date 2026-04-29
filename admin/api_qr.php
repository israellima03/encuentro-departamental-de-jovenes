<?php
require_once('funciones/sesiones.php');
usuario_autentificado();
header('Content-Type: application/json');
require_once('../includes/funciones/bd_conexion.php');

$accion = trim($_GET['accion'] ?? $_POST['accion'] ?? '');

switch($accion){

    /* ── LISTAR inscripciones por QR ── */
    case 'listar':
        $buscar      = '%' . trim($_GET['buscar'] ?? '') . '%';
        $estado      = trim($_GET['estado'] ?? 'pendiente');
        $fecha_desde = trim($_GET['fecha_desde'] ?? '');
        $fecha_hasta = trim($_GET['fecha_hasta'] ?? '');

        $where   = "WHERE ins.metodo_pago = 'qr'";
        $params  = [];
        $tipos   = '';

        if($estado !== ''){
            $where   .= " AND ins.estado_pago = ?";
            $params[] = $estado;
            $tipos   .= 's';
        }

        if($fecha_desde !== ''){
            $where   .= " AND DATE(ins.fecha_pago) >= ?";
            $params[] = $fecha_desde;
            $tipos   .= 's';
        }

        if($fecha_hasta !== ''){
            $where   .= " AND DATE(ins.fecha_pago) <= ?";
            $params[] = $fecha_hasta;
            $tipos   .= 's';
        }

        /* busqueda por nombre carnet celular */
        $buscarVal = trim($_GET['buscar'] ?? '');
        if($buscarVal !== ''){
            $like     = '%' . $buscarVal . '%';
            $where   .= " AND (i.nombre LIKE ? OR i.apellido LIKE ? OR i.carnet LIKE ? OR i.celular LIKE ?)";
            $params[] = $like; $params[] = $like;
            $params[] = $like; $params[] = $like;
            $tipos   .= 'ssss';
        }

        $sql = "
            SELECT
                ins.id               AS inscripcion_id,
                i.nombre, i.apellido, i.carnet, i.celular,
                ig.nombre            AS iglesia,
                p.nombre             AS paquete,
                ins.precio_original,
                ins.precio_paquete,
                ins.precio_productos,
                ins.descuento_aplicado,
                ins.precio_final,
                ins.estado_pago,
                ins.comprobante_qr,
                ins.fecha_pago,
                ins.fecha_confirmacion,
                CONCAT(uc.nombre)    AS confirmado_por_nombre
            FROM inscripciones ins
            INNER JOIN inscritos i   ON i.id   = ins.inscrito_id
            LEFT JOIN  iglesias  ig  ON ig.id  = i.iglesia_id
            LEFT JOIN  paquetes  p   ON p.id   = ins.paquete_id
            LEFT JOIN  usuarios  uc  ON uc.id  = ins.confirmado_por
            $where
            ORDER BY ins.fecha_pago DESC
        ";

        $stmt = $conn->prepare($sql);
        if(!empty($params)){
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();

        $filas = [];
        while($r = $res->fetch_assoc()) $filas[] = $r;
        $stmt->close();

        /* conteos rapidos */
        $stmtC = $conn->query("
            SELECT
                SUM(estado_pago = 'pendiente')  AS pendientes,
                SUM(estado_pago = 'confirmado') AS confirmados
            FROM inscripciones
            WHERE metodo_pago = 'qr'
        ");
        $conteos = $stmtC->fetch_assoc();

        echo json_encode([
            'ok'          => true,
            'datos'       => $filas,
            'pendientes'  => intval($conteos['pendientes']  ?? 0),
            'confirmados' => intval($conteos['confirmados'] ?? 0),
        ]);
        break;

    /* ── CONFIRMAR pago ── */
    case 'confirmar':
        $inscripcion_id   = intval($_POST['inscripcion_id'] ?? 0);
        $confirmado_por   = intval($_SESSION['usuario_id']  ?? 0);

        if(!$inscripcion_id){
            echo json_encode(['ok'=>false,'msg'=>'ID invalido']);
            exit;
        }

        $stmt = $conn->prepare("
            UPDATE inscripciones
            SET estado_pago      = 'confirmado',
                confirmado_por   = ?,
                fecha_confirmacion = NOW()
            WHERE id = ? AND metodo_pago = 'qr'
        ");
        $stmt->bind_param('ii', $confirmado_por, $inscripcion_id);

        if($stmt->execute() && $stmt->affected_rows > 0){
            echo json_encode(['ok'=>true,'msg'=>'Pago confirmado correctamente']);
        } else {
            echo json_encode(['ok'=>false,'msg'=>'No se pudo confirmar — verifica que sea una inscripcion QR pendiente']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['ok'=>false,'msg'=>'Accion no valida']);
}