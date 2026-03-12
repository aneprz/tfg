<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $puntos = $_POST['puntos'] ?? '';
    $id_videojuego = (int)($_POST['id_videojuego'] ?? 0);

    if ($id_videojuego <= 0) {
        echo "<script>alert('Error: Debes seleccionar un videojuego válido.'); window.history.back();</script>";
        exit();
    }

    $checkSql = "SELECT id_logro FROM logros WHERE nombre_logro = ? AND id_videojuego = ?";
    $checkStmt = $conexion->prepare($checkSql);
    $checkStmt->bind_param("si", $titulo, $id_videojuego);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        echo "<script>alert('Error: Este logro ya existe en este juego.'); window.location.href='../gestionLogros.php';</script>";
        exit();
    }
    $checkStmt->close();

    $sql = "INSERT INTO logros (nombre_logro, descripcion, puntos_logro, id_videojuego) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssi", $titulo, $descripcion, $puntos, $id_videojuego);

    if ($stmt->execute()) {
        echo "<script>window.location.href='../gestionLogros.php';</script>";
    } else {
        echo "Error en base de datos: " . $stmt->error;
    }
    $stmt->close();
} else {
    header("Location: ../gestionLogros.php");
    exit();
}
$conexion->close();
?>