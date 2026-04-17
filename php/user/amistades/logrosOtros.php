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
$Usuario = $queryUser->get_result()->fetch_assoc();

if (!$Usuario) die("Usuario no encontrado");

$sql = "SELECT l.nombre_logro, l.descripcion, l.puntos_logro 
        FROM Logros_Usuario lu 
        JOIN Logros l ON lu.id_logro = l.id_logro 
        WHERE lu.id_usuario = ?";
$query = $conexion->prepare($sql);
$query->bind_param("i", $id_objetivo);
$query->execute();
$resultado = $query->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Logros de <?php echo $Usuario['gameTag']; ?></title>
    <link rel="stylesheet" href="../../../estilos/estilos_statsPerfil.css">
    <link rel="icon" href="../../../media/logoPlatino.png">
</head>
<body>
    <div class="container-lista" style="width: 100%; max-width: 900px; margin: 0 auto; padding: 20px;">
        <a href="perfilOtros.php?id=<?php echo $id_objetivo; ?>" class="btn-volver" style="color: #9ab3bc; text-decoration: none;">← Volver al Perfil</a>
        <h1 class="section-title" style="color: #e0be00; border-bottom: 2px solid #e0be00; padding-bottom: 10px; margin-top: 20px;">
            Logros de <?php echo htmlspecialchars($Usuario['gameTag']); ?>
        </h1>

        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <div class="item-card" style="display: flex; justify-content: space-between; align-items: center; background: #1b2129; padding: 15px; border-radius: 12px; margin-bottom: 10px; border: 1px solid #2c3440;">
                    <div class="item-content">
                        <h3 class="item-title" style="margin: 0; color: #e0be00;">
                            <?php echo htmlspecialchars($row['nombre_logro']); ?>
                        </h3>
                        <p class="item-desc" style="margin: 5px 0 0; color: #9ab3bc; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($row['descripcion']); ?>
                        </p>
                    </div>
                    <div class="puntos" style="color: #e0be00; font-weight: bold; font-size: 1.2rem;">
                        +<?php echo $row['puntos_logro']; ?>G
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg" style="color: #9ab3bc; font-style: italic; margin-top: 20px;">
                Este Usuario no ha desbloqueado logros todavía.
            </p>
        <?php endif; ?>
    </div>
</body>
</html>