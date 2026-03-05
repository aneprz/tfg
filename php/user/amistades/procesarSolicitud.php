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
} 
elseif ($accion == 'aceptar') {
    $sql = "UPDATE Amigos SET estado = 'aceptada' WHERE id_usuario = ? AND id_amigo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $id_objetivo, $id_sesion);
} 
elseif ($accion == 'borrar') {
    $sql = "DELETE FROM Amigos WHERE (id_usuario = ? AND id_amigo = ?) OR (id_usuario = ? AND id_amigo = ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiii", $id_sesion, $id_objetivo, $id_objetivo, $id_sesion);
}

$stmt->execute();
header("Location: perfilOtros.php?id=" . $id_objetivo);