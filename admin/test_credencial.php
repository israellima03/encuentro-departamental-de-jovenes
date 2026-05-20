<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('../includes/funciones/bd_conexion.php');

$r = $conn->query("SELECT i.id, ins.id AS inscripcion_id FROM inscritos i INNER JOIN inscripciones ins ON ins.inscrito_id=i.id LIMIT 1")->fetch_assoc();

if(!$r){ die('No hay inscritos'); }

echo "Inscrito ID: " . $r['id'] . "<br>";
echo "Inscripcion ID: " . $r['inscripcion_id'] . "<br>";

require_once(__DIR__ . '/funciones_credencial.php');

$resultado = generarCredencialHTML($conn, $r['id'], $r['inscripcion_id']);

echo "Resultado: ";
var_dump($resultado);