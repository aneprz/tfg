<?php
require_once '../../db/conexiones.php';
header('Content-Type: application/json');

if (!isset($_GET['id_caja'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'ID no proporcionado']);
    exit;
}

$idCaja = (int) $_GET['id_caja'];

$stmt = $conexion->prepare("SELECT tipo_premio, nombre_premio, imagen_premio, puntos_premio, probabilidad FROM Recompensa_Caja WHERE id_caja = ? ORDER BY probabilidad ASC");
$stmt->bind_param("i", $idCaja);
$stmt->execute();
$premios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode(['status' => 'success', 'premios' => $premios]);