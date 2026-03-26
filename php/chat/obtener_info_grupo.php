<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = (int)$_SESSION['id_usuario'];
$id_conv = (int)$_GET['id_conv'];

// 1. ¿Quién es el creador?
$resC = mysqli_query($conexion, "SELECT id_usuario_creador FROM chat_conversacion WHERE id_conversacion = $id_conv");
$infoC = mysqli_fetch_assoc($resC);
$id_creador = (int)($infoC['id_usuario_creador'] ?? 0);

// 2. Miembros actuales
$miembros = [];
$sqlM = "SELECT u.id_usuario, u.gameTag 
         FROM chat_participante p 
         JOIN usuario u ON p.id_usuario = u.id_usuario 
         WHERE p.id_conversacion = $id_conv";
$resM = mysqli_query($conexion, $sqlM);
while($row = mysqli_fetch_assoc($resM)) {
    $row['es_creador'] = ((int)$row['id_usuario'] === $id_creador);
    $miembros[] = $row;
}

// 3. Mis amigos que NO están en este grupo
// Ajusta 'amigo' e 'id_usuario' según tus tablas de amistad
$amigos_fuera = [];
$sqlA = "SELECT u.id_usuario, u.gameTag 
         FROM amistad a 
         JOIN usuario u ON (a.id_usuario_2 = u.id_usuario) 
         WHERE a.id_usuario_1 = $id_yo 
         AND a.estado = 'aceptada'
         AND u.id_usuario NOT IN (SELECT id_usuario FROM chat_participante WHERE id_conversacion = $id_conv)";
$resA = mysqli_query($conexion, $sqlA);
while($row = mysqli_fetch_assoc($resA)) {
    $amigos_fuera[] = $row;
}

echo json_encode([
    'soy_creador' => ($id_yo === $id_creador),
    'miembros' => $miembros,
    'amigos_fuera' => $amigos_fuera
]);