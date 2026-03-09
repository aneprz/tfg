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
    $jugador = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($jugador) {
        $foto_a_borrar = $jugador['avatar'];
        $ruta_media = __DIR__ . '/../../../../../media/';

        if (!empty($foto_a_borrar) && $foto_a_borrar !== 'perfil_default.jpg' && file_exists($ruta_media . $foto_a_borrar)) {
            unlink($ruta_media . $foto_a_borrar);
        }

        $conexion->begin_transaction();

        try {
            $stmtAmigos = $conexion->prepare("DELETE FROM Amigos WHERE id_usuario = ? OR id_amigo = ?");
            $stmtAmigos->bind_param("ii", $id, $id);
            $stmtAmigos->execute();
            $stmtAmigos->close();

            $stmtBiblioteca = $conexion->prepare("DELETE FROM Biblioteca WHERE id_usuario = ?");
            $stmtBiblioteca->bind_param("i", $id);
            $stmtBiblioteca->execute();
            $stmtBiblioteca->close();

            $stmtDelete = $conexion->prepare("DELETE FROM Usuario WHERE id_usuario = ?");
            $stmtDelete->bind_param("i", $id);
            $stmtDelete->execute();
            $stmtDelete->close();

            $conexion->commit();
            echo "<script>window.location.href='eliminarJugadores.php';</script>";
        } catch (Exception $e) {
            $conexion->rollback();
            echo "Error al eliminar: " . $e->getMessage();
        }
    }
}
$conexion->close();
?>