<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = (int)$_SESSION['id_usuario'];
$id_conv = (int)$_POST['id_conv'];
$id_target = (int)$_POST['id_user'];
$accion = $_POST['accion'];

// 1. Verificar si soy el creador (para poder eliminar)
$resC = mysqli_query($conexion, "SELECT id_usuario_creador FROM chat_conversacion WHERE id_conversacion = $id_conv");
$creador = mysqli_fetch_assoc($resC)['id_usuario_creador'];

if ($accion === 'quitar' && $id_yo == $creador) {
    // No puedes eliminarte a ti mismo si eres el creador (o sí, pero mejor no)
    if ($id_target != $id_yo) {
        mysqli_query($conexion, "DELETE FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario = $id_target");
    }
    echo json_encode(['success' => true]);
} 
elseif ($accion === 'añadir') {
    // Añadimos al amigo al grupo
    mysqli_query($conexion, "INSERT IGNORE INTO chat_participante (id_conversacion, id_usuario) VALUES ($id_conv, $id_target)");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No permitido']);
}