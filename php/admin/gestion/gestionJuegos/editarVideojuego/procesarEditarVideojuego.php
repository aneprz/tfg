<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $titulo = $_POST['nombre'];
    $dev = $_POST['developer'];
    $desc = $_POST['descripcion'];
    $gen = $_POST['genero'];
    $plat = $_POST['plataforma'];
    $fecha = $_POST['fecha'];

    if (!empty($_FILES["portada"]["name"])) {
        $res = $conexion->query("SELECT portada FROM Videojuego WHERE id_videojuego = $id");
        $antigua = $res->fetch_assoc()['portada'];
        if ($antigua !== 'logoPlatino.png' && file_exists("../../../../../media/" . $antigua)) {
            unlink("../../../../../media/" . $antigua);
        }

        $nombreNuevo = time() . '_' . basename($_FILES["portada"]["name"]);
        move_uploaded_file($_FILES["portada"]["tmp_name"], "../../../../../media/" . $nombreNuevo);
        
        $sql = "UPDATE Videojuego SET titulo=?, developer=?, descripcion=?, genero=?, plataforma=?, fecha_lanzamiento=?, portada=? WHERE id_videojuego=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssssi", $titulo, $dev, $desc, $gen, $plat, $fecha, $nombreNuevo, $id);
    } else {
        $sql = "UPDATE Videojuego SET titulo=?, developer=?, descripcion=?, genero=?, plataforma=?, fecha_lanzamiento=? WHERE id_videojuego=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssssi", $titulo, $dev, $desc, $gen, $plat, $fecha, $id);
    }

    if ($stmt->execute()) {
        echo "<script> window.location.href='listaEditarVideojuego.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar'); window.history.back();</script>";
    }
    $stmt->close();
}
?>