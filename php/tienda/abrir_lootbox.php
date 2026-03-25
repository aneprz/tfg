<?php
session_start();
require '../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../sesiones/login/login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_lootbox = (int) $_POST['id_item'];

/* =========================
   1. OBTENER LOOTBOX DESDE Tienda_Items
   ========================= */
$res = mysqli_query($conexion, "
    SELECT id_item
    FROM Tienda_Items
    WHERE id_item = $id_lootbox AND tipo='lootbox' FOR UPDATE
");

if (!$res || mysqli_num_rows($res) === 0) {
    $_SESSION['error'] = "Lootbox no válida";
    header("Location: inventario.php");
    exit;
}

/* =========================
   2. OBTENER RECOMPENSAS
   ========================= */
$res = mysqli_query($conexion, "
    SELECT *
    FROM lootbox_recompensas
    WHERE id_lootbox = $id_lootbox
");

$recompensas = [];
while ($r = mysqli_fetch_assoc($res)) {
    $recompensas[] = $r;
}

if (empty($recompensas)) {
    $_SESSION['error'] = "Lootbox sin recompensas";
    header("Location: inventario.php");
    exit;
}

/* =========================
   3. SORTEO
   ========================= */
$rand = rand(1, 100);
$acumulado = 0;
$ganado = null;

foreach ($recompensas as $r) {
    $acumulado += $r['probabilidad'];
    if ($rand <= $acumulado) {
        $ganado = $r;
        break;
    }
}

if (!$ganado) {
    $_SESSION['error'] = "Error en el sorteo";
    header("Location: inventario.php");
    exit;
}

$id_item_ganado = $ganado['id_item'];

/* =========================
   4. DAR ITEM Y ELIMINAR LOOTBOX
   ========================= */
mysqli_begin_transaction($conexion);
try {
    // Insertar item ganado
    mysqli_query($conexion, "
        INSERT IGNORE INTO Usuario_Items (id_usuario, id_item)
        VALUES ($id_usuario, $id_item_ganado)
    ");

    // Eliminar lootbox usada
    mysqli_query($conexion, "
        DELETE FROM Usuario_Items
        WHERE id_usuario = $id_usuario
        AND id_item = $id_lootbox
        LIMIT 1
    ");

    // Obtener nombre del item
    $res = mysqli_query($conexion, "
        SELECT nombre FROM Tienda_Items WHERE id_item = $id_item_ganado
    ");
    $nombre = mysqli_fetch_assoc($res)['nombre'] ?? "Item desconocido";

    mysqli_commit($conexion);
    $_SESSION['success'] = "¡Has conseguido: $nombre 🎉!";

} catch (Exception $e) {
    mysqli_rollback($conexion);
    $_SESSION['error'] = $e->getMessage();
}

header("Location: inventario.php");
exit;