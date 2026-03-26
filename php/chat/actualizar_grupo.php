<?php
ob_start(); // Evita que cualquier warning rompa el JSON
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db/conexiones.php';

$id_conv = isset($_POST['id_conv']) ? (int)$_POST['id_conv'] : 0;
// Importante: usamos 'nombre_grupo' para recibirlo y para la base de datos
$nuevo_nombre = isset($_POST['nombre_grupo']) ? mysqli_real_escape_string($conexion, $_POST['nombre_grupo']) : '';

if ($id_conv <= 0 || empty($nuevo_nombre)) {
    echo json_encode(['success' => false, 'error' => 'ID de grupo o nombre no recibidos']);
    exit;
}

$ruta_db = null;
if (isset($_FILES['foto_grupo']) && $_FILES['foto_grupo']['error'] === 0) {
    $directorio = "../../assets/img/grupos/"; 
    if (!is_dir($directorio)) mkdir($directorio, 0777, true);
    
    $extension = pathinfo($_FILES['foto_grupo']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = "grupo_" . $id_conv . "_" . time() . "." . $extension;
    $ruta_final = $directorio . $nombre_archivo;

    if (move_uploaded_file($_FILES['foto_grupo']['tmp_name'], $ruta_final)) {
        $ruta_db = "assets/img/grupos/" . $nombre_archivo;
    }
}

// CORRECCIÓN: Tu columna en la DB se llama 'nombre_grupo', no 'nombre'
$sql = "UPDATE chat_conversacion SET nombre_grupo = '$nuevo_nombre'";
if ($ruta_db) {
    // Si tienes una columna foto, asegúrate que se llame así. Si no existe, esta línea dará error SQL
    $sql .= ", foto = '$ruta_db'"; 
}
$sql .= " WHERE id_conversacion = $id_conv";

if (mysqli_query($conexion, $sql)) {
    ob_end_clean();
    echo json_encode(['success' => true]);
} else {
    $error = mysqli_error($conexion);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => $error]);
}