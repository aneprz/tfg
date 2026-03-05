<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (isset($_SESSION['id_usuario']) && isset($_GET['id'])) {
    $miId = $_SESSION['id_usuario'];
    $idAmigo = (int)$_GET['id'];
    $comunidadRef = (int)$_GET['ref'];

    // Insertar en la tabla Amigos (según tu base de datos)
    // Usamos INSERT IGNORE para evitar duplicados si le dan dos veces
    $sql = "INSERT IGNORE INTO Amigos (id_usuario, id_amigo) VALUES ($miId, $idAmigo)";
    
    if (mysqli_query($conexion, $sql)) {
        header("Location: ver_comunidad.php?id=" . $comunidadRef);
    } else {
        echo "Error al agregar amigo.";
    }
} else {
    header("Location: ../../index.php");
}