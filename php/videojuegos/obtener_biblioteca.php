<?php
session_start();
header('Content-Type: application/json');
require_once '../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No has iniciado sesión']);
    exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];

$sql = "SELECT b.id_videojuego, v.titulo, v.portada, b.estado, r.puntuacion 
        FROM Biblioteca b
        JOIN Videojuego v ON b.id_videojuego = v.id_videojuego
        LEFT JOIN Resena r ON r.id_usuario = b.id_usuario AND r.id_videojuego = b.id_videojuego
        WHERE b.id_usuario = ?
        ORDER BY b.estado ASC, v.titulo ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$biblioteca = [];
while ($row = $result->fetch_assoc()) {
    $biblioteca[] = $row;
}

echo json_encode($biblioteca);
?>