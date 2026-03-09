<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    exit("Acceso denegado");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $gameTag = $_POST['gameTag'];
    $nombre_apellido = $_POST['nombre_apellido'];
    $email = $_POST['email'];
    $biografia = $_POST['biografia'];

    if (!empty($_FILES["avatar"]["name"])) {
        $res = $conexion->query("SELECT avatar FROM Usuario WHERE id_usuario = $id");
        $antiguo = $res->fetch_assoc()['avatar'];
        
        if (!empty($antiguo) && $antiguo !== 'logoPlatino.png' && file_exists("../../../../../media/" . $antiguo)) {
            unlink("../../../../../media/" . $antiguo);
        }

        $nombreNuevo = time() . '_user_' . basename($_FILES["avatar"]["name"]);
        move_uploaded_file($_FILES["avatar"]["tmp_name"], "../../../../../media/" . $nombreNuevo);
        
        $sql = "UPDATE Usuario SET gameTag=?, nombre_apellido=?, email=?, biografia=?, avatar=? WHERE id_usuario=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssi", $gameTag, $nombre_apellido, $email, $biografia, $nombreNuevo, $id);
    } else {
        $sql = "UPDATE Usuario SET gameTag=?, nombre_apellido=?, email=?, biografia=? WHERE id_usuario=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssi", $gameTag, $nombre_apellido, $email, $biografia, $id);
    }

    if ($stmt->execute()) {
        if ($_SESSION['id_usuario'] == $id) {
            $_SESSION['tag'] = $gameTag;
        }
        echo "<script> window.location.href='listaEditarJugadores.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar: El GameTag o Email ya podrían estar en uso'); window.history.back();</script>";
    }
    $stmt->close();
}
$conexion->close();
?>