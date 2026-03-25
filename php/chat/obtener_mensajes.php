<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = (int)$_SESSION['id_usuario'];
$id_conv = (int)$_GET['id'];

if (!$id_conv) exit;

// 1. Marcar que YO he leído la conversación ahora mismo
mysqli_query($conexion, "UPDATE chat_participante SET ultima_lectura = NOW() WHERE id_conversacion = $id_conv AND id_usuario = $id_yo");

// 2. Obtener la última lectura del OTRO
$resL = mysqli_query($conexion, "SELECT ultima_lectura FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario != $id_yo LIMIT 1");
$otra_lectura = mysqli_fetch_assoc($resL)['ultima_lectura'];

// 3. Obtener mensajes
$sql = "SELECT * FROM chat_mensaje WHERE id_conversacion = $id_conv ORDER BY fecha_envio ASC";
$res = mysqli_query($conexion, $sql);

$html = "";
$ultimo_msg_mio_fecha = null;

while ($m = mysqli_fetch_assoc($res)) {
    $clase = ($m['id_emisor'] == $id_yo) ? 'yo' : 'otro';
    if($clase == 'yo') $ultimo_msg_mio_fecha = $m['fecha_envio'];
    
    $html .= '<div class="mensaje ' . $clase . '">';
    $html .= htmlspecialchars($m['contenido']);
    $html .= '<span style="display:block; font-size:9px; opacity:0.5; margin-top:4px; text-align:right;">' . date('H:i', strtotime($m['fecha_envio'])) . '</span>';
    $html .= '</div>';
}

// 4. Determinar texto de "Visto"
$visto_txt = "";
if ($ultimo_msg_mio_fecha && $otra_lectura && $otra_lectura >= $ultimo_msg_mio_fecha) {
    $visto_txt = "Visto a las " . date('H:i', strtotime($otra_lectura));
}

echo json_encode(['html' => $html, 'visto' => $visto_txt]);