<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

// Limpiamos cualquier salida inesperada
if (ob_get_length()) ob_clean();
header('Content-Type: application/json');

$response = ["status" => "error"];

if (isset($_SESSION['id_usuario']) && isset($_GET['id'])) {
    $miId = (int)$_SESSION['id_usuario'];
    $idAmigo = (int)$_GET['id'];
    $accion = $_GET['accion'] ?? 'agregar';

    if ($accion === 'agregar') {
        // Insertar solicitud
        $sql = "INSERT IGNORE INTO Amigos (id_usuario, id_amigo, estado) VALUES ($miId, $idAmigo, 'pendiente')";
        if (mysqli_query($conexion, $sql)) {
            $response = ["status" => "success", "nuevo_estado" => "pendiente"];
        }
    } else {
        // Cancelar solicitud
        $sql = "DELETE FROM Amigos WHERE id_usuario = $miId AND id_amigo = $idAmigo AND estado = 'pendiente'";
        if (mysqli_query($conexion, $sql)) {
            $response = ["status" => "success", "nuevo_estado" => "agregar"];
        }
    }
}

echo json_encode($response);
exit;