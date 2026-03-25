<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = (int)$_SESSION['id_usuario'];
$contenido = mysqli_real_escape_string($conexion, trim($_POST['mensaje']));
$id_conv = isset($_POST['id_conversacion']) ? (int)$_POST['id_conversacion'] : null;
$id_receptor = isset($_POST['id_receptor']) ? (int)$_POST['id_receptor'] : null;

if (empty($contenido)) exit;

if (!$id_conv && $id_receptor) {
    // 1. Crear la conversación
    mysqli_query($conexion, "INSERT INTO chat_conversacion (tipo) VALUES ('individual')");
    $id_conv = mysqli_insert_id($conexion);

    // 2. Añadir participantes
    mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario, estado_solicitud) VALUES ($id_conv, $id_yo, 'aceptada')");
    mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario, estado_solicitud) VALUES ($id_conv, $id_receptor, 'aceptada')");
    
    $nuevoChat = true;
}

// 3. Insertar el mensaje
mysqli_query($conexion, "INSERT INTO chat_mensaje (id_conversacion, id_emisor, contenido) VALUES ($id_conv, $id_yo, '$contenido')");

echo json_encode(['success' => true, 'nueva_id_conversacion' => isset($nuevoChat) ? $id_conv : null]);