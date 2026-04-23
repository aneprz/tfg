<?php
session_start();
require '../../../db/conexiones.php'; 

if (!isset($_SESSION['id_usuario']) || !isset($_POST['id_objetivo'])) {
    header("Location: jugadores.php");
    exit();
}

$id_sesion = $_SESSION['id_usuario'];
$id_objetivo = $_POST['id_objetivo'];
$accion = $_POST['accion'];

// Nombre de quien realiza la acción
$nombre_usuario = isset($_SESSION['gameTag']) ? $_SESSION['gameTag'] : (isset($_SESSION['tag']) ? $_SESSION['tag'] : 'Un Usuario');

if ($accion == 'enviar') {
    // 1. Insertar la amistad pendiente
    $sql = "INSERT INTO Amigos (id_usuario, id_amigo, estado) VALUES (?, ?, 'pendiente')";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $id_sesion, $id_objetivo);
    $stmt->execute();

    // 2. Notificación para el que RECIBE la solicitud (id_objetivo)
    $mensajeNotif = "👋 @$nombre_usuario te ha enviado una solicitud de amistad.";
    $urlNotif = "/php/user/amistades/perfilOtros.php?id=" . $id_sesion; 
    
    $insNotif = $conexion->prepare("INSERT INTO Notificacion (mensaje, url_destino, tipo, id_usuario_destino) VALUES (?, ?, 'Usuario', ?)");
    $insNotif->bind_param("ssi", $mensajeNotif, $urlNotif, $id_objetivo);
    $insNotif->execute();

} 
elseif ($accion == 'aceptar') {
     // 1. Actualizar estado a aceptada
    $sql = "UPDATE Amigos SET estado = 'aceptada' WHERE id_usuario = ? AND id_amigo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $id_objetivo, $id_sesion);
    $stmt->execute();
    $stmt->close();

    // ========== CREAR CONVERSACIÓN INDIVIDUAL ==========
    // Verificar si ya existe una conversación entre estos dos usuarios
    $checkConv = $conexion->prepare("
        SELECT c.id_conversacion 
        FROM chat_conversacion c
        JOIN chat_participante p1 ON p1.id_conversacion = c.id_conversacion
        JOIN chat_participante p2 ON p2.id_conversacion = c.id_conversacion
        WHERE c.tipo = 'individual' 
        AND ((p1.id_usuario = ? AND p2.id_usuario = ?) OR (p1.id_usuario = ? AND p2.id_usuario = ?))
    ");
    $checkConv->bind_param("iiii", $id_sesion, $id_objetivo, $id_objetivo, $id_sesion);
    $checkConv->execute();
    $result = $checkConv->get_result();
    
    if ($result->num_rows == 0) {
        // Crear conversación individual
        $conexion->query("INSERT INTO chat_conversacion (tipo) VALUES ('individual')");
        $id_conv = $conexion->insert_id;
        
        // Añadir participantes
        $stmt1 = $conexion->prepare("INSERT INTO chat_participante (id_conversacion, id_usuario) VALUES (?, ?)");
        $stmt1->bind_param("ii", $id_conv, $id_sesion);
        $stmt1->execute();
        
        $stmt2 = $conexion->prepare("INSERT INTO chat_participante (id_conversacion, id_usuario) VALUES (?, ?)");
        $stmt2->bind_param("ii", $id_conv, $id_objetivo);
        $stmt2->execute();
        
        $stmt1->close();
        $stmt2->close();
    }
    $checkConv->close();

    // 2. BORRAR la notificación de "te ha enviado solicitud"
    $delNotif = $conexion->prepare("DELETE FROM Notificacion WHERE id_usuario_destino = ? AND mensaje LIKE '%solicitud de amistad%'");
    $delNotif->bind_param("i", $id_sesion);
    $delNotif->execute();

    // 3. Notificación para el que ENVIÓ originalmente
    $mensajeNotif = "✅ @$nombre_usuario ha aceptado tu solicitud de amistad.";
    $urlNotif = "/php/user/perfiles/mis_amigos.php"; 
    
    $insNotif = $conexion->prepare("INSERT INTO Notificacion (mensaje, url_destino, tipo, id_usuario_destino) VALUES (?, ?, 'Usuario', ?)");
    $insNotif->bind_param("ssi", $mensajeNotif, $urlNotif, $id_objetivo);
    $insNotif->execute();
} 
elseif ($accion == 'cancelar' || $accion == 'eliminar') {
    $sql = "DELETE FROM Amigos WHERE (id_usuario = ? AND id_amigo = ?) OR (id_usuario = ? AND id_amigo = ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiii", $id_sesion, $id_objetivo, $id_objetivo, $id_sesion);
    $stmt->execute();

    // Limpiar cualquier notificación de solicitud entre estos dos
    $delNotif = $conexion->prepare("DELETE FROM Notificacion WHERE (id_usuario_destino = ? OR id_usuario_destino = ?) AND mensaje LIKE '%solicitud de amistad%'");
    $delNotif->bind_param("ii", $id_objetivo, $id_sesion);
    $delNotif->execute();
}

header("Location: perfilOtros.php?id=" . $id_objetivo);
exit();
?>