<?php
session_start();
require '../../db/conexiones.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_lootbox = (int)($_POST['id_lootbox'] ?? 0);

if (!$id_usuario || !$id_lootbox) {
    echo json_encode(["ok"=>false,"error"=>"Datos inválidos"]);
    exit;
}

mysqli_begin_transaction($conexion);

try {

    /* =========================
       1. OBTENER LOOTBOX
    ========================= */
    $res = mysqli_query($conexion, "
        SELECT precio 
        FROM Tienda_Items 
        WHERE id_item = $id_lootbox AND tipo='lootbox'
    ");

    if (!$res || mysqli_num_rows($res) === 0) {
        throw new Exception("Lootbox no encontrada");
    }

    $lootbox = mysqli_fetch_assoc($res);
    $precio = (int)$lootbox['precio'];

    /* =========================
       2. BLOQUEAR USUARIO (ANTI BUG)
    ========================= */
    $resUser = mysqli_query($conexion, "
        SELECT puntos_actuales 
        FROM Usuario 
        WHERE id_usuario = $id_usuario
        FOR UPDATE
    ");

    if (!$resUser) {
        throw new Exception("Error usuario");
    }

    $user = mysqli_fetch_assoc($resUser);

    if ($user['puntos_actuales'] < $precio) {
        throw new Exception("No tienes puntos suficientes");
    }

    /* =========================
       3. RESTAR PUNTOS
    ========================= */
    mysqli_query($conexion, "
        UPDATE Usuario 
        SET puntos_actuales = puntos_actuales - $precio
        WHERE id_usuario = $id_usuario
    ");

    /* =========================
       4. OBTENER ITEMS
    ========================= */
    $resItems = mysqli_query($conexion, "
        SELECT ti.id_item, ti.nombre, ti.imagen, lr.probabilidad
        FROM lootbox_recompensas lr
        JOIN Tienda_Items ti ON ti.id_item = lr.id_item
        WHERE lr.id_lootbox = $id_lootbox
    ");

    if (!$resItems) {
        throw new Exception("Error cargando items");
    }

    $items = [];
    $totalProb = 0;

    while ($row = mysqli_fetch_assoc($resItems)) {
        $prob = max(0, (int)$row['probabilidad']);
        $row['probabilidad'] = $prob;

        $totalProb += $prob;
        $items[] = $row;
    }

    if (empty($items) || $totalProb <= 0) {
        throw new Exception("Lootbox mal configurada");
    }

    /* =========================
       5. RNG REAL
    ========================= */
    $rand = random_int(1, $totalProb);
    $acumulado = 0;
    $ganado = null;

    foreach ($items as $item) {
        $acumulado += $item['probabilidad'];

        if ($rand <= $acumulado) {
            $ganado = $item;
            break;
        }
    }

    // fallback seguridad
    if (!$ganado) {
        $ganado = $items[array_rand($items)];
    }

    /* =========================
       6. GUARDAR ITEM (SIN DUPLICAR)
    ========================= */
    mysqli_query($conexion, "
        INSERT IGNORE INTO Usuario_Items (id_usuario, id_item)
        VALUES ($id_usuario, {$ganado['id_item']})
    ");

    mysqli_commit($conexion);

    /* =========================
       7. RESPUESTA
    ========================= */
    echo json_encode([
        "ok" => true,
        "items" => $items,
        "ganado" => $ganado,
        "nuevosPuntos" => $user['puntos_actuales'] - $precio
    ]);

} catch (Exception $e) {

    mysqli_rollback($conexion);

    echo json_encode([
        "ok" => false,
        "error" => $e->getMessage()
    ]);
}