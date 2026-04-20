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
    // Insertar mensaje
    mysqli_query($conexion, "INSERT INTO chat_mensaje (id_conversacion, id_emisor, contenido) VALUES ($id_conv, $id_yo, '$texto')");
    
    // Actualizar mi propia lectura
    mysqli_query($conexion, "UPDATE chat_participante SET ultima_lectura = NOW() WHERE id_conversacion = $id_conv AND id_usuario = $id_yo");
    
    // INCREMENTAR CONTADOR DE NO LEÍDOS PARA LOS RECEPTORES
    mysqli_query($conexion, "UPDATE chat_participante SET mensajes_no_leidos = mensajes_no_leidos + 1 
                             WHERE id_conversacion = $id_conv AND id_usuario != $id_yo");
}

// ========== CREAR NOTIFICACIÓN ==========
$// ========== CREAR NOTIFICACIÓN ==========
$infoConv = mysqli_query($conexion, "SELECT tipo, nombre_grupo FROM chat_conversacion WHERE id_conversacion = $id_conv");
$conv = mysqli_fetch_assoc($infoConv);
$tipoConv = $conv['tipo'];

$resYo = mysqli_query($conexion, "SELECT gameTag FROM Usuario WHERE id_usuario = $id_yo");
$yo = mysqli_fetch_assoc($resYo);
$miNombre = $yo['gameTag'];

$urlDestino = "../../chat/bandeja.php?conv=$id_conv";
$destinatarios = [];

if ($tipoConv == 'individual') {
    // Obtener el receptor de la conversación (el que NO soy yo)
    $resReceptor = mysqli_query($conexion, "SELECT id_usuario FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario != $id_yo LIMIT 1");
    $receptor = mysqli_fetch_assoc($resReceptor);
    $id_receptor_db = $receptor['id_usuario'];
    
    $destinatarios = [$id_receptor_db];
    $mensajeNotif = "$miNombre te ha enviado un mensaje";
} else {
    // Grupal: todos los participantes excepto yo
    $resParticipantes = mysqli_query($conexion, "SELECT id_usuario FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario != $id_yo");
    while($p = mysqli_fetch_assoc($resParticipantes)) {
        $destinatarios[] = $p['id_usuario'];
    }
    $nombreGrupo = $conv['nombre_grupo'] ?? 'Grupo';
    $mensajeNotif = "$miNombre ha enviado un mensaje en $nombreGrupo";
}

$stmt = $conexion->prepare("INSERT INTO Notificacion (id_usuario_destino, mensaje, url_destino, leida, tipo) VALUES (?, ?, ?, 0, 'usuario')");
foreach ($destinatarios as $id_destino) {
    $stmt->bind_param("iss", $id_destino, $mensajeNotif, $urlDestino);
    $stmt->execute();
}
$stmt->close();

echo json_encode(['success' => true, 'nueva_id_conversacion' => $nueva_id]);
?>