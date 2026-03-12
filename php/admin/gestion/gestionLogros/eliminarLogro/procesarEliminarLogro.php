<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt1 = $conexion->prepare("DELETE FROM Logros_Usuario WHERE id_logro = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    $stmt2 = $conexion->prepare("DELETE FROM logros WHERE id_logro = ?");
    $stmt2->bind_param("i", $id);
    
    if ($stmt2->execute()) {
        header("Location: eliminarLogro.php");
    } else {
        echo "Error: " . $stmt2->error;
    }
    $stmt1->close();
    $stmt2->close();
}
$conexion->close();
?>