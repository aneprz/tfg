<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    exit('No autorizado');
}

$id_yo = (int)$_SESSION['id_usuario'];
$id_conversacion = (int)$_GET['id'];

// Seguridad: Verificar que el usuario pertenece a esta conversación
$sqlCheck = "SELECT 1 FROM chat_participante WHERE id_conversacion = $id_conversacion AND id_usuario = $id_yo";
$resCheck = mysqli_query($conexion, $sqlCheck);

if (mysqli_num_rows($resCheck) === 0) {
    exit('Acceso denegado');
}

// Obtener mensajes
$sql = "SELECT * FROM chat_mensaje WHERE id_conversacion = $id_conversacion ORDER BY fecha_envio ASC";
$res = mysqli_query($conexion, $sql);

if (mysqli_num_rows($res) === 0) {
    echo '<p style="text-align:center; color:#555; margin-top:20px;">No hay mensajes en esta conversación. ¡Dile hola!</p>';
}

while ($msg = mysqli_fetch_assoc($res)) {
    $clase = ($msg['id_emisor'] == $id_yo) ? 'yo' : 'otro';
    echo '<div class="mensaje ' . $clase . '">';
    echo htmlspecialchars($msg['contenido']);
    echo '<span style="display:block; font-size:10px; opacity:0.6; margin-top:5px;">' . date('H:i', strtotime($msg['fecha_envio'])) . '</span>';
    echo '</div>';
}
?>