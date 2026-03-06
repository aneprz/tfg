<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("SELECT portada FROM Videojuego WHERE id_videojuego = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $juego = $resultado->fetch_assoc();
    $stmt->close();

    if ($juego) {
        $nombrePortada = $juego['portada'];
        
        $rutaPortada = __DIR__ . '/../../../../../media/' . $nombrePortada;

        if (!empty($nombrePortada) && $nombrePortada !== 'logoPlatino.png' && file_exists($rutaPortada)) {
            unlink($rutaPortada);
        }

        $stmtDelete = $conexion->prepare("DELETE FROM Videojuego WHERE id_videojuego = ?");
        $stmtDelete->bind_param("i", $id);

        if ($stmtDelete->execute()) {
            echo "<script> window.location.href='eliminarVideojuego.php';</script>";
        } else {
            echo "Error al eliminar de la base de datos: " . $stmtDelete->error;
        }
        $stmtDelete->close();
    }
}
$conexion->close();
?>