<?php
$id_user_stats = $_SESSION['id_usuario'];

$queryJuegos = $conexion->prepare("SELECT COUNT(*) as total FROM Biblioteca WHERE id_usuario = ?");
$queryJuegos->bind_param("i", $id_user_stats);
$queryJuegos->execute();
$resJuegos = $queryJuegos->get_result()->fetch_assoc();
$totalJuegos = $resJuegos['total'];

$queryPuntos = $conexion->prepare("
    SELECT SUM(l.puntos_logro) as total_puntos 
    FROM Logros_Usuario lu 
    JOIN Logros l ON lu.id_logro = l.id_logro 
    WHERE lu.id_usuario = ?
");
$queryPuntos->bind_param("i", $id_user_stats);
$queryPuntos->execute();
$resPuntos = $queryPuntos->get_result()->fetch_assoc();
$totalPuntos = $resPuntos['total_puntos'] ?? 0;

$queryAmigos = $conexion->prepare("
    SELECT COUNT(*) as total 
    FROM Amigos 
    WHERE (id_usuario = ? OR id_amigo = ?) 
    AND estado = 'aceptada'
");
$queryAmigos->bind_param("ii", $id_user_stats, $id_user_stats);
$queryAmigos->execute();
$resAmigos = $queryAmigos->get_result()->fetch_assoc();
$totalAmigos = $resAmigos['total'];

$queryPendientes = $conexion->prepare("SELECT COUNT(*) as total FROM Amigos WHERE id_amigo = ? AND estado = 'pendiente'");
$queryPendientes->bind_param("i", $id_user_stats);
$queryPendientes->execute();
$totalPendientes = $queryPendientes->get_result()->fetch_assoc()['total'];
?>