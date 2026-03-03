<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) exit("No autorizado");

$id = $_SESSION['id_usuario'];
$nombre = $_POST['nombreApellido'];
$bio = $_POST['biografia'];
$avatar = $_POST['avatar'];
$gameTag = $_POST['gameTag'];
$_SESSION['tag'] = $gameTag;

$stmt = $conexion->prepare("UPDATE Usuario SET gameTag = ?, nombre_apellido = ?, biografia = ?, avatar = ? WHERE id_usuario = ?");
$stmt->bind_param("ssssi", $gameTag, $nombre, $bio, $avatar, $id);

if ($stmt->execute()) {
    echo "<script> window.location.href='../perfiles/perfilSesion.php';</script>";
} else {
    echo "Error al actualizar";
}

$stmt->close();
$conexion->close();
?>