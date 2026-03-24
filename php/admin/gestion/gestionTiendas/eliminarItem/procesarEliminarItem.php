<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {

    $id = (int)$_POST['id'];

    // =========================
    // OBTENER IMAGEN
    // =========================
    $stmt = $conexion->prepare("SELECT imagen FROM Tienda_Items WHERE id_item = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $item = $resultado->fetch_assoc();

    $stmt->close();

    if ($item) {

        $imagen = $item['imagen'];
        $ruta = __DIR__ . '/../../../../../media/' . $imagen;

        // =========================
        // BORRAR IMAGEN
        // =========================
        if (!empty($imagen) && $imagen !== 'default.png' && file_exists($ruta)) {
            unlink($ruta);
        }

        // =========================
        // BORRAR ITEM
        // =========================
        $stmtDelete = $conexion->prepare("DELETE FROM Tienda_Items WHERE id_item = ?");
        $stmtDelete->bind_param("i", $id);

        if ($stmtDelete->execute()) {
            echo "<script> window.location.href='eliminarItem.php';</script>";
        } else {
            echo "Error al eliminar: " . $stmtDelete->error;
        }

        $stmtDelete->close();
    }
}

$conexion->close();
?>