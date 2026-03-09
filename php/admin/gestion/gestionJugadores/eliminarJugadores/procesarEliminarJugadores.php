<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("SELECT avatar FROM Usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $jugador = $resultado->fetch_assoc();
    $stmt->close();

    if ($jugador) {
        $foto_a_borrar = $jugador['avatar'];
        $ruta_media = __DIR__ . '/../../../../../media/';

        if (!empty($foto_a_borrar) && 
            $foto_a_borrar !== 'perfil_default.jpg' && 
            file_exists($ruta_media . $foto_a_borrar)) {
            
            unlink($ruta_media . $foto_a_borrar);
        }

        $stmtDelete = $conexion->prepare("DELETE FROM Usuario WHERE id_usuario = ?");
        $stmtDelete->bind_param("i", $id);

        if ($stmtDelete->execute()) {
            echo "<script> window.location.href='eliminarJugadores.php';</script>";
        } else {
            echo "Error al eliminar de la base de datos: " . $stmtDelete->error;
        }
        $stmtDelete->close();
    }
}
$conexion->close();
?>