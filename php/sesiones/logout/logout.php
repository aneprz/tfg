<?php
session_start();
require '../../../db/conexiones.php';
require_once __DIR__ . '/../remember_me.php';

salsabox_forget_current_remember_token($conexion);
salsabox_clear_remember_cookie();

$_SESSION = [];
session_destroy();
header("Location: ../../../index.php");
exit();
?>
