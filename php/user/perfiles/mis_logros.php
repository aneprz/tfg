<?php
session_start();
require '../../../db/conexiones.php';

$id_usuario = $_SESSION['id_usuario'];

$query = $conexion->prepare("
    SELECT l.nombre_logro, l.descripcion, l.puntos_logro, lu.fecha_obtencion 
    FROM Logros_Usuario lu
    JOIN Logros l ON lu.id_logro = l.id_logro
    WHERE lu.id_usuario = ?
    ORDER BY lu.fecha_obtencion DESC
");
$query->bind_param("i", $id_usuario);
$query->execute();
$resultado = $query->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Logros - SalsaBox</title>
    <link rel="stylesheet" href="../../../estilos/estilos_statsPerfil.css">
</head>
<body>
    <a href="perfilSesion.php" class="btn-volver">← Volver al Perfil</a>
    <h1>Mis Logros</h1>
    <?php while($row = $resultado->fetch_assoc()): ?>
    <div class="logro-card">
        <span class="puntos">+<?php echo $row['puntos_logro']; ?> pts</span>
        <h3><?php echo htmlspecialchars($row['nombre_logro']); ?></h3>
        <p><?php echo htmlspecialchars($row['descripcion']); ?></p>
        <span class="fecha">Obtenido el: <?php echo date('d/m/Y', strtotime($row['fecha_obtencion'])); ?></span>
    </div>
    <?php endwhile; ?>
</body>
</html>