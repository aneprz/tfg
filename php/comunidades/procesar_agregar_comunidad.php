<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_usuario'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $id_videojuego = (int)$_POST['id_videojuego'];
    $id_creador = (int)$_SESSION['id_usuario'];
    $nombreArchivo = "";

    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0) {
        $rutaMedia = __DIR__ . '/../../media/';
        $extension = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
        $nombreArchivo = "banner_com_" . time() . "_" . uniqid() . "." . $extension;
        move_uploaded_file($_FILES['banner']['tmp_name'], $rutaMedia . $nombreArchivo);
    }

    $stmt = $conexion->prepare("INSERT INTO Comunidad (nombre, id_videojuego_principal, id_creador, banner_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $nombre, $id_videojuego, $id_creador, $nombreArchivo);

    if ($stmt->execute()) {
        $id_nueva_com = $stmt->insert_id;

        // 1. Unir al creador automáticamente
        $conexion->query("INSERT INTO Miembro_Comunidad (id_comunidad, id_usuario, rol) VALUES ($id_nueva_com, $id_creador, 'Admin')");

        // 2. ENVIAR NOTIFICACIÓN (Esto es lo nuevo)
        $mensaje = "🚀 ¡Comunidad creada! Bienvenido a $nombre.";
        $url = "/php/comunidades/ver_comunidad.php?id=" . $id_nueva_com;
        $insNotif = $conexion->prepare("INSERT INTO Notificacion (mensaje, url_destino, tipo, id_usuario_destino) VALUES (?, ?, 'comunidad', ?)");
        $insNotif->bind_param("ssi", $mensaje, $url, $id_creador);
        $insNotif->execute();

        header("Location: comunidades.php?mensaje=creada");
        exit();
    }
}