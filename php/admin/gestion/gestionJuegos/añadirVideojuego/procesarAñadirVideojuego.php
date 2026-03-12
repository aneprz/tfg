<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['nombre'];
    $developer = $_POST['developer'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha_lanzamiento'];
    $genero = $_POST['genero'];
    $plataforma = $_POST['plataforma'];
    $rating = 0.00;

    // $nombreArchivo = time() . '_' . basename($_FILES["portada"]["name"]);
    // $directorioDestino = "../../../../../media/";
    // $rutaFinal = $directorioDestino . $nombreArchivo;

    //!! ACABO DE CAMBIAR - NAHIA
    $nombreArchivo = time() . '_' . basename($_FILES["portada"]["name"]);

    $directorioDestino = realpath(__DIR__ . "/../../../../../media");

    $rutaFinal = $directorioDestino . "/" . $nombreArchivo;

    echo "Ruta final: $rutaFinal";

    //!!!!!!!!!

    if (move_uploaded_file($_FILES["portada"]["tmp_name"], $rutaFinal)) {
        $sql = "INSERT INTO Videojuego (titulo, developer, descripcion, fecha_lanzamiento, portada, genero, plataforma, rating_medio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssssd", $titulo, $developer, $descripcion, $fecha, $nombreArchivo, $genero, $plataforma, $rating);

        if ($stmt->execute()) {
            echo "<script> window.location.href='../gestionJuegos.php';</script>";
        } else {
            echo "Error en base de datos: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error al subir la imagen.";
    }
    $conexion->close();
}
?>