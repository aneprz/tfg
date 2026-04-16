<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
ob_clean();

require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = $_SESSION['id_usuario'] ?? 0;
$id_conv = isset($_POST['id_conv']) ? (int)$_POST['id_conv'] : 0;
$nuevo_nombre = isset($_POST['nombre_grupo']) ? trim($_POST['nombre_grupo']) : '';

$respuesta = ['success' => false, 'error' => ''];

if ($id_yo == 0 || $id_conv == 0) {
    $respuesta['error'] = 'Sesión o ID inválido';
    echo json_encode($respuesta);
    exit;
}

// Obtener datos actuales del grupo
$resActual = mysqli_query($conexion, "SELECT nombre_grupo, foto_grupo FROM chat_conversacion WHERE id_conversacion = $id_conv");
$actual = mysqli_fetch_assoc($resActual);

$nombre_final = $actual['nombre_grupo'];
$foto_final = $actual['foto_grupo'];

// 1. Actualizar NOMBRE solo si se envió y no está vacío
if (!empty($nuevo_nombre)) {
    $nombre_escapado = mysqli_real_escape_string($conexion, $nuevo_nombre);
    $nombre_final = $nombre_escapado;
}

// 2. Actualizar FOTO solo si se subió un archivo
if (isset($_FILES['foto_grupo']) && $_FILES['foto_grupo']['error'] === 0 && $_FILES['foto_grupo']['size'] > 0) {
    $directorio = __DIR__ . '/../../assets/img/grupos/';
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }
    
    $extension = strtolower(pathinfo($_FILES['foto_grupo']['name'], PATHINFO_EXTENSION));
    $extensiones_validas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($extension, $extensiones_validas)) {
        $nombre_archivo = "grupo_" . $id_conv . "_" . time() . "." . $extension;
        $ruta_final = $directorio . $nombre_archivo;
        
        if (move_uploaded_file($_FILES['foto_grupo']['tmp_name'], $ruta_final)) {
            // Borrar foto anterior si existe y no es la default
            if (!empty($actual['foto_grupo']) && $actual['foto_grupo'] != 'assets/img/grupos/grupo_default.png') {
                $ruta_anterior = __DIR__ . '/../../' . $actual['foto_grupo'];
                if (file_exists($ruta_anterior)) {
                    unlink($ruta_anterior);
                }
            }
            $foto_final = "assets/img/grupos/" . $nombre_archivo;
        }
    }
}

// 3. Ejecutar UPDATE solo si hubo cambios
$actualizar = false;
$campos = [];

if ($nombre_final != $actual['nombre_grupo']) {
    $campos[] = "nombre_grupo = '$nombre_final'";
    $actualizar = true;
}
if ($foto_final != $actual['foto_grupo']) {
    $campos[] = "foto_grupo = '$foto_final'";
    $actualizar = true;
}

if ($actualizar) {
    $sql = "UPDATE chat_conversacion SET " . implode(", ", $campos) . " WHERE id_conversacion = $id_conv";
    if (mysqli_query($conexion, $sql)) {
        $respuesta['success'] = true;
        $respuesta['nueva_foto'] = $foto_final;
        $respuesta['nuevo_nombre'] = $nombre_final;
    } else {
        $respuesta['error'] = 'Error al actualizar: ' . mysqli_error($conexion);
    }
} else {
    $respuesta['success'] = true;
    $respuesta['message'] = 'Sin cambios';
}

echo json_encode($respuesta);
exit;
?>