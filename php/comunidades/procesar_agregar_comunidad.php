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
        $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));

        if (in_array($extension, $permitidos)) {
            $nombreArchivo = "banner_com_" . time() . "_" . uniqid() . "." . $extension;
            if (!move_uploaded_file($_FILES['banner']['tmp_name'], $rutaMedia . $nombreArchivo)) {
                die("Error al mover el archivo.");
            }
        } else {
            die("Formato no permitido.");
        }
    }

    $stmt = $conexion->prepare("INSERT INTO Comunidad (nombre, id_videojuego_principal, id_creador, banner_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $nombre, $id_videojuego, $id_creador, $nombreArchivo);

    if ($stmt->execute()) {
        header("Location: comunidades.php?mensaje=creada");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    header("Location: ../../index.php");
    exit();
}
?>