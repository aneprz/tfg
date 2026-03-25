<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = (int)$_SESSION['id_usuario'];
$texto = mysqli_real_escape_string($conexion, trim($_POST['mensaje']));
$id_conv = !empty($_POST['id_conversacion']) ? (int)$_POST['id_conversacion'] : null;
$id_receptor = !empty($_POST['id_receptor']) ? (int)$_POST['id_receptor'] : null;

$nueva_id = null;

if (empty($texto)) exit;

if (!$id_conv && $id_receptor) {
    mysqli_query($conexion, "INSERT INTO chat_conversacion (tipo) VALUES ('individual')");
    $id_conv = mysqli_insert_id($conexion);
    mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario, estado_solicitud) VALUES ($id_conv, $id_yo, 'aceptada')");
    mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario, estado_solicitud) VALUES ($id_conv, $id_receptor, 'aceptada')");
    $nueva_id = $id_conv;
}

if ($id_conv) {
    mysqli_query($conexion, "INSERT INTO chat_mensaje (id_conversacion, id_emisor, contenido) VALUES ($id_conv, $id_yo, '$texto')");
    // Actualizar mi propia lectura al enviar
    mysqli_query($conexion, "UPDATE chat_participante SET ultima_lectura = NOW() WHERE id_conversacion = $id_conv AND id_usuario = $id_yo");
}

echo json_encode(['success' => true, 'nueva_id_conversacion' => $nueva_id]);