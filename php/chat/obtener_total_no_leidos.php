<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = $_SESSION['id_usuario'] ?? 0;

if ($id_yo <= 0) {
    echo json_encode(['total' => 0]);
    exit;
}

$sql = "SELECT SUM(mensajes_no_leidos) as total FROM chat_participante WHERE id_usuario = $id_yo";
$res = mysqli_query($conexion, $sql);
$row = mysqli_fetch_assoc($res);

echo json_encode(['total' => (int)$row['total']]);
?>