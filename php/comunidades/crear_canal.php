<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$id_comunidad = (int)$_POST['id_comunidad'];
$nombre_canal = trim($_POST['nombre']);

if (empty($nombre_canal)) {
    echo json_encode(['success' => false, 'error' => 'El nombre no puede estar vacío']);
    exit;
}

$sql = "INSERT INTO canal (id_comunidad, nombre_canal, tipo) VALUES (?, ?, 'texto')";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("is", $id_comunidad, $nombre_canal);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id_canal' => $stmt->insert_id, 'nombre' => $nombre_canal]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>