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

$sql = "SELECT u.id_usuario, u.gameTag, u.avatar 
        FROM Usuario u 
        WHERE u.id_usuario IN (
            SELECT id_amigo FROM Amigos WHERE id_usuario = ? AND estado = 'aceptada'
            UNION
            SELECT id_usuario FROM Amigos WHERE id_amigo = ? AND estado = 'aceptada'
        ) AND u.id_usuario != ?";

$query = $conexion->prepare($sql);
$query->bind_param("iii", $id_objetivo, $id_objetivo, $id_objetivo);
$query->execute();
$resultado = $query->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Amigos de <?php echo $usuario['gameTag']; ?></title>
    <link rel="stylesheet" href="../../../estilos/estilos_statsPerfil.css">
    <link rel="icon" href="../../../media/logoPlatino.png">
</head>
<body>
    <div class="container-lista">
    <a href="perfilOtros.php?id=<?php echo $id_objetivo; ?>" class="btn-volver">← Volver al Perfil</a>
    <h1>Amigos de <?php echo htmlspecialchars($usuario['gameTag']); ?></h1>

    <?php if ($resultado->num_rows > 0): ?>
        <?php while ($row = $resultado->fetch_assoc()): ?>
            <div class="item-card">
                <a href="perfilOtros.php?id=<?php echo $row['id_usuario']; ?>" class="amigo-info-principal" style="text-decoration: none;">
                    <?php $img = !empty($row['avatar']) ? "../../../".$row['avatar'] : "../../../media/perfil_default.jpg"; ?>
                    <img src="<?php echo $img; ?>" class="item-img" alt="Avatar">
                    <div class="item-content">
                        <h3 class="item-title"><?php echo htmlspecialchars($row['gameTag']); ?></h3>
                        <p>Jugador de SalsaBox</p>
                    </div>
                </a>
                </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="empty-msg">Este usuario no tiene amigos aún.</p>
    <?php endif; ?>
</div>
    </div>
</body>
</html>