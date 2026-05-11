<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recogemos los datos básicos
    $nombre = $_POST['nombre'];
    $precio = (int)$_POST['precio'];
    $color_neon = $_POST['color_neon'];
    $cosmeticos = $_POST['cosmeticos'] ?? [];

    // ==============================================================
    // LA REGLA DE ORO: El servidor calcula los puntos, no el navegador
    // ==============================================================
    $pts_consuelo = (int)round($precio * 0.50);
    $pts_ganancia = (int)round($precio * 1.50);
    $pts_jackpot  = (int)round($precio * 7.00);

    // Las probabilidades sí las seguimos cogiendo del formulario
    $prob_consuelo = (int)$_POST['prob_consuelo'];
    $prob_ganancia = (int)$_POST['prob_ganancia'];
    $prob_jackpot  = (int)$_POST['prob_jackpot'];

    if (empty($nombre) || $precio <= 0 || count($cosmeticos) < 3) {
        die("Faltan datos o no has añadido suficientes cosméticos.");
    }

    // --- PROCESAR LA IMAGEN ---
    if (!isset($_FILES['imagen_caja']) || $_FILES['imagen_caja']['error'] !== UPLOAD_ERR_OK) {
        die("Error al subir la imagen de la caja.");
    }

    $fileTmpPath = $_FILES['imagen_caja']['tmp_name'];
    $fileName = $_FILES['imagen_caja']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($fileExtension, $extensionesPermitidas)) die("Formato no permitido.");

    $nuevoNombreImagen = 'evento_' . time() . '.' . $fileExtension;
    $rutaDestinoFisica = __DIR__ . '/../../../../../media/' . $nuevoNombreImagen;

    if (!move_uploaded_file($fileTmpPath, $rutaDestinoFisica)) {
        die("Error guardando la imagen en el servidor.");
    }

    mysqli_begin_transaction($conexion);

    try {
        // 1. CREAR LA LOOTBOX EN LA TIENDA
        $stmtLootbox = $conexion->prepare("
            INSERT INTO Tienda_Items (nombre, tipo, precio, imagen, activo, fecha_creacion, color_neon)
            VALUES (?, 'lootbox', ?, ?, 1, NOW(), ?)
        ");
        $stmtLootbox->bind_param("siss", $nombre, $precio, $nuevoNombreImagen, $color_neon);
        $stmtLootbox->execute();
        $id_lootbox = $stmtLootbox->insert_id;

        // 2. FUNCIÓN PARA CREAR LOS PREMIOS DE PUNTOS DINÁMICOS
        // Para que la ruleta sepa qué son puntos, guardaremos el valor en el 'precio' (como recompensa)
        $stmtPuntos = $conexion->prepare("
            INSERT INTO Tienda_Items (nombre, tipo, precio, imagen, activo)
            VALUES (?, 'puntos', ?, 'logoPlatino.png', 0)
        ");

        $stmtLink = $conexion->prepare("INSERT INTO lootbox_recompensas (id_lootbox, id_item, probabilidad) VALUES (?, ?, ?)");

        // 2.1 Insertar y linkear Premio Consuelo
        $nombreConsuelo = $pts_consuelo . " Puntos";
        $stmtPuntos->bind_param("si", $nombreConsuelo, $pts_consuelo);
        $stmtPuntos->execute();
        $id_consuelo = $stmtPuntos->insert_id;
        $stmtLink->bind_param("iii", $id_lootbox, $id_consuelo, $prob_consuelo);
        $stmtLink->execute();

        // 2.2 Insertar y linkear Premio Ganancia
        $nombreGanancia = $pts_ganancia . " Puntos";
        $stmtPuntos->bind_param("si", $nombreGanancia, $pts_ganancia);
        $stmtPuntos->execute();
        $id_ganancia = $stmtPuntos->insert_id;
        $stmtLink->bind_param("iii", $id_lootbox, $id_ganancia, $prob_ganancia);
        $stmtLink->execute();

        // 2.3 Insertar y linkear Premio Jackpot
        $nombreJackpot = $pts_jackpot . " Puntos";
        $stmtPuntos->bind_param("si", $nombreJackpot, $pts_jackpot);
        $stmtPuntos->execute();
        $id_jackpot = $stmtPuntos->insert_id;
        $stmtLink->bind_param("iii", $id_lootbox, $id_jackpot, $prob_jackpot);
        $stmtLink->execute();

        // 3. LINKEAR LOS COSMÉTICOS
        foreach ($cosmeticos as $cosmetico) {
            $id_item = (int)$cosmetico['id_item'];
            $prob = (int)$cosmetico['probabilidad'];
            $stmtLink->bind_param("iii", $id_lootbox, $id_item, $prob);
            $stmtLink->execute();
        }

        mysqli_commit($conexion);
        header("Location: ../gestionTienda.php");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        if(file_exists($rutaDestinoFisica)) unlink($rutaDestinoFisica);
        die("Error en la Base de Datos: " . $e->getMessage());
    }
}
?>