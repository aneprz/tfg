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
    $precio = (float)$lootbox['precio'];

    /* =========================
       2. BLOQUEAR USUARIO
    ========================= */
    $resUser = mysqli_query($conexion, "
        SELECT puntos_actuales 
        FROM Usuario 
        WHERE id_usuario = $id_usuario
        FOR UPDATE
    ");

    if (!$resUser) {
        throw new Exception("Error Usuario");
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
       4. OBTENER ITEMS (INCLUYENDO RAREZA)
    ========================= */
    $resItems = mysqli_query($conexion, "
        SELECT ti.id_item, ti.nombre, ti.imagen, ti.rareza, lr.probabilidad
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
        $prob = max(0, (float)$row['probabilidad']); // ✅ DECIMALES
        $row['probabilidad'] = $prob;
        $totalProb += $prob;
        $items[] = $row;
    }

    if (empty($items) || $totalProb <= 0) {
        throw new Exception("Lootbox mal configurada");
    }

    /* =========================
       5. RNG REAL (DECIMALES)
    ========================= */
    $rand = (mt_rand() / mt_getrandmax()) * $totalProb;

    $acumulado = 0;
    $ganado = null;

    foreach ($items as $item) {
        $acumulado += $item['probabilidad'];

        if ($rand <= $acumulado) {
            $ganado = $item;
            break;
        }
    }

    if (!$ganado) {
        $ganado = $items[array_rand($items)];
    }

    /* =========================
       6. DUPLICADOS
    ========================= */
    $resCheck = mysqli_query($conexion, "
        SELECT 1 FROM Usuario_Items 
        WHERE id_usuario = $id_usuario 
        AND id_item = {$ganado['id_item']}
    ");

    $esDuplicado = mysqli_num_rows($resCheck) > 0;
    $valorItem = 0;

    if ($esDuplicado) {
        $resValor = mysqli_query($conexion, "
            SELECT precio FROM Tienda_Items 
            WHERE id_item = {$ganado['id_item']}
        ");

        $valorItem = (float)(mysqli_fetch_assoc($resValor)['precio'] ?? 0);

        mysqli_query($conexion, "
            UPDATE Usuario 
            SET puntos_actuales = puntos_actuales + $valorItem
            WHERE id_usuario = $id_usuario
        ");
    } else {
        mysqli_query($conexion, "
            INSERT INTO Usuario_Items (id_usuario, id_item)
            VALUES ($id_usuario, {$ganado['id_item']})
        ");
    }

    /* =========================
       7. OBTENER PUNTOS REALES
    ========================= */
    $resFinal = mysqli_query($conexion, "
        SELECT puntos_actuales FROM Usuario 
        WHERE id_usuario = $id_usuario
    ");

    $puntosFinales = (float)mysqli_fetch_assoc($resFinal)['puntos_actuales'];

    mysqli_commit($conexion); // ✅ CRÍTICO

    /* =========================
       8. RESPUESTA
    ========================= */
    echo json_encode([
        "ok" => true,
        "items" => $items,
        "ganado" => $ganado,
        "nuevosPuntos" => $puntosFinales,
        "duplicado" => $esDuplicado,
        "valorDevuelto" => $valorItem
    ]);

} catch (Exception $e) {
    mysqli_rollback($conexion);
    echo json_encode([
        "ok" => false,
        "error" => $e->getMessage()
    ]);
}