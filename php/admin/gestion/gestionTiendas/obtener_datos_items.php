<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../../db/conexiones.php';

// Parámetros seguros
$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
$draw = isset($_GET['draw']) ? (int)$_GET['draw'] : 0;

// Search seguro
$searchValue = '';
if (isset($_GET['search']) && isset($_GET['search']['value'])) {
    $searchValue = $_GET['search']['value'];
}
$search = "%$searchValue%";

// Query datos
$sql = "SELECT id_item, nombre 
        FROM Tienda_Items
        WHERE nombre LIKE ?
        LIMIT ?, ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("sii", $search, $start, $length);
$stmt->execute();

$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Totales
$total = $conexion->query("SELECT COUNT(*) as total FROM Tienda_Items")->fetch_assoc()['total'];

$stmt2 = $conexion->prepare("SELECT COUNT(*) as total FROM Tienda_Items WHERE nombre LIKE ?");
$stmt2->bind_param("s", $search);
$stmt2->execute();
$count = $stmt2->get_result()->fetch_assoc()['total'];

// JSON limpio
header('Content-Type: application/json');

echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $total,
    "recordsFiltered" => $count,
    "data" => $data
]);