<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: ../../../index.php");
    exit();    
}

$id_objetivo = $_GET['id'];

$queryUser = $conexion->prepare("SELECT gameTag FROM Usuario WHERE id_usuario = ?");
$queryUser->bind_param("i", $id_objetivo);
$queryUser->execute();
$usuario = $queryUser->get_result()->fetch_assoc();

if (!$usuario) die("Usuario no encontrado");

$sql = "SELECT v.titulo, v.portada, b.horas_totales 
        FROM Biblioteca b 
        JOIN Videojuego v ON b.id_videojuego = v.id_videojuego 
        WHERE b.id_usuario = ?";
$query = $conexion->prepare($sql);
$query->bind_param("i", $id_objetivo);
$query->execute();
$resultado = $query->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca de <?php echo $usuario['gameTag']; ?> - SalsaBox</title>
    <link rel="stylesheet" href="../../../estilos/estilos_statsPerfil.css">
    <link rel="icon" href="../../../media/logoPlatino.png">
</head>
<body>
    <div class="container-lista" style="width: 100%; max-width: 900px; margin: 0 auto; padding: 20px;">
        <a href="perfilOtros.php?id=<?php echo $id_objetivo; ?>" class="btn-volver" style="color: #9ab3bc; text-decoration: none; font-weight: bold;">← Volver al Perfil</a>
        
        <h1 class="section-title" style="color: #e0be00; border-bottom: 2px solid #e0be00; padding-bottom: 10px; margin-top: 20px;">
            Biblioteca de <?php echo htmlspecialchars($usuario['gameTag']); ?>
        </h1>

        <div class="grid-juegos" style="margin-top: 20px;">
            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <div class="item-card" style="display: flex; align-items: center; background: #1b2129; padding: 15px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #2c3440;">
                        <img src="../../../<?php echo !empty($row['portada']) ? $row['portada'] : 'media/logoPlatino.png'; ?>" 
                             style="width: 100px; height: 140px; border-radius: 8px; object-fit: cover; margin-right: 20px; border: 1px solid #444;">
                        
                        <div class="item-content">
                            <h3 class="item-title" style="margin: 0; color: #e0be00; font-size: 1.4rem;">
                                <?php echo htmlspecialchars($row['titulo']); ?>
                            </h3>
                            <p class="item-desc" style="margin: 10px 0 0; color: #9ab3bc; font-size: 1rem;">
                                <strong>Tiempo:</strong> <?php echo number_format($row['horas_totales'], 1); ?> horas
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-msg" style="color: #9ab3bc; font-style: italic; text-align: center; margin-top: 50px;">
                    Este usuario aún no ha añadido juegos a su biblioteca.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>