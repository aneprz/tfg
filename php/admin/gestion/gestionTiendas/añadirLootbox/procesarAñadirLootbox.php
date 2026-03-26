<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $precio = (int)$_POST['precio'];
    $imagen = $_POST['imagen']; // ✅ nuevo campo
    $items = $_POST['items'] ?? [];

    if (empty($nombre) || $precio < 0 || empty($items) || empty($imagen)) {
        die("Datos incompletos");
    }

    // Validar que la imagen sea una de las permitidas (seguridad)
    $imagenesPermitidas = ['lootbox_default.png', 'lootbox_oro.png', 'lootbox_plata.png', 'lootbox_legendaria.png'];
    if (!in_array($imagen, $imagenesPermitidas)) {
        die("Imagen no válida");
    }

    mysqli_begin_transaction($conexion);

    try {
        // 1️⃣ Insertar lootbox como item de tienda (con imagen)
        $stmtItem = $conexion->prepare("
            INSERT INTO Tienda_Items (nombre, tipo, precio, imagen, activo, fecha_creacion)
            VALUES (?, 'lootbox', ?, ?, 1, NOW())
        ");
        $stmtItem->bind_param("sis", $nombre, $precio, $imagen);
        $stmtItem->execute();
        $id_tienda_item = $stmtItem->insert_id;

        // 2️⃣ Insertar recompensas en lootbox_recompensas
        $stmt2 = $conexion->prepare("INSERT INTO lootbox_recompensas (id_lootbox, id_item, probabilidad) VALUES (?, ?, ?)");
        foreach ($items as $item) {
            $id_item = (int)$item['id_item'];
            $prob = (int)$item['probabilidad'];
            $stmt2->bind_param("iii", $id_tienda_item, $id_item, $prob);
            $stmt2->execute();
        }

        mysqli_commit($conexion);
        header("Location: ../gestionTienda.php");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        die("Error al crear lootbox: " . $e->getMessage());
    }
}