<?php
function get_config(mysqli $conn): array {
  $cfg = [];
  $res = $conn->query("SELECT clave, valor FROM config_sitio");
  if($res) while($r = $res->fetch_assoc()) $cfg[$r['clave']] = $r['valor'];
  return $cfg;
}
function get_noticias(mysqli $conn): array {
  $rows = [];
  $res = $conn->query("SELECT texto FROM noticias_footer ORDER BY orden ASC");
  if($res) while($r = $res->fetch_assoc()) $rows[] = $r['texto'];
  return $rows;
}
function get_redes(mysqli $conn): array {
  $rows = [];
  $res = $conn->query("SELECT * FROM redes_sociales WHERE activo=1 ORDER BY orden ASC");
  if($res) while($r = $res->fetch_assoc()) $rows[] = $r;
  return $rows;
}