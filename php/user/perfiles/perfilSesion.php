<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['tag'])) {
    header("Location: ../../../index.php");
    exit();
}

$id = $_SESSION['id_usuario'];
$stmt = $conexion->prepare("SELECT biografia, avatar FROM Usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$user = $resultado->fetch_assoc();

$biografia = $user['biografia'] ?? '';
$avatar = $user['avatar'] ?? '';
$inicial = strtoupper(substr($_SESSION['tag'], 0, 1));
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
                    <?php if (!empty($avatar)): ?>
                        <img src="<?php echo htmlspecialchars($avatar); ?>" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                    <?php else: ?>
                        <div style="display:flex; justify-content:center; align-items:center; height:100%; width:100%; font-size:2rem; background: #2c3440; border-radius:50%; color: white;">
                            <?php echo $inicial; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h1><?php echo htmlspecialchars($_SESSION['tag']); ?></h1>
                <p class="status">Miembro de SalsaBox</p><br>
                <a href="../editarPerfil/editarPerfil.php" style="background-color: #e0be00; color:#000; padding: 0.6rem 1.2rem; border-radius:4px; text-decoration:none; font-weight:bold;">Editar Perfil</a>
            </div>

            <div class="perfil-stats">
                <div class="stat-item">
                    <span class="stat-num">0</span>
                    <span class="stat-label">Juegos</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num">0</span>
                    <span class="stat-label">Puntos</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num">0</span>
                    <span class="stat-label">Amigos</span>
                </div>
            </div>

            <div class="perfil-body">
                <h3>Sobre ti</h3>
                <p><?php echo !empty($biografia) ? nl2br(htmlspecialchars($biografia)) : "Bienvenido. Aún no tienes biografía."; ?></p>
            </div>

            <div class="perfil-footer">
                <a href="../../../index.php" class="btn-volver">Volver al inicio</a>
                <a href="../../sesiones/logout/logout.php" style="color: #ff4d4d; text-decoration: none;">Cerrar Sesión</a>
            </div>
        </div>
    </main>
</body>
</html>