<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = (int)$_POST['id_usuario'];
    $id_logro = (int)$_POST['id_logro'];

    $check = $conexion->prepare("SELECT id_usuario FROM Logros_Usuario WHERE id_usuario = ? AND id_logro = ?");
    $check->bind_param("ii", $id_usuario, $id_logro);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        echo "<script>alert('Este jugador ya tiene este logro.'); window.history.back();</script>";
    } else {
        $insert = $conexion->prepare("INSERT INTO Logros_Usuario (id_usuario, id_logro) VALUES (?, ?)");
        $insert->bind_param("ii", $id_usuario, $id_logro);
        
        if ($insert->execute()) {
            echo "<script> window.location.href='../gestionLogros.php';</script>";
        } else {
            echo "Error al asignar: " . $insert->error;
        }
        $insert->close();
    }
    $check->close();
}
?>