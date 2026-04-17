<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = $_SESSION['id_usuario'] ?? 0;
$id_conv = isset($_POST['id_conv']) ? (int)$_POST['id_conv'] : 0;
$nuevo_nombre = isset($_POST['nombre_grupo']) ? trim($_POST['nombre_grupo']) : '';

if ($id_yo <= 0 || $id_conv <= 0 || empty($nuevo_nombre)) {
    echo json_encode(['success' => false]);
    exit;
}

$nombre_escapado = mysqli_real_escape_string($conexion, $nuevo_nombre);
$sql = "UPDATE chat_conversacion SET nombre_grupo = '$nombre_escapado' WHERE id_conversacion = $id_conv";

echo json_encode(['success' => mysqli_query($conexion, $sql)]);
exit;
?>