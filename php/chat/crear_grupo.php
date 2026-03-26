<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

// 1. Capturamos quién crea el grupo y el nombre
$id_yo = $_SESSION['id_usuario'];
$nombre_grupo = mysqli_real_escape_string($conexion, $_POST['nombre_grupo']);
$amigos_ids = $_POST['amigos'] ?? []; // Array de IDs de los checks seleccionados

// 2. INSERTAR LA CONVERSACIÓN (Aquí es donde pones el código que te pasé)
// IMPORTANTE: id_usuario_creador es lo que te faltaba para que salgan los botones
$sql = "INSERT INTO chat_conversacion (tipo, nombre, id_usuario_creador) 
        VALUES ('grupal', '$nombre_grupo', $id_yo)";

if (mysqli_query($conexion, $sql)) {
    // 3. Obtenemos el ID del grupo recién creado
    $id_nueva_conv = mysqli_insert_id($conexion);

    // 4. METEMOS A LOS PARTICIPANTES
    // Primero te metes a ti mismo como creador
    mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario) VALUES ($id_nueva_conv, $id_yo)");

    // Luego metemos a todos los amigos seleccionados
    foreach ($amigos_ids as $id_amigo) {
        $id_amigo = (int)$id_amigo;
        mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario) VALUES ($id_nueva_conv, $id_amigo)");
    }

    echo json_encode(['success' => true, 'id_conv' => $id_nueva_conv]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
}