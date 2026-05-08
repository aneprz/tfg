<?php
session_start();
require '../../db/conexiones.php';

$id_usuario = (int)$_SESSION['id_usuario'];
$id_item = (int) $_POST['id_item'];

mysqli_begin_transaction($conexion);

try {

    // 1. EL ARREGLO: Sacamos el tipo Y LA IMAGEN del item
    $res = mysqli_query($conexion, "
        SELECT tipo, imagen FROM Tienda_Items WHERE id_item = $id_item
    ");
    
    $item = mysqli_fetch_assoc($res);
    if (!$item) throw new Exception("Item no encontrado");

    $tipo = $item['tipo'];
    $imagen = $item['imagen']; // Ej: 'marcos/1.png'

    // Desequipar todos los del mismo tipo en el inventario
    mysqli_query($conexion, "
        UPDATE Usuario_Items ui
        JOIN Tienda_Items ti ON ti.id_item = ui.id_item
        SET ui.equipado = 0
        WHERE ui.id_usuario = $id_usuario
        AND ti.tipo = '$tipo'
    ");

    // Equipar este
    mysqli_query($conexion, "
        UPDATE Usuario_Items
        SET equipado = 1
        WHERE id_usuario = $id_usuario
        AND id_item = $id_item
    ");

    // 2. Guardar en Usuario: Actualizamos tanto el ID activo como la ruta de la imagen
    if ($tipo === 'avatar') {
        mysqli_query($conexion, "UPDATE Usuario SET avatar_activo = $id_item, avatar = '$imagen' WHERE id_usuario = $id_usuario");
    }

    if ($tipo === 'marco') {
        mysqli_query($conexion, "UPDATE Usuario SET marco_activo = $id_item, marco_avatar = '$imagen' WHERE id_usuario = $id_usuario");
    }

    if ($tipo === 'fondo') {
        mysqli_query($conexion, "UPDATE Usuario SET fondo_activo = $id_item WHERE id_usuario = $id_usuario");
    }

    mysqli_commit($conexion);

} catch (Exception $e) {
    mysqli_rollback($conexion);
}

header("Location: inventario.php");
exit;
?>