<?php
require_once __DIR__ . '/../../../../../db/conexiones.php';
$id_juego = (int)($_GET['id_juego'] ?? 0);
$stmt = $conexion->prepare("SELECT id_logro, nombre_logro FROM Logros WHERE id_videojuego = ? ORDER BY nombre_logro ASC");
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = ['id' => $row['id_logro'], 'text' => $row['nombre_logro']];
}
echo json_encode($data);