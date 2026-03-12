<?php
require_once __DIR__ . '/../../../../../db/conexiones.php';
$start = (int)$_GET['start'];
$length = (int)$_GET['length'];
$search = "%" . ($_GET['search']['value'] ?? '') . "%";
$id_juego = $_GET['id_juego'] ?? '';

$where = " WHERE nombre_logro LIKE ?";
$params = [$search];
$types = "s";

if (!empty($id_juego)) {
    $where .= " AND id_videojuego = ?";
    $params[] = (int)$id_juego;
    $types .= "i";
}

$sql = "SELECT id_logro, nombre_logro FROM logros $where LIMIT ?, ?";
$params[] = $start;
$params[] = $length;
$types .= "ii";

$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = $conexion->query("SELECT COUNT(*) as total FROM logros")->fetch_assoc()['total'];
$filtered = $conexion->prepare("SELECT COUNT(*) as total FROM logros $where");
$filtered->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
$filtered->execute();
$count = $filtered->get_result()->fetch_assoc()['total'];

echo json_encode(["draw" => (int)$_GET['draw'], "recordsTotal" => $total, "recordsFiltered" => $count, "data" => $data]);