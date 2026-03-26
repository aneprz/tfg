<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = (int)$_SESSION['id_usuario'];
$id_conv = (int)$_POST['id_conv'];
$accion = $_POST['accion'];

// Obtener info del creador
$resC = mysqli_query($conexion, "SELECT id_usuario_creador FROM chat_conversacion WHERE id_conversacion = $id_conv");
$rowC = mysqli_fetch_assoc($resC);
$id_creador = (int)$rowC['id_usuario_creador'];

if ($accion === 'quitar') {
    $id_target = (int)$_POST['id_user'];
    // Solo el creador puede quitar a otros
    if ($id_yo === $id_creador && $id_target !== $id_creador) {
        mysqli_query($conexion, "DELETE FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario = $id_target");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No tienes permiso']);
    }
} 
elseif ($accion === 'abandonar') {
    // Cualquiera puede irse, PERO si el creador se va, deberías decidir si el grupo se borra o se queda sin jefe
    mysqli_query($conexion, "DELETE FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario = $id_yo");
    echo json_encode(['success' => true]);
}
elseif ($accion === 'añadir') {
    $id_target = (int)$_POST['id_user'];
    mysqli_query($conexion, "INSERT IGNORE INTO chat_participante (id_conversacion, id_usuario) VALUES ($id_conv, $id_target)");
    echo json_encode(['success' => true]);
}