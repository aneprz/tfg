<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$miId = (int)$_SESSION['id_usuario'];
$idAmigo = (int)$_GET['id'];

// Insertamos con estado 'pendiente' por defecto
$sql = "INSERT IGNORE INTO amigos (id_usuario, id_amigo, estado) VALUES ($miId, $idAmigo, 'pendiente')";

if (mysqli_query($conexion, $sql)) {
    if (mysqli_affected_rows($conexion) > 0) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Ya existe una solicitud"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
}