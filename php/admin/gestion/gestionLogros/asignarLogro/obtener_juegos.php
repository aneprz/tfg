<?php
require_once __DIR__ . '/../../../../../db/conexiones.php';
$busqueda = "%" . ($_GET['q'] ?? '') . "%";
$stmt = $conexion->prepare("SELECT id_videojuego, titulo FROM videojuego WHERE titulo LIKE ? ORDER BY titulo ASC LIMIT 20");
$stmt->bind_param("s", $busqueda);
$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = ['id' => $row['id_videojuego'], 'text' => $row['titulo']];
}
echo json_encode(['results' => $data]);