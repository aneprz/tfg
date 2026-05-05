<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';
require_once __DIR__ . '/../../../../user/perfiles/UserProgressService.php';

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
        mysqli_begin_transaction($conexion);

        try {
            $insert = $conexion->prepare("INSERT INTO Logros_Usuario (id_usuario, id_logro) VALUES (?, ?)");

            if (!$insert) {
                throw new RuntimeException("No se pudo preparar la asignacion del logro.");
            }

            $insert->bind_param("ii", $id_usuario, $id_logro);

            if (!$insert->execute()) {
                $error = $insert->error;
                $insert->close();
                throw new RuntimeException($error !== '' ? $error : "Error al asignar el logro.");
            }

            $insert->close();

            $stmtLogro = $conexion->prepare("SELECT nombre_logro, puntos_logro FROM Logros WHERE id_logro = ?");
            $stmtLogro->bind_param("i", $id_logro);
            $stmtLogro->execute();
            $logro = $stmtLogro->get_result()->fetch_assoc();
            $stmtLogro->close();

            $nombreLogro = $logro['nombre_logro'] ?? 'Logro';
            $puntosLogro = (int) ($logro['puntos_logro'] ?? 0);

            if ($puntosLogro !== 0) {
                UserProgressService::applyPointDelta($conexion, $id_usuario, $puntosLogro);
                UserProgressService::registerPointMovement(
                    $conexion,
                    $id_usuario,
                    $puntosLogro,
                    'admin',
                    'Logro asignado por admin: ' . $nombreLogro
                );
            }

            $stmtAdmin = $conexion->prepare("SELECT gameTag FROM Usuario WHERE id_usuario = ?");
            $stmtAdmin->bind_param("i", $id_admin);
            $stmtAdmin->execute();
            $admin = $stmtAdmin->get_result()->fetch_assoc();
            $nombreAdmin = $admin['gameTag'] ?? 'Un administrador';
            $stmtAdmin->close();

            $mensaje = "👑 " . $nombreAdmin . " te ha asignado el logro: " . $nombreLogro;
            $url = "/php/user/perfiles/perfilSesion.php";

            $stmtNotif = $conexion->prepare("INSERT INTO Notificacion (id_usuario_destino, mensaje, url_destino, leida, tipo) VALUES (?, ?, ?, 0, 'usuario')");
            $stmtNotif->bind_param("iss", $id_usuario, $mensaje, $url);
            $stmtNotif->execute();
            $stmtNotif->close();

            mysqli_commit($conexion);
            echo "<script> window.location.href='../gestionLogros.php';</script>";
        } catch (Throwable $e) {
            mysqli_rollback($conexion);
            echo "Error al asignar: " . $e->getMessage();
        }
    }
    $check->close();
}
?>
