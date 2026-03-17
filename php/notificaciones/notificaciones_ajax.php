<?php
session_start();
require '../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['total' => 0, 'html' => '']);
    exit();
}

$id_sesion = $_SESSION['id_usuario'];

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
        // Creamos el diseño de cada notificación aquí
        $html .= '<li style="padding: 10px; border-bottom: 1px solid #eee;">';
        $html .= '<a href="' . $fila['url_destino'] . '" style="text-decoration:none; color:black;">';
        $html .= '<div style="font-size: 14px;">' . htmlspecialchars($fila['mensaje']) . '</div>';
        $html .= '<div style="font-size: 11px; color: #888;">' . $fila['fecha_creacion'] . '</div>';
        $html .= '</a>';
        $html .= '</li>';
    }
} else {
    $html = '<li style="padding: 15px; color: #666; text-align: center;">No tienes notificaciones</li>';
}

// Enviamos el objeto exacto que el JS espera
echo json_encode([
    'total' => $total,
    'html' => $html
]);