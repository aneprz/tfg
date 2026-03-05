<?php
session_start();
header('Content-Type: application/json'); // Indicamos que devolvemos JSON
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$miId = $_SESSION['id_usuario'];
$idAmigo = (int)$_GET['id'];

// Insertamos en la tabla Amigos (según tu esquema)
$sql = "INSERT IGNORE INTO Amigos (id_usuario, id_amigo) VALUES ($miId, $idAmigo)";

if (mysqli_query($conexion, $sql)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
}
?>