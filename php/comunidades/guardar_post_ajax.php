<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

// Indicamos al navegador que responderemos en formato JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_usuario'])) {
    $id_comunidad = (int)$_POST['id_comunidad'];
    $id_usuario = $_SESSION['id_usuario'];
    $contenido = mysqli_real_escape_string($conexion, $_POST['contenido']);

    if (!empty($contenido)) {
        // Usamos minúsculas 'post' para ser consistentes
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

echo json_encode(['status' => 'error', 'message' => 'Datos insuficientes o sesión expirada']);