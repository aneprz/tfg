<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../sesiones/login/login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_item = (int) $_POST['id_item'];

/* =========================
   INICIAR TRANSACCIÓN
   ========================= */

mysqli_begin_transaction($conexion);

try {

    // Obtener item
    $res = mysqli_query($conexion, "
    SELECT precio FROM Tienda_Items WHERE id_item = $id_item FOR UPDATE
    ");

    if (!$res || mysqli_num_rows($res) === 0) {
        throw new Exception("Item no existe");
    }

    $item = mysqli_fetch_assoc($res);
    $precio = $item['precio'];

    // Obtener puntos usuario
    $res = mysqli_query($conexion, "
    SELECT puntos_actuales FROM Usuario WHERE id_usuario = $id_usuario FOR UPDATE
    ");

    $user = mysqli_fetch_assoc($res);

    if ($user['puntos_actuales'] < $precio) {
        throw new Exception("No tienes suficientes puntos");
    }

    // Insertar item (evita duplicados por índice UNIQUE)
    mysqli_query($conexion, "
    INSERT INTO Usuario_Items (id_usuario, id_item)
    VALUES ($id_usuario, $id_item)
    ");

    if (mysqli_affected_rows($conexion) === 0) {
        throw new Exception("Ya tienes este item");
    }

    // Restar puntos
    mysqli_query($conexion, "
    UPDATE Usuario
    SET puntos_actuales = puntos_actuales - $precio
    WHERE id_usuario = $id_usuario
    ");

    // Registrar movimiento
    mysqli_query($conexion, "
    INSERT INTO Movimientos_Puntos (id_usuario, puntos, tipo, descripcion)
    VALUES ($id_usuario, -$precio, 'compra', 'Compra de item')
    ");

    mysqli_commit($conexion);

} catch (Exception $e) {

    mysqli_rollback($conexion);
}

header("Location: tienda.php");
exit;