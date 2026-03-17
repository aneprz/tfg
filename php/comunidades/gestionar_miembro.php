<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id_comunidad'])) {
    exit("error");
}

$id_user = $_SESSION['id_usuario'];
$id_com = (int)$_GET['id_comunidad'];
$accion = $_GET['accion'];

if ($accion === 'unirse') {
    $sql = "INSERT IGNORE INTO Miembro_Comunidad (id_comunidad, id_usuario, rol) VALUES ($id_com, $id_user, 'Miembro')";
    $res = mysqli_query($conexion, $sql);

    if ($res && mysqli_affected_rows($conexion) > 0) {
        // --- BLOQUE DE NOTIFICACIÓN NUEVO ---
        $info = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT id_creador, nombre FROM Comunidad WHERE id_comunidad = $id_com"));
        $id_dueno = $info['id_creador'];
        $nombre_com = $info['nombre'];

        if ($id_user != $id_dueno) { // No notificarse a uno mismo
            $tag = $_SESSION['tag'];
            $msj = "👥 @$tag se ha unido a tu comunidad: $nombre_com";
            $url = "/php/comunidades/ver_comunidad.php?id=$id_com";
            $conexion->query("INSERT INTO Notificacion (mensaje, url_destino, tipo, id_usuario_destino) VALUES ('$msj', '$url', 'comunidad', $id_dueno)");
        }
        // ------------------------------------
    }
} else {
    $sql = "DELETE FROM Miembro_Comunidad WHERE id_comunidad = $id_com AND id_usuario = $id_user";
    $res = mysqli_query($conexion, $sql);
}

echo $res ? "success" : "error";