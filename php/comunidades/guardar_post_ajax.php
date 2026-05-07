<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_usuario'])) {
    $id_comunidad = (int)$_POST['id_comunidad'];
    $id_usuario = $_SESSION['id_usuario'];
    $id_canal = isset($_POST['id_canal']) ? (int)$_POST['id_canal'] : 0;
    $contenido = mysqli_real_escape_string($conexion, $_POST['contenido']);

    // Obtener nombre del canal para la etiqueta
    if ($id_canal > 0) {
        $resCanal = mysqli_query($conexion, "SELECT nombre_canal FROM canal WHERE id_canal = $id_canal");
        $canal = mysqli_fetch_assoc($resCanal);
        $etiqueta = '[CANAL:' . $canal['nombre_canal'] . '] ';
        $contenido = $etiqueta . $contenido;
    }

    if (!empty($contenido)) {
        $sql = "INSERT INTO post (id_comunidad, id_usuario, contenido, fecha_publicacion) 
                VALUES ($id_comunidad, $id_usuario, '$contenido', NOW())";
        
        if (mysqli_query($conexion, $sql)) {
            echo json_encode(['status' => 'success']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conexion)]);
            exit;
        }
    }
}

echo json_encode(['status' => 'error', 'message' => 'Datos insuficientes']);
?>