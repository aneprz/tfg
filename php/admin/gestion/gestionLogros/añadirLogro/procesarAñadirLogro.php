<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $puntos = $_POST['puntos'];

    $checkSql = "SELECT id_logro FROM logros WHERE nombre_logro = ? AND puntos_logro = ?";
    $checkStmt = $conexion->prepare($checkSql);
    $checkStmt->bind_param("ss", $titulo, $puntos);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo "<script>alert('Error: Este logro con esa cantidad de puntos ya existe.'); window.location.href='../gestionLogros.php';</script>";
        exit();
    }
$checkStmt->close();

$sql = "INSERT INTO logros (nombre_logro, descripcion, puntos_logro) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sss", $titulo, $descripcion, $puntos);

if ($stmt->execute()) {
    echo "<script> window.location.href='../gestionLogros.php';</script>";
} else {
    echo "Error en base de datos: " . $stmt->error;
}
$stmt->close();
} else {
    echo "Error al subir la imagen.";
}
$conexion->close();

?>