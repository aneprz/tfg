<?php
session_start();
require '../../db/conexiones.php';

$id_sesion = $_SESSION['id_usuario'];
$id_objetivo = $_POST['id_objetivo'];
$accion = $_POST['accion'];

if ($accion == 'enviar') {
    $sql = "INSERT INTO Amigos (id_usuario, id_amigo, estado) VALUES (?, ?, 'pendiente')";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $id_sesion, $id_objetivo);
    $stmt->execute();
    $stmt->close();
} 
elseif ($accion == 'aceptar') {
    // 1. Actualizar estado de la amistad
    $sql = "UPDATE Amigos SET estado = 'aceptada' WHERE id_usuario = ? AND id_amigo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $id_objetivo, $id_sesion);
    $stmt->execute();
    $stmt->close();
    
    // 2. Verificar si ya existe una conversación
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
        // Crear conversación
        if ($conexion->query("INSERT INTO chat_conversacion (tipo) VALUES ('individual')")) {
            $id_conv = $conexion->insert_id;
            
            $stmt1 = $conexion->prepare("INSERT INTO chat_participante (id_conversacion, id_usuario) VALUES (?, ?)");
            $stmt1->bind_param("ii", $id_conv, $id_sesion);
            $stmt1->execute();
            
            $stmt2 = $conexion->prepare("INSERT INTO chat_participante (id_conversacion, id_usuario) VALUES (?, ?)");
            $stmt2->bind_param("ii", $id_conv, $id_objetivo);
            $stmt2->execute();
            
            // DEPURACIÓN: Crear archivo para ver si llegó aquí
            file_put_contents(__DIR__ . '/debug_conv.txt', "Conversación creada ID: $id_conv para usuarios $id_sesion y $id_objetivo\n", FILE_APPEND);
        } else {
            file_put_contents(__DIR__ . '/debug_conv.txt', "ERROR al crear conversación: " . $conexion->error . "\n", FILE_APPEND);
        }
    } else {
        file_put_contents(__DIR__ . '/debug_conv.txt', "Conversación ya existía\n", FILE_APPEND);
    }
    $checkConv->close();
} 
elseif ($accion == 'borrar') {
    $sql = "DELETE FROM Amigos WHERE (id_usuario = ? AND id_amigo = ?) OR (id_usuario = ? AND id_amigo = ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiii", $id_sesion, $id_objetivo, $id_objetivo, $id_sesion);
    $stmt->execute();
    $stmt->close();
}

header("Location: perfilOtros.php?id=" . $id_objetivo);
exit;
?>