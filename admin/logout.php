<?php
/* destruir sesion completamente */
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$_SESSION = array();
session_unset();

if(isset($_COOKIE[session_name()])){
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Location: login.php');
exit();