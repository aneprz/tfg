<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['tag'])) {
    header("Location: ../../../index.php");
    exit();
}

/* =========================
   MENSAJES
   ========================= */

if(isset($_SESSION["steam_vinculado"])){
    echo "<script>alert('Cuenta de Steam vinculada correctamente');</script>";
    unset($_SESSION["steam_vinculado"]);
}

if(isset($_SESSION["steam_sync"])){
    echo "<script>alert('".$_SESSION["steam_sync"]."');</script>";
    unset($_SESSION["steam_sync"]);
}

include 'statsUsuario.php'; 

$id = $_SESSION['id_usuario'];

/* =========================
   ITEMS EQUIPADOS
   ========================= */

$stmt = $conexion->prepare("
    SELECT ti.tipo, ti.imagen, ti.rareza
    FROM Usuario_Items ui
    JOIN Tienda_Items ti ON ui.id_item = ti.id_item
    WHERE ui.id_usuario = ?
    AND ui.equipado = 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$marco = null;
$fondo = null;
$avatar_item = null;

while ($item = $result->fetch_assoc()) {
    if ($item['tipo'] === 'marco') $marco = $item['imagen'];
    if ($item['tipo'] === 'fondo') $fondo = $item['imagen'];
    if ($item['tipo'] === 'avatar') $avatar_item = $item['imagen'];
}

/* =========================
   DATOS USUARIO
   ========================= */

$stmt = $conexion->prepare("
    SELECT biografia, avatar, puntos_actuales 
    FROM Usuario 
    WHERE id_usuario = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$biografia = $user['biografia'] ?? '';
$puntos_actuales = $user['puntos_actuales'] ?? 0;

/* =========================
   AVATAR FINAL
   ========================= */

if ($avatar_item) {
    $img = "../../../media/" . $avatar_item;
} else {
    $avatar_db = trim($user['avatar'] ?? '');
    $img = empty($avatar_db) 
        ? "../../../media/perfil_default.jpg" 
        : "../../../media/" . $avatar_db;
}
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

<body style="<?php if($fondo): ?>
    background-image: url('../../../media/<?php echo htmlspecialchars($fondo); ?>');
<?php endif; ?>">

<main class="perfil-container">

    <div class="perfil-card">

        <div class="perfil-header">

            <!-- AVATAR + MARCO -->
            <div class="avatar-grande">

                <img src="<?php echo htmlspecialchars($img); ?>" class="avatar-img">

                <?php if($marco): ?>
                    <img src="../../../media/<?php echo htmlspecialchars($marco); ?>" class="marco-img">
                <?php endif; ?>

            </div>

            <h1><?php echo htmlspecialchars($_SESSION['tag']); ?></h1>

            <p class="status">
                <?= ($_SESSION['admin'] == 1) ? 'Administrador de SalsaBox' : 'Miembro de SalsaBox'; ?>
            </p>

            <br>

            <a href="../editarPerfil/editarPerfil.php"
               style="background-color: #e0be00; color:#000; padding: 0.6rem 1.2rem; border-radius:4px; text-decoration:none; font-weight:bold;">
               Editar Perfil
            </a>

        </div>

        <!-- STATS -->
        <div class="perfil-stats">

            <a href="mis_juegos.php" class="stat-link">
                <div class="stat-item">
                    <span class="stat-num"><?php echo $totalJuegos; ?></span>
                    <span class="stat-label">Juegos</span>
                </div>
            </a>

            <a href="mis_logros.php" class="stat-link">
                <div class="stat-item">
                    <span class="stat-num"><?php echo $puntos_actuales; ?></span>
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

        <!-- STEAM -->
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

        <!-- BIO -->
        <div class="perfil-body">
            <h3>Sobre ti</h3>
            <p class="bio-text">
                <?php echo !empty($biografia) 
                    ? nl2br(htmlspecialchars($biografia)) 
                    : "Bienvenido. Aún no tienes biografía."; ?>
            </p>
        </div>

        <!-- FOOTER -->
        <div class="perfil-footer">
            <a href="../../../index.php" class="btn-volver">Volver al inicio</a>
            <a href="../../sesiones/logout/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>

    </div>

</main>

</body>
</html>