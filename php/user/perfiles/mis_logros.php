<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../../index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$query = $conexion->prepare("
    SELECT 
        l.nombre_logro,
        l.descripcion,
        l.puntos_logro,
        lu.fecha_obtencion,
        v.titulo AS videojuego
    FROM Logros_Usuario lu
    JOIN Logros l ON lu.id_logro = l.id_logro
    JOIN Videojuego v ON l.id_videojuego = v.id_videojuego
    WHERE lu.id_usuario = ?
    ORDER BY lu.fecha_obtencion DESC
");

$query->bind_param("i", $id_usuario);
$query->execute();
$resultado = $query->get_result();
$total_logros = $resultado->num_rows;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Logros - SalsaBox</title>
    <link rel="stylesheet" href="../../../estilos/estilos_statsPerfil.css">
    <link rel="icon" href="../../../media/logoPlatino.png">
</head>
<body>
    <div class="container-lista" style="width: 100%; max-width: 900px; margin: 0 auto;">
        <a href="perfilSesion.php" class="btn-volver">← Volver al Perfil</a>
        
        <h1>Mis Logros (<?php echo $total_logros; ?>)</h1>

        <?php if ($total_logros > 0): ?>
            <?php while($row = $resultado->fetch_assoc()): ?>
                <div class="logro-card">
                    <span class="puntos">+<?php echo $row['puntos_logro']; ?> pts</span>
                    
                    <div class="item-content">
                        <h3 class="item-title"><?php echo htmlspecialchars($row['nombre_logro']); ?></h3>
                        <p class="item-desc">
                            <?php echo htmlspecialchars($row['descripcion']); ?>
                        </p>
                        <p class="item-desc">
                            <?php echo htmlspecialchars($row['videojuego']); ?>
                        </p>
                        <span class="fecha">
                            <?php echo date('d/m/Y', strtotime($row['fecha_obtencion'])); ?>
                        </span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="item-card" style="border-left: 4px solid #ff4d4d;">
                <p>Aún no has desbloqueado ningún logro.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>