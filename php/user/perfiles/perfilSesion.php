<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['tag'])) {
    header("Location: ../../../index.php");
    exit();
}
if(isset($_SESSION["steam_vinculado"])): ?>

<script>
alert("Cuenta de Steam vinculada correctamente");
</script>

<?php
unset($_SESSION["steam_vinculado"]);
endif;

if(isset($_SESSION["steam_sync"])){
    echo "<script>alert('".$_SESSION["steam_sync"]."');</script>";
    unset($_SESSION["steam_sync"]);
}


include 'statsUsuario.php'; 

$id = $_SESSION['id_usuario'];
$stmt = $conexion->prepare("SELECT biografia, avatar FROM Usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$user = $resultado->fetch_assoc();

$biografia = $user['biografia'] ?? '';
$avatar_db = trim($user['avatar'] ?? '');
$img = (empty($avatar_db)) ? "../../../media/perfil_default.jpg" : "../../../media/" . $avatar_db;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($_SESSION['tag']); ?></title>
    <link rel="icon" href="../../../media/logoplatino.png">
    <link rel="stylesheet" href="../../../estilos/estilos_perfilSesion.css?v=<?php echo time(); ?>">
</head>
<body>
    <main class="perfil-container">
        <div class="perfil-card">
            <div class="perfil-header">
                <div class="avatar-grande">
                    <img src="<?php echo htmlspecialchars($img); ?>" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                </div>
                <h1><?php echo htmlspecialchars($_SESSION['tag']); ?></h1>
                <p class="status">
                <?= ($_SESSION['admin'] == 1) ? 'Administrador de SalsaBox' : 'Miembro de SalsaBox'; ?>
                </p>
                <br>
                <a href="../editarPerfil/editarPerfil.php" style="background-color: #e0be00; color:#000; padding: 0.6rem 1.2rem; border-radius:4px; text-decoration:none; font-weight:bold;">Editar Perfil</a>
            </div>

            <div class="perfil-stats">
                <a href="mis_juegos.php" class="stat-link">
                    <div class="stat-item">
                        <span class="stat-num"><?php echo $totalJuegos; ?></span>
                        <span class="stat-label">Juegos</span>
                    </div>
                </a>

                <a href="mis_logros.php" class="stat-link">
                    <div class="stat-item">
                        <span class="stat-num"><?php echo $totalPuntos; ?></span>
                        <span class="stat-label">Puntos</span>
                    </div>
                </a>

                <a href="mis_amigos.php" class="stat-link">
                    <div class="stat-item">
                        <span class="stat-num"><?php echo $totalAmigos; ?></span>
                        <span class="stat-label">Amigos</span>
                    </div>
                </a>
            </div>
            <br>
            <div>
                <a href="../../../Steam/steam_login.php">
                    <img src="https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_01.png">
                </a>
            </div>
            <div>
                <a href="../../../Steam/sync_logros_steam.php" class="btn-sync-steam">
                    Sincronizar logros de Steam
                </a>
            </div>
            <div class="perfil-body">
                <h3>Sobre ti</h3>
                <p class="bio-text">
                    <?php echo !empty($biografia) ? nl2br(htmlspecialchars($biografia)) : "Bienvenido. Aún no tienes biografía."; ?>
                </p>
            </div>

            <div class="perfil-footer">
                <a href="../../../index.php" class="btn-volver">Volver al inicio</a>
                <a href="../../sesiones/logout/logout.php" style="color: #ff4d4d; text-decoration: none;">Cerrar Sesión</a>
            </div>
        </div>
    </main>
</body>
</html>