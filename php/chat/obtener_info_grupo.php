<?php
// Evitamos que cualquier error ensucie la respuesta
error_reporting(0); 
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = $_SESSION['id_usuario'] ?? 0;
$id_conv = isset($_GET['id_conv']) ? (int)$_GET['id_conv'] : 0;

$data = [
    'miembros' => [],
    'amigos_fuera' => [],
    'soy_creador' => false,
    'error' => null
];

if (!$conexion) {
    echo json_encode(['error' => 'Error de conexión a la DB']);
    exit;
}

// 1. Info del creador
$resC = mysqli_query($conexion, "SELECT id_usuario_creador FROM chat_conversacion WHERE id_conversacion = $id_conv");
if ($resC) {
    $infoC = mysqli_fetch_assoc($resC);
    $id_creador = (int)($infoC['id_usuario_creador'] ?? 0);
    $data['soy_creador'] = ($id_yo === $id_creador);

    // 2. Miembros
    $resM = mysqli_query($conexion, "SELECT u.id_usuario, u.gameTag FROM chat_participante p JOIN usuario u ON p.id_usuario = u.id_usuario WHERE p.id_conversacion = $id_conv");
    while($row = mysqli_fetch_assoc($resM)) {
        $row['es_creador'] = ((int)$row['id_usuario'] === $id_creador);
        $data['miembros'][] = $row;
    }

    // 3. Amigos fuera
    $sqlA = "SELECT u.id_usuario, u.gameTag FROM amigos a 
             JOIN usuario u ON (CASE WHEN a.id_usuario = $id_yo THEN a.id_amigo = u.id_usuario ELSE a.id_usuario = u.id_usuario END)
             WHERE (a.id_usuario = $id_yo OR a.id_amigo = $id_yo) 
             AND u.id_usuario NOT IN (SELECT id_usuario FROM chat_participante WHERE id_conversacion = $id_conv)
             AND a.estado = 'aceptada' AND u.id_usuario != $id_yo";
    $resA = mysqli_query($conexion, $sqlA);
    if ($resA) {
        while($row = mysqli_fetch_assoc($resA)) {
            $data['amigos_fuera'][] = $row;
        }
    }
} else {
    $data['error'] = mysqli_error($conexion);
}

echo json_encode($data);
exit;