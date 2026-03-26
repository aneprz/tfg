<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = (int)$_SESSION['id_usuario'];
$id_conv = (int)$_POST['id_conv'];
$id_target = (int)$_POST['id_user'];
$accion = $_POST['accion'];

// Verificar que YO soy el creador para poder quitar gente
$resC = mysqli_query($conexion, "SELECT id_usuario_creador FROM chat_conversacion WHERE id_conversacion = $id_conv");
$creador = mysqli_fetch_assoc($resC)['id_usuario_creador'];

if ($accion === 'quitar' && $id_yo == $creador) {
    mysqli_query($conexion, "DELETE FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario = $id_target");
    echo json_encode(['success' => true]);
} 
elseif ($accion === 'añadir') {
    mysqli_query($conexion, "INSERT IGNORE INTO chat_participante (id_conversacion, id_usuario, estado_solicitud) VALUES ($id_conv, $id_target, 'aceptada')");
    echo json_encode(['success' => true]);
} 
else {
    echo json_encode(['success' => false, 'error' => 'No tienes permiso']);
}