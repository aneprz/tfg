<?php
require_once __DIR__ . '/../../../../../db/conexiones.php';
$start = (int)$_GET['start'];
$length = (int)$_GET['length'];
$search = "%" . ($_GET['search']['value'] ?? '') . "%";

$sql = "SELECT id_videojuego, titulo FROM Videojuego WHERE titulo LIKE ? LIMIT ?, ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sii", $search, $start, $length);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = $conexion->query("SELECT COUNT(*) as total FROM Videojuego")->fetch_assoc()['total'];
$filtered = $conexion->prepare("SELECT COUNT(*) as total FROM Videojuego WHERE titulo LIKE ?");
$filtered->bind_param("s", $search);
$filtered->execute();
$count = $filtered->get_result()->fetch_assoc()['total'];

echo json_encode(["draw" => (int)$_GET['draw'], "recordsTotal" => $total, "recordsFiltered" => $count, "data" => $data]);
?>