<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = (int)$_SESSION['id_usuario'];
$id_conv = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id_conv) exit;

// 1. Marcar que YO he leído esto ahora
mysqli_query($conexion, "UPDATE chat_participante SET ultima_lectura = NOW() WHERE id_conversacion = $id_conv AND id_usuario = $id_yo");

// 2. Ver cuándo leyó el OTRO por última vez
$resL = mysqli_query($conexion, "SELECT ultima_lectura FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario != $id_yo LIMIT 1");
$rowL = mysqli_fetch_assoc($resL);
$otra_lectura = $rowL['ultima_lectura'];

// 3. Obtener mensajes
$sql = "SELECT * FROM chat_mensaje WHERE id_conversacion = $id_conv ORDER BY fecha_envio ASC";
$res = mysqli_query($conexion, $sql);

$html = "";

while ($m = mysqli_fetch_assoc($res)) {
    $clase = ($m['id_emisor'] == $id_yo) ? 'yo' : 'otro';
    $hora = date('H:i', strtotime($m['fecha_envio']));
    
    $status_html = "";
    if ($clase == 'yo') {
        // Si el otro leyó después de enviar este mensaje
        if ($otra_lectura && $otra_lectura >= $m['fecha_envio']) {
            $status_html = '<span style="color: #f0c330; font-weight: bold; margin-left: 8px; font-size: 8px; letter-spacing: 0.5px;">VISTO</span>';
        } else {
            $status_html = '<span style="color: #666; font-weight: bold; margin-left: 8px; font-size: 8px; letter-spacing: 0.5px;">ENVIADO</span>';
        }
    }

    $html .= '<div class="mensaje ' . $clase . '">';
    $html .= htmlspecialchars($m['contenido']);
    $html .= '<div style="display: flex; justify-content: flex-end; align-items: center; font-size: 9px; opacity: 0.8; margin-top: 6px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 2px;">';
    $html .= $hora . $status_html;
    $html .= '</div>';
    $html .= '</div>';
}

echo $html;