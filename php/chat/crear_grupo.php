<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

header('Content-Type: application/json');

$id_yo = (int)$_SESSION['id_usuario'];
$nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
// Recibimos los usuarios y los limpiamos
$usuarios = isset($_POST['usuarios']) ? json_decode($_POST['usuarios']) : [];

if (empty($nombre) || empty($usuarios)) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// 1. Crear Conversación
$sqlC = "INSERT INTO chat_conversacion (tipo, nombre_grupo) VALUES ('grupal', '$nombre')";
if(mysqli_query($conexion, $sqlC)){
    $id_conv = mysqli_insert_id($conexion);

    // 2. Añadirte a ti
    mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario, estado_solicitud) VALUES ($id_conv, $id_yo, 'aceptada')");

    // 3. Añadir a los elegidos
    foreach ($usuarios as $u_id) {
        $u_id = (int)$u_id;
        mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario, estado_solicitud) VALUES ($id_conv, $u_id, 'aceptada')");
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
}