<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_conv = (int)$_POST['id_conv'];
$nuevo_nombre = mysqli_real_escape_string($conexion, $_POST['nombre_grupo']);

// Manejo de la imagen
$ruta_db = null;
if (isset($_FILES['foto_grupo']) && $_FILES['foto_grupo']['error'] === 0) {
    $directorio = "../../assets/img/grupos/"; // Asegúrate de que esta carpeta exista
    $extension = pathinfo($_FILES['foto_grupo']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = "grupo_" . $id_conv . "_" . time() . "." . $extension;
    $ruta_final = $directorio . $nombre_archivo;

    if (move_uploaded_file($_FILES['foto_grupo']['tmp_name'], $ruta_final)) {
        $ruta_db = "assets/img/grupos/" . $nombre_archivo;
    }
}

// Actualizar base de datos
$sql = "UPDATE chat_conversacion SET nombre = '$nuevo_nombre'";
if ($ruta_db) {
    $sql .= ", foto = '$ruta_db'"; // Asumiendo que tu columna se llama 'foto'
}
$sql .= " WHERE id_conversacion = $id_conv";

if (mysqli_query($conexion, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
}