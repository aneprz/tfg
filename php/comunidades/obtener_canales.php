<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_comunidad = (int)$_GET['id_comunidad'];

$sql = "SELECT id_canal, nombre_canal, tipo FROM canal WHERE id_comunidad = ? ORDER BY id_canal ASC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_comunidad);
$stmt->execute();
$res = $stmt->get_result();

$canales = [];
while ($row = $res->fetch_assoc()) {
    $canales[] = $row;
}

echo json_encode($canales);
?>