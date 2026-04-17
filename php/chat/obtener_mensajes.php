<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

// Verificamos sesión e ID de conversación
if (!isset($_SESSION['id_usuario'])) exit;
$id_yo = (int)$_SESSION['id_usuario'];

// Importante: usamos $_GET['id_conversacion'] porque viene del fetch de JS
$id_conv = isset($_GET['id_conversacion']) ? (int)$_GET['id_conversacion'] : 0;

if ($id_conv <= 0) exit;

// Marcar notificaciones de este chat como leídas
$urlPattern = "%conv=$id_conv%";
mysqli_query($conexion, "UPDATE Notificacion SET leida = 1 WHERE id_usuario_destino = $id_yo AND url_destino LIKE '$urlPattern' AND leida = 0");

// Resetear contador de no leídos al abrir el chat
mysqli_query($conexion, "UPDATE chat_participante SET mensajes_no_leidos = 0 
                         WHERE id_conversacion = $id_conv AND id_usuario = $id_yo");
                         
// 1. Obtener info básica del chat (para saber si es grupo y quién manda)
$resInfo = mysqli_query($conexion, "SELECT tipo, id_usuario_creador FROM chat_conversacion WHERE id_conversacion = $id_conv");
$info = mysqli_fetch_assoc($resInfo);
$tipo_conv = $info['tipo'] ?? 'individual';
$id_creador = (int)($info['id_usuario_creador'] ?? 0);

// 2. Marcar como leído
mysqli_query($conexion, "UPDATE chat_participante SET ultima_lectura = NOW() WHERE id_conversacion = $id_conv AND id_usuario = $id_yo");

// 3. Obtener última lectura del OTRO (para el "Visto")
$resL = mysqli_query($conexion, "SELECT MAX(ultima_lectura) as ultima FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario != $id_yo");
$rowL = mysqli_fetch_assoc($resL);
$otra_lectura = $rowL['ultima'];

// 4. Cargar los mensajes con el GameTag del emisor (JOIN)
$sql = "SELECT m.*, u.gameTag 
        FROM chat_mensaje m 
        JOIN Usuario u ON m.id_emisor = u.id_usuario 
        WHERE m.id_conversacion = $id_conv 
        ORDER BY m.fecha_envio ASC";
$res = mysqli_query($conexion, $sql);

$html = "";
while ($m = mysqli_fetch_assoc($res)) {
    $es_mio = ($m['id_emisor'] == $id_yo);
    $clase = $es_mio ? 'yo' : 'otro';
    $hora = date('H:i', strtotime($m['fecha_envio']));
    $es_creador = ($m['id_emisor'] == $id_creador);

    $html .= '<div class="mensaje ' . $clase . '">';
    
    // Si es grupo y no soy yo, mostrar nombre y corona
    if ($tipo_conv == 'grupal' && !$es_mio) {
        $corona = $es_creador ? ' <span title="Creador" style="cursor:help;">👑</span>' : '';
        $html .= '<b style="font-size:11px; color:#f0c330; display:block; margin-bottom:4px;">' . htmlspecialchars($m['gameTag']) . $corona . '</b>';
    }

    $html .= '<div class="contenido-texto">' . htmlspecialchars($m['contenido']) . '</div>';
    
    // Footer del mensaje
    $status_visto = "";
    if ($es_mio) {
        $visto = ($otra_lectura && $otra_lectura >= $m['fecha_envio']);
        $status_visto = $visto ? ' <span style="color:#f0c330;">VISTO</span>' : ' <span style="color:#666;">ENVIADO</span>';
    }

    $html .= '<div style="display:flex; justify-content:flex-end; font-size:9px; opacity:0.6; margin-top:4px;">' . $hora . $status_visto . '</div>';
    $html .= '</div>';
}

echo $html;