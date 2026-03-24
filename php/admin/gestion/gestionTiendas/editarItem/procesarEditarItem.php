<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {

    $id = (int)$_POST['id'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $tipo = $_POST['tipo'];
    $precio = (int)$_POST['precio'];
    $rareza = $_POST['rareza'];
    $activo = (int)$_POST['activo'];

    if (!empty($_FILES["imagen"]["name"])) {

        $res = $conexion->query("SELECT imagen FROM Tienda_Items WHERE id_item = $id");
        $antigua = $res->fetch_assoc()['imagen'];

        if (!empty($antigua) && file_exists("../../../../../media/" . $antigua)) {
            unlink("../../../../../media/" . $antigua);
        }

        $nombreNuevo = time() . '_' . basename($_FILES["imagen"]["name"]);
        move_uploaded_file($_FILES["imagen"]["tmp_name"], "../../../../../media/" . $nombreNuevo);

        $sql = "UPDATE Tienda_Items 
                SET nombre=?, descripcion=?, tipo=?, precio=?, rareza=?, activo=?, imagen=? 
                WHERE id_item=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssisssi", $nombre, $descripcion, $tipo, $precio, $rareza, $activo, $nombreNuevo, $id);

    } else {

        $sql = "UPDATE Tienda_Items 
                SET nombre=?, descripcion=?, tipo=?, precio=?, rareza=?, activo=? 
                WHERE id_item=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssissi", $nombre, $descripcion, $tipo, $precio, $rareza, $activo, $id);
    }

    if ($stmt->execute()) {
        echo "<script> window.location.href='listaEditarItem.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar'); window.history.back();</script>";
    }

    $stmt->close();
}
?>