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
elseif ($accion == 'eliminar') { // Cambiado 'borrar' por 'eliminar' para que coincida con tu HTML
    $sql = "DELETE FROM Amigos WHERE (id_usuario = ? AND id_amigo = ?) OR (id_usuario = ? AND id_amigo = ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiii", $id_sesion, $id_objetivo, $id_objetivo, $id_sesion);
}

if (isset($stmt)) {
    $stmt->execute();
}

header("Location: perfilOtros.php?id=" . $id_objetivo);
exit();