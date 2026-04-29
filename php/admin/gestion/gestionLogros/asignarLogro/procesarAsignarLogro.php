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
    $id_admin = $_SESSION['id_usuario'];

    $check = $conexion->prepare("SELECT id_usuario FROM Logros_Usuario WHERE id_usuario = ? AND id_logro = ?");
    $check->bind_param("ii", $id_usuario, $id_logro);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        echo "<script>alert('Este jugador ya tiene este logro.'); window.history.back();</script>";
    } else {
        $insert = $conexion->prepare("INSERT INTO Logros_Usuario (id_usuario, id_logro) VALUES (?, ?)");
        $insert->bind_param("ii", $id_usuario, $id_logro);
        
        if ($insert->execute()) {
            // ========== CREAR NOTIFICACIÓN PARA EL USUARIO ==========
            // Obtener nombre del logro
            $stmtLogro = $conexion->prepare("SELECT nombre_logro FROM Logros WHERE id_logro = ?");
            $stmtLogro->bind_param("i", $id_logro);
            $stmtLogro->execute();
            $logro = $stmtLogro->get_result()->fetch_assoc();
            $nombreLogro = $logro['nombre_logro'];
            $stmtLogro->close();
            
            // Obtener nombre del administrador
            $stmtAdmin = $conexion->prepare("SELECT gameTag FROM Usuario WHERE id_usuario = ?");
            $stmtAdmin->bind_param("i", $id_admin);
            $stmtAdmin->execute();
            $admin = $stmtAdmin->get_result()->fetch_assoc();
            $nombreAdmin = $admin['gameTag'];
            $stmtAdmin->close();
            
            // Crear la notificación
            $mensaje = "👑 " . $nombreAdmin . " te ha asignado el logro: " . $nombreLogro;
            $url = "/php/user/perfiles/perfilSesion.php";
            
            $stmtNotif = $conexion->prepare("INSERT INTO Notificacion (id_usuario_destino, mensaje, url_destino, leida, tipo) VALUES (?, ?, ?, 0, 'usuario')");
            $stmtNotif->bind_param("iss", $id_usuario, $mensaje, $url);
            $stmtNotif->execute();
            $stmtNotif->close();
            
            echo "<script> window.location.href='../gestionLogros.php';</script>";
        } else {
            echo "Error al asignar: " . $insert->error;
        }
        $insert->close();
    }
    $check->close();
}
?>