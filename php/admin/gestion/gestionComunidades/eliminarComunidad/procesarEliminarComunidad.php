<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

$stmtDelete = $conexion->prepare("DELETE FROM Comunidad WHERE id_comunidad = ?");
$stmtDelete->bind_param("i", $id);

if ($stmtDelete->execute()) {
    echo "<script> window.location.href='eliminarComunidad.php';</script>";
} else {
    echo "Error al eliminar de la base de datos: " . $stmtDelete->error;
}
$stmtDelete->close();
}
$conexion->close();
?>