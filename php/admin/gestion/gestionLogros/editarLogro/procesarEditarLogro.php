<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $titulo = $_POST['nombre'];
    $desc = $_POST['descripcion'];
    $puntos = (int)$_POST['puntos'];

    if (!empty($_FILES["portada"]["name"])) {
        $res = $conexion->prepare("SELECT imagen FROM logros WHERE id_logro = ?");
        $res->bind_param("i", $id);
        $res->execute();
        $antigua = $res->get_result()->fetch_assoc()['imagen'] ?? '';
        $res->close();

        if (!empty($antigua) && $antigua !== 'default_logro.jpg' && file_exists("../../../../../media/" . $antigua)) {
            unlink("../../../../../media/" . $antigua);
        }
        $nombreNuevo = time() . '_' . basename($_FILES["portada"]["name"]);
        move_uploaded_file($_FILES["portada"]["tmp_name"], "../../../../../media/" . $nombreNuevo);
        
        $sql = "UPDATE logros SET nombre_logro=?, descripcion=?, puntos_logro=?, imagen=? WHERE id_logro=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssisi", $titulo, $desc, $puntos, $nombreNuevo, $id);
    } else {
        $sql = "UPDATE logros SET nombre_logro=?, descripcion=?, puntos_logro=? WHERE id_logro=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssii", $titulo, $desc, $puntos, $id);
    }

    if ($stmt->execute()) {
        echo "<script>window.location.href='listaEditarLogro.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar: " . $stmt->error . "'); window.history.back();</script>";
    }
    $stmt->close();
}
$conexion->close();
?>