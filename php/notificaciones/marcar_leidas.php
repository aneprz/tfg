<?php
session_start();
require '../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false]);
    exit();
}

$id_sesion = $_SESSION['id_usuario'];
$sql = "UPDATE Notificacion SET leida = 1 WHERE id_usuario_destino = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_sesion);

echo json_encode(['success' => $stmt->execute()]);