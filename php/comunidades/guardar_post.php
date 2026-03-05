<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_usuario'])) {
    $id_comunidad = (int)$_POST['id_comunidad'];
    $id_usuario = $_SESSION['id_usuario'];
    $contenido = mysqli_real_escape_string($conexion, $_POST['contenido']);

    if (!empty($contenido)) {
        $sql = "INSERT INTO post (id_comunidad, id_usuario, contenido, fecha_publicacion) 
                VALUES ($id_comunidad, $id_usuario, '$contenido', NOW())";
        
        if (mysqli_query($conexion, $sql)) {
            // Regresa a la comunidad para ver el nuevo mensaje
            header("Location: ver_comunidad.php?id=$id_comunidad");
            exit;
        }
    }
}
// Si algo falla, redirigir a comunidades
header("Location: comunidades.php");
exit;