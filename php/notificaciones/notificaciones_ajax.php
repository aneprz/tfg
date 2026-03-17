<?php
session_start();
require '../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['total' => 0, 'html' => '']);
    exit();
}

$id_sesion = $_SESSION['id_usuario'];

// Solo notificaciones no leídas para el usuario en sesión
$sql = "SELECT * FROM Notificacion WHERE id_usuario_destino = ? AND leida = 0 ORDER BY fecha_creacion DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_sesion);
$stmt->execute();
$resultado = $stmt->get_result();

$html = "";
$total = 0;

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $total++;
        $html .= '<li style="border-bottom: 1px solid #222;">';
        $html .= '<a href="' . $fila['url_destino'] . '" style="text-decoration:none; display:block; padding:10px;">';
        $html .= '<div style="color:#ffffff; font-size:13px; margin-bottom:3px;">' . htmlspecialchars($fila['mensaje']) . '</div>';
        $html .= '<small style="color:#888; font-size:11px;">' . $fila['fecha_creacion'] . '</small>';
        $html .= '</a>';
        $html .= '</li>';
    }
} else {
    $html = '<li style="padding: 15px; color: #888; text-align: center;">No hay notificaciones nuevas</li>';
}

echo json_encode([
    'total' => $total,
    'html' => $html
]);