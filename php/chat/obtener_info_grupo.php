<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = $_SESSION['id_usuario'] ?? 0;
$id_conv = isset($_GET['id_conv']) ? (int)$_GET['id_conv'] : 0;

$data = [
    'miembros' => [],
    'amigos_fuera' => [],
    'soy_creador' => false,
    'id_creador' => 0,
    'nombre_grupo' => ''
];

if ($id_yo <= 0 || $id_conv <= 0) {
    echo json_encode($data);
    exit;
}

// Info del grupo (sin foto_grupo porque no existe)
$sqlC = "SELECT id_usuario_creador, nombre_grupo FROM chat_conversacion WHERE id_conversacion = $id_conv";
$resC = mysqli_query($conexion, $sqlC);
$rowC = mysqli_fetch_assoc($resC);

if ($rowC) {
    $data['id_creador'] = (int)$rowC['id_usuario_creador'];
    $data['soy_creador'] = ($id_yo === $data['id_creador']);
    $data['nombre_grupo'] = $rowC['nombre_grupo'] ?? 'Grupo';
}

// Miembros del grupo
$sqlM = "SELECT u.id_usuario, u.gameTag, u.avatar 
         FROM chat_participante p 
         JOIN Usuario u ON p.id_usuario = u.id_usuario 
         WHERE p.id_conversacion = $id_conv";
$resM = mysqli_query($conexion, $sqlM);
while($row = mysqli_fetch_assoc($resM)) {
    $row['es_creador'] = ((int)$row['id_usuario'] === $data['id_creador']);
    $row['foto_perfil'] = !empty($row['avatar']) ? "../../img/avatares/" . $row['avatar'] : "../../img/avatares/default.png";
    $data['miembros'][] = $row;
}

// Amigos que NO están en el grupo
$sqlA = "SELECT DISTINCT u.id_usuario, u.gameTag, u.avatar 
         FROM amigos a 
         JOIN Usuario u ON ((a.id_usuario = $id_yo AND a.id_amigo = u.id_usuario) OR (a.id_amigo = $id_yo AND a.id_usuario = u.id_usuario))
         WHERE u.id_usuario != $id_yo AND a.estado = 'aceptada'
         AND u.id_usuario NOT IN (SELECT id_usuario FROM chat_participante WHERE id_conversacion = $id_conv)";
$resA = mysqli_query($conexion, $sqlA);
while($row = mysqli_fetch_assoc($resA)) {
    $row['foto_perfil'] = !empty($row['avatar']) ? "../../img/avatares/" . $row['avatar'] : "../../img/avatares/default.png";
    $data['amigos_fuera'][] = $row;
}

echo json_encode($data);
exit;
?>