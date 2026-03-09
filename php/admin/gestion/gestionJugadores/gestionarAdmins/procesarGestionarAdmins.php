<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['nuevo_estado'])) {
    $id = (int)$_POST['id'];
    $nuevo_estado = (int)$_POST['nuevo_estado'];

    $stmt = $conexion->prepare("UPDATE Usuario SET admin = ? WHERE id_usuario = ?");
    $stmt->bind_param("ii", $nuevo_estado, $id);

    if ($stmt->execute()) {
        header("Location: gestionarAdmins.php?success=1");
    } else {
        echo "Error al actualizar permisos: " . $stmt->error;
    }
    $stmt->close();
}

$conexion->close();
?>