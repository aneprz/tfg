<?php
session_start();
// Ajustado a la profundidad: eliminarLootbox (1) -> gestionTiendas (2) -> gestion (3) -> admin (4) -> php (5)
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_lootbox'])) {
    $id_lootbox = (int)$_POST['id_lootbox'];

    mysqli_begin_transaction($conexion);

    try {
        // 1. Obtener imagen para borrarla del servidor
        $stmtImg = $conexion->prepare("SELECT imagen FROM Tienda_Items WHERE id_item = ? AND tipo = 'lootbox'");
        $stmtImg->bind_param("i", $id_lootbox);
        $stmtImg->execute();
        $caja = $stmtImg->get_result()->fetch_assoc();
        
        if (!$caja) {
            throw new Exception("La caja no existe.");
        }

        // 2. Borrar los items fantasma de "Puntos" de la tienda
        $sqlPuntos = "SELECT t.id_item FROM Tienda_Items t 
                      JOIN lootbox_recompensas lr ON t.id_item = lr.id_item 
                      WHERE lr.id_lootbox = ? AND t.tipo = 'puntos'";
        $stmtBuscarPuntos = $conexion->prepare($sqlPuntos);
        $stmtBuscarPuntos->bind_param("i", $id_lootbox);
        $stmtBuscarPuntos->execute();
        $resPuntos = $stmtBuscarPuntos->get_result();
        
        while ($punto = $resPuntos->fetch_assoc()) {
            $idPunto = $punto['id_item'];
            $conexion->query("DELETE FROM Tienda_Items WHERE id_item = $idPunto");
        }

        // 3. Borrar enlaces de recompensas
        $stmtLink = $conexion->prepare("DELETE FROM lootbox_recompensas WHERE id_lootbox = ?");
        $stmtLink->bind_param("i", $id_lootbox);
        $stmtLink->execute();

        // 4. Borrar la caja en sí
        $stmtCaja = $conexion->prepare("DELETE FROM Tienda_Items WHERE id_item = ?");
        $stmtCaja->bind_param("i", $id_lootbox);
        $stmtCaja->execute();

        // 5. Borrar la imagen física
        $rutaImagen = __DIR__ . '/../../../../../media/' . $caja['imagen'];
        if (file_exists($rutaImagen) && strpos($caja['imagen'], 'evento_') !== false) {
            unlink($rutaImagen);
        }

        mysqli_commit($conexion);
        $_SESSION['success'] = "Caja de evento eliminada correctamente.";

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $_SESSION['error'] = "Error al borrar: " . $e->getMessage();
    }
}

// Redirigir de vuelta al panel principal de la tienda
header("Location: ../gestionTienda.php");
exit();
?>