<?php
// Desactivar salida de errores automática para que no rompan el JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

// 1. Verifica la ruta de conexión
if (!file_exists(__DIR__ . '/../../db/conexiones.php')) {
    echo json_encode(['error' => 'No se encuentra conexiones.php']);
    exit;
}
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = $_SESSION['id_usuario'] ?? 0;
$id_conv = isset($_GET['id_conv']) ? (int)$_GET['id_conv'] : 0;

$data = ['miembros' => [], 'amigos_fuera' => [], 'soy_creador' => false, 'id_creador' => 0];

// 2. Obtener creador
$sqlC = "SELECT id_usuario_creador FROM chat_conversacion WHERE id_conversacion = $id_conv";
$resC = mysqli_query($conexion, $sqlC);
if ($resC && $rowC = mysqli_fetch_assoc($resC)) {
    $data['id_creador'] = (int)$rowC['id_usuario_creador'];
    $data['soy_creador'] = ($id_yo === $data['id_creador']);
}

// 3. Miembros (Consulta simplificada sin fotos para evitar errores de columnas)
$sqlM = "SELECT u.id_usuario, u.gameTag 
         FROM chat_participante p 
         JOIN Usuario u ON p.id_usuario = u.id_usuario 
         WHERE p.id_conversacion = $id_conv";
$resM = mysqli_query($conexion, $sqlM);

if ($resM) {
    while($row = mysqli_fetch_assoc($resM)) {
        $row['es_creador'] = ((int)$row['id_usuario'] === $data['id_creador']);
        // Foto temporal fija para evitar errores
        $row['foto_perfil'] = "../../img/avatares/default.png"; 
        $data['miembros'][] = $row;
    }
}

// 4. Amigos fuera
$sqlA = "SELECT u.id_usuario, u.gameTag FROM amigos a 
         JOIN Usuario u ON (CASE WHEN a.id_usuario = $id_yo THEN a.id_amigo = u.id_usuario ELSE a.id_usuario = u.id_usuario END)
         WHERE (a.id_usuario = $id_yo OR a.id_amigo = $id_yo) 
         AND u.id_usuario NOT IN (SELECT id_usuario FROM chat_participante WHERE id_conversacion = $id_conv)
         AND a.estado = 'aceptada' AND u.id_usuario != $id_yo";

$resA = mysqli_query($conexion, $sqlA);
if ($resA) {
    while($row = mysqli_fetch_assoc($resA)) {
        $row['foto_perfil'] = "../../img/avatares/default.png";
        $data['amigos_fuera'][] = $row;
    }
}

echo json_encode($data);
exit;