<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

// Eliminamos el IF de sesión solo para probar si llegan los datos
$response = ['total' => 0, 'html' => ''];

$resCount = mysqli_query($conexion, "SELECT COUNT(*) as total FROM Notificacion WHERE leida = 0");
$info = mysqli_fetch_assoc($resCount);
$response['total'] = (int)$info['total'];

$resLast = mysqli_query($conexion, "SELECT * FROM Notificacion ORDER BY fecha_creacion DESC LIMIT 5");

if ($resLast && mysqli_num_rows($resLast) > 0) {
    while($n = mysqli_fetch_assoc($resLast)) {
        $fecha = date('H:i', strtotime($n['fecha_creacion']));
        $response['html'] .= "<li style='padding:10px; border-bottom:1px solid #333;'><a href='{$n['url_destino']}' style='color:#eee; text-decoration:none;'>".htmlspecialchars($n['mensaje'])." <br><small style='color:#666'>$fecha</small></a></li>";
    }
} else {
    $response['html'] = "<li style='padding:15px; color:#666; text-align:center;'>No hay notificaciones</li>";
}

header('Content-Type: application/json');
echo json_encode($response);