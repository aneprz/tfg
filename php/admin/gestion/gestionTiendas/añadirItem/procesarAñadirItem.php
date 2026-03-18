<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $tipo = $_POST['tipo'];
    $precio = (int)$_POST['precio'];
    $rareza = $_POST['rareza'];

    echo "OK POST<br>";

    // Imagen
    if (!isset($_FILES["imagen"])) {
        die("No llega imagen");
    }

    $nombreArchivo = time() . '_' . basename($_FILES["imagen"]["name"]);
    $ruta = __DIR__ . '/../../../../../media/' . $nombreArchivo;

    echo "Ruta: $ruta<br>";

    if (!move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta)) {
        die("Error al subir imagen");
    }

    $sql = "INSERT INTO Tienda_Items (nombre, descripcion, tipo, precio, rareza, imagen)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error prepare: " . $conexion->error);
    }

    $stmt->bind_param("sssiss", $nombre, $descripcion, $tipo, $precio, $rareza, $nombreArchivo);

    if ($stmt->execute()) {

        // IMPORTANTE: limpiar buffer antes de redirigir
        header("Location: ../gestionTienda.php");
        exit();

    } else {
        echo "Error execute: " . $stmt->error;
    }

}