<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $id_comunidad = (int)$_POST['id_comunidad'];
    $contenido = mysqli_real_escape_string($conexion, $_POST['contenido']);

    $sql = "INSERT INTO post (id_usuario, id_comunidad, contenido) VALUES ($id_usuario, $id_comunidad, '$contenido')";
    mysqli_query($conexion, $sql);
    
    header("Location: ver_comunidad.php?id=" . $id_comunidad);
} else {
    echo "Error: Debes estar logueado.";
}
?>