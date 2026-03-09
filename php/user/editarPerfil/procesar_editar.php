<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) exit("No autorizado");

$id = $_SESSION['id_usuario'];
$nombre = $_POST['nombreApellido'];
$bio = $_POST['biografia'];
$gameTag = $_POST['gameTag'];

$stmt_old = $conexion->prepare("SELECT avatar FROM Usuario WHERE id_usuario = ?");
$stmt_old->bind_param("i", $id);
$stmt_old->execute();
$res_old = $stmt_old->get_result();
$user_old = $res_old->fetch_assoc();

$nombre_avatar_final = $user_old['avatar']; 
$ruta_media = "../../../media/";

if (isset($_FILES['avatar_archivo']) && $_FILES['avatar_archivo']['error'] === UPLOAD_ERR_OK) {
    
    $ext = pathinfo($_FILES['avatar_archivo']['name'], PATHINFO_EXTENSION);
    $nuevo_nombre = "u" . $id . "_" . time() . "." . $ext;
    $destino = $ruta_media . $nuevo_nombre;

    if (move_uploaded_file($_FILES['avatar_archivo']['tmp_name'], $destino)) {
    
        $foto_a_borrar = $user_old['avatar'];

        if (!empty($foto_a_borrar) && 
            $foto_a_borrar !== 'perfil_default.jpg' && 
            file_exists($ruta_media . $foto_a_borrar)) {
            
            unlink($ruta_media . $foto_a_borrar);
        }

        $nombre_avatar_final = $nuevo_nombre; 
    }
}

$_SESSION['tag'] = $gameTag;
$stmt = $conexion->prepare("UPDATE Usuario SET gameTag = ?, nombre_apellido = ?, biografia = ?, avatar = ? WHERE id_usuario = ?");
$stmt->bind_param("ssssi", $gameTag, $nombre, $bio, $nombre_avatar_final, $id);

if ($stmt->execute()) {
    echo "<script> window.location.href='../perfiles/perfilSesion.php';</script>";
} else {
    echo "Error al actualizar";
}

$stmt->close();
$conexion->close();
?>