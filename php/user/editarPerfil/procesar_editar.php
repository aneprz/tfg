<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) exit("No autorizado");

$id = $_SESSION['id_usuario'];
$nombre = $_POST['nombreApellido'];
$bio = $_POST['biografia'];
$gameTag = $_POST['gameTag'];

// 1. Sacamos su foto y su marco actual para saber qué hacemos luego con ellos
$stmt_old = $conexion->prepare("SELECT avatar, marco_avatar FROM Usuario WHERE id_usuario = ?");
$stmt_old->bind_param("i", $id);
$stmt_old->execute();
$res_old = $stmt_old->get_result();
$user_old = $res_old->fetch_assoc();

$nombre_avatar_final = $user_old['avatar']; 
$ruta_media = "../../../media/";
$foto_a_borrar = $user_old['avatar'];
$marco_final = $user_old['marco_avatar']; // Guardamos el marco que ya tenía por si no lo cambia

// 2. LÓGICA A: ¿Ha seleccionado un avatar de su inventario?
if (isset($_POST['avatar_inventario']) && !empty($_POST['avatar_inventario'])) {
    
    $nombre_avatar_final = $_POST['avatar_inventario']; 

    // Solo borramos si empieza por "u" + su ID (así no borramos cosas de la tienda).
    if (!empty($foto_a_borrar) && strpos($foto_a_borrar, 'u'.$id.'_') === 0) {
        if (file_exists($ruta_media . $foto_a_borrar)) {
            unlink($ruta_media . $foto_a_borrar);
        }
    }

} 
// 3. LÓGICA B: No ha elegido del inventario, pero ¿ha subido una foto desde su PC?
elseif (isset($_FILES['avatar_archivo']) && $_FILES['avatar_archivo']['error'] === UPLOAD_ERR_OK) {
    
    $ext = pathinfo($_FILES['avatar_archivo']['name'], PATHINFO_EXTENSION);
    $nuevo_nombre = "u" . $id . "_" . time() . "." . $ext;
    $destino = $ruta_media . $nuevo_nombre;

    if (move_uploaded_file($_FILES['avatar_archivo']['tmp_name'], $destino)) {
        
        if (!empty($foto_a_borrar) && strpos($foto_a_borrar, 'u'.$id.'_') === 0) {
            if (file_exists($ruta_media . $foto_a_borrar)) {
                unlink($ruta_media . $foto_a_borrar);
            }
        }

        $nombre_avatar_final = $nuevo_nombre; 
    }
}

// LÓGICA C: ¿Ha tocado algo de los marcos?
if (isset($_POST['marco_inventario'])) {
    if ($_POST['marco_inventario'] === 'NULL' || $_POST['marco_inventario'] === '') {
        $marco_final = NULL; // Se lo ha quitado
    } else {
        $marco_final = $_POST['marco_inventario']; // Se ha puesto uno nuevo
    }
}

// 4. Actualizamos la sesión y la base de datos (AHORA CON MARCO)
$_SESSION['tag'] = $gameTag;
$stmt = $conexion->prepare("UPDATE Usuario SET gameTag = ?, nombre_apellido = ?, biografia = ?, avatar = ?, marco_avatar = ? WHERE id_usuario = ?");
// ATENCIÓN AQUÍ: 5 strings (s) y 1 entero (i)
$stmt->bind_param("sssssi", $gameTag, $nombre, $bio, $nombre_avatar_final, $marco_final, $id);

if ($stmt->execute()) {
    echo "<script> window.location.href='../perfiles/perfilSesion.php';</script>";
} else {
    echo "Error al actualizar";
}

$stmt->close();
$conexion->close();
?>