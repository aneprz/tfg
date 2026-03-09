<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (isset($_SESSION['id_usuario']) && isset($_GET['id'])) {
    $miId = (int)$_SESSION['id_usuario'];
    $idAmigo = (int)$_GET['id'];
    $comunidadRef = isset($_GET['ref']) ? (int)$_GET['ref'] : null;

    if ($miId !== $idAmigo) {
        $sql = "INSERT IGNORE INTO Amigos (id_usuario, id_amigo, estado) VALUES (?, ?, 'pendiente')";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $miId, $idAmigo);
        mysqli_stmt_execute($stmt);
    }

    // Redirigir siempre a la comunidad si existe la referencia
    if ($comunidadRef) {
        header("Location: ver_comunidad.php?id=" . $comunidadRef . "&msg=pendiente");
    } else {
        header("Location: ../../perfil.php?id=" . $idAmigo);
    }
    exit;
} else {
    header("Location: ../../index.php");
    exit;
}