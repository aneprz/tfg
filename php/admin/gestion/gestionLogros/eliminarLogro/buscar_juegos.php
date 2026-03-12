<?php
require_once __DIR__ . '/../../../../../db/conexiones.php';
$search = "%" . ($_GET['q'] ?? '') . "%";
$stmt = $conexion->prepare("SELECT id_videojuego, titulo FROM videojuego WHERE titulo LIKE ? LIMIT 20");
$stmt->bind_param("s", $search);
$stmt->execute();
$res = $stmt->get_result();
$data = [['id' => '', 'text' => 'Todos los juegos']];
while ($row = $res->fetch_assoc()) {
    $data[] = ['id' => $row['id_videojuego'], 'text' => $row['titulo']];
}
echo json_encode(['results' => $data]);
?>