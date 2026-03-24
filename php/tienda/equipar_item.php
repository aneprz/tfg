<?php
session_start();
require '../../db/conexiones.php';

$id_usuario = $_SESSION['id_usuario'];
$id_item = (int) $_POST['id_item'];

mysqli_begin_transaction($conexion);

try {

    // Obtener tipo del item
    $res = mysqli_query($conexion, "
        SELECT tipo FROM Tienda_Items WHERE id_item = $id_item
    ");

    $item = mysqli_fetch_assoc($res);
    $tipo = $item['tipo'];

    // Desequipar todos los del mismo tipo
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

    // Guardar en Usuario (CONTROLADO)
    if ($tipo === 'avatar') {
        mysqli_query($conexion, "UPDATE Usuario SET avatar_activo = $id_item WHERE id_usuario = $id_usuario");
    }

    if ($tipo === 'marco') {
        mysqli_query($conexion, "UPDATE Usuario SET marco_activo = $id_item WHERE id_usuario = $id_usuario");
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