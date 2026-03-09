<?php
$id_user_stats = $_SESSION['id_usuario'];

$queryJuegos = $conexion->prepare("
    SELECT COUNT(b.id_videojuego) as total 
    FROM Biblioteca b
    INNER JOIN Videojuego v ON b.id_videojuego = v.id_videojuego
    WHERE b.id_usuario = ?
");
$queryJuegos->bind_param("i", $id_user_stats);
$queryJuegos->execute();
$resJuegos = $queryJuegos->get_result()->fetch_assoc();
$totalJuegos = (int)$resJuegos['total'];
$queryJuegos->close();

$queryPuntos = $conexion->prepare("
    SELECT SUM(l.puntos_logro) as total_puntos 
    FROM Logros_Usuario lu 
    JOIN Logros l ON lu.id_logro = l.id_logro 
    WHERE lu.id_usuario = ?
");
$queryPuntos->bind_param("i", $id_user_stats);
$queryPuntos->execute();
$resPuntos = $queryPuntos->get_result()->fetch_assoc();
$totalPuntos = (int)($resPuntos['total_puntos'] ?? 0);
$queryPuntos->close();

$queryAmigos = $conexion->prepare("
    SELECT COUNT(*) as total 
    FROM Amigos a
    INNER JOIN Usuario u1 ON a.id_usuario = u1.id_usuario
    INNER JOIN Usuario u2 ON a.id_amigo = u2.id_usuario
    WHERE (a.id_usuario = ? OR a.id_amigo = ?) 
    AND a.estado = 'aceptada'
");
$queryAmigos->bind_param("ii", $id_user_stats, $id_user_stats);
$queryAmigos->execute();
$resAmigos = $queryAmigos->get_result()->fetch_assoc();
$totalAmigos = (int)$resAmigos['total'];
$queryAmigos->close();

$queryPendientes = $conexion->prepare("SELECT COUNT(*) as total FROM Amigos WHERE id_amigo = ? AND estado = 'pendiente'");
$queryPendientes->bind_param("i", $id_user_stats);
$queryPendientes->execute();
$totalPendientes = (int)$queryPendientes->get_result()->fetch_assoc()['total'];
$queryPendientes->close();
?>