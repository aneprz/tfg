<?php
session_start();
require '../../db/conexiones.php';

$id_usuario = $_SESSION['id_usuario'];
$id_item = (int) $_POST['id_item'];

mysqli_begin_transaction($conexion);

try {

    // Obtener tipo
    $res = mysqli_query($conexion, "
        SELECT tipo FROM Tienda_Items WHERE id_item = $id_item
    ");

    $item = mysqli_fetch_assoc($res);
    $tipo = $item['tipo'];

    // Desequipar SOLO este item
    mysqli_query($conexion, "
        UPDATE Usuario_Items
        SET equipado = 0
        WHERE id_usuario = $id_usuario
        AND id_item = $id_item
    ");

    // Limpiar en tabla Usuario
    if ($tipo === 'avatar') {
        mysqli_query($conexion, "
            UPDATE Usuario SET avatar_activo = NULL 
            WHERE id_usuario = $id_usuario
        ");
    }

    if ($tipo === 'marco') {
        mysqli_query($conexion, "
            UPDATE Usuario SET marco_activo = NULL 
            WHERE id_usuario = $id_usuario
        ");
    }

    if ($tipo === 'fondo') {
        mysqli_query($conexion, "
            UPDATE Usuario SET fondo_activo = NULL 
            WHERE id_usuario = $id_usuario
        ");
    }

    mysqli_commit($conexion);

} catch (Exception $e) {
    mysqli_rollback($conexion);
}

header("Location: inventario.php");
exit;