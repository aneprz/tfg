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
    $activo = (int)$_POST['activo']; 

    // Validar imagen
    if (!isset($_FILES["imagen"]) || $_FILES["imagen"]["error"] !== 0) {
        die("Error con la imagen");
    }

    // Nombre seguro
    $nombreArchivo = uniqid() . '_' . basename($_FILES["imagen"]["name"]);

    // Ruta destino
    $ruta = __DIR__ . '/../../../../../media/' . $nombreArchivo;

    // Crear carpeta si no existe 
    if (!file_exists(dirname($ruta))) {
        mkdir(dirname($ruta), 0777, true);
    }

    // Mover archivo
    if (!move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta)) {
        die("Error al subir imagen");
    }

    // Insertar en BD
    $sql = "INSERT INTO Tienda_Items (nombre, descripcion, tipo, precio, rareza, imagen, activo)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error prepare: " . $conexion->error);
    }

    $stmt->bind_param("sssissi", $nombre, $descripcion, $tipo, $precio, $rareza, $nombreArchivo, $activo);

    if ($stmt->execute()) {
        header("Location: ../gestionTienda.php");
        exit();
    } else {
        echo "Error execute: " . $stmt->error;
    }
}