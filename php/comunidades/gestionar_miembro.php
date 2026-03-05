<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id_comunidad'])) {
    header("Location: comunidades.php");
    exit;
}

$id_user = $_SESSION['id_usuario'];
$id_com = (int)$_GET['id_comunidad'];
$accion = $_GET['accion'];

if ($accion === 'unirse') {
    $sql = "INSERT IGNORE INTO Miembro_Comunidad (id_comunidad, id_usuario, rol) VALUES ($id_com, $id_user, 'Miembro')";
} else if ($accion === 'salir') {
    $sql = "DELETE FROM Miembro_Comunidad WHERE id_comunidad = $id_com AND id_usuario = $id_user";
}

mysqli_query($conexion, $sql);
header("Location: ver_comunidad.php?id=" . $id_com);