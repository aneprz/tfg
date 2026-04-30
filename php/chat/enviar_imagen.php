<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = (int)$_SESSION['id_usuario'];
$id_conv = !empty($_POST['id_conversacion']) ? (int)$_POST['id_conversacion'] : null;
$id_receptor = !empty($_POST['id_receptor']) ? (int)$_POST['id_receptor'] : null;

$nueva_id = null;

// Si no hay conversación, crear una nueva (chat individual)
if (!$id_conv && $id_receptor) {
    mysqli_query($conexion, "INSERT INTO chat_conversacion (tipo) VALUES ('individual')");
    $id_conv = mysqli_insert_id($conexion);
    mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario, estado_solicitud) VALUES ($id_conv, $id_yo, 'aceptada')");
    mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario, estado_solicitud) VALUES ($id_conv, $id_receptor, 'aceptada')");
    $nueva_id = $id_conv;
}

if ($id_conv && isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
    $directorio = __DIR__ . '/../../img/chats/';
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }
    
    $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($extension, $extensiones_permitidas)) {
        echo json_encode(['success' => false, 'error' => 'Formato de imagen no permitido']);
        exit;
    }
    
    $nombre_archivo = 'chat_' . time() . '_' . uniqid() . '.' . $extension;
    $ruta_destino = $directorio . $nombre_archivo;
    
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
        $ruta_db = '../../img/chats/' . $nombre_archivo;
        
        // Insertar mensaje con la imagen
        $texto = '[IMAGEN] ' . $ruta_db;
        $texto_escapado = mysqli_real_escape_string($conexion, $texto);
        mysqli_query($conexion, "INSERT INTO chat_mensaje (id_conversacion, id_emisor, contenido) VALUES ($id_conv, $id_yo, '$texto_escapado')");
        
        // Actualizar lectura
        mysqli_query($conexion, "UPDATE chat_participante SET ultima_lectura = NOW() WHERE id_conversacion = $id_conv AND id_usuario = $id_yo");
        
        // Incrementar contador de no leídos
        mysqli_query($conexion, "UPDATE chat_participante SET mensajes_no_leidos = mensajes_no_leidos + 1 
                                 WHERE id_conversacion = $id_conv AND id_usuario != $id_yo");
        
        // Crear notificación (opcional, puedes reutilizar la de enviar_mensaje.php)
        echo json_encode(['success' => true, 'nueva_id_conversacion' => $nueva_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al subir la imagen']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No se recibió ninguna imagen']);
}
exit;
?>