<?php
require_once __DIR__ . '/../../../../../db/conexiones.php';
$start = (int)$_GET['start'];
$length = (int)$_GET['length'];
$search = "%" . ($_GET['search']['value'] ?? '') . "%";

$sql = "SELECT id_logro, nombre_logro FROM logros WHERE nombre_logro LIKE ? LIMIT ?, ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sii", $search, $start, $length);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = $conexion->query("SELECT COUNT(*) as total FROM logros")->fetch_assoc()['total'];

echo json_encode([
    "draw" => (int)$_GET['draw'],
    "recordsTotal" => $total,
    "recordsFiltered" => $total,
    "data" => $data
]);