<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../../index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$query = $conexion->prepare("
    SELECT v.titulo, v.portada, b.estado, b.horas_totales 
    FROM Biblioteca b
    JOIN Videojuego v ON b.id_videojuego = v.id_videojuego
    WHERE b.id_usuario = ?
");
$query->bind_param("i", $id_usuario);
$query->execute();
$resultado = $query->get_result();
$total_juegos = $resultado->num_rows;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Juegos - SalsaBox</title>
    <link rel="stylesheet" href="../../../estilos/estilos_statsPerfil.css">
    <link rel="icon" href="../../../media/logoPlatino.png">

</head>
<body>
    <div class="container-lista" style="width: 100%; max-width: 900px; margin: 0 auto; padding: 20px;">
        <a href="perfilSesion.php" class="btn-volver" style="color: #9ab3bc; text-decoration: none;">← Volver al Perfil</a>
        
        <h1 style="color: #e0be00; border-bottom: 2px solid #e0be00; padding-bottom: 10px;">
            Mi Biblioteca (<?php echo $total_juegos; ?>)
        </h1>

        <?php if ($total_juegos > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Portada</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Horas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php 
                                $ruta_portada = !empty($row['portada']) ? "../../../" . $row['portada'] : "../../../media/default_game.png";
                            ?>
                            <img src="<?php echo htmlspecialchars($ruta_portada); ?>" class="portada" alt="Juego">
                        </td>
                        <td style="font-weight: bold; color: #e0be00;">
                            <?php echo htmlspecialchars($row['titulo']); ?>
                        </td>
                        <td>
                            <span class="status-badge">
                                <?php echo htmlspecialchars($row['estado']); ?>
                            </span>
                        </td>
                        <td style="color: #9ab3bc;">
                            <?php echo number_format($row['horas_totales'], 1); ?> h
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="item-card" style="border-left: 4px solid #ff4d4d; margin-top: 20px;">
                <p>Parece que tu biblioteca está vacía. ¡Añade algunos juegos para empezar a trackear tus horas!</p>
                <p style="font-size: 0.8rem; color: #888;">Tu ID de usuario es: <?php echo $id_usuario; ?></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>