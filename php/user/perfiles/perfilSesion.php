<?php
session_start();

if (!isset($_SESSION['tag'])) {
    header("Location: ../../../index.php");
    exit();
}

$inicial = strtoupper(substr($_SESSION['tag'], 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($_SESSION['tag']); ?> - SalsaBox</title>
    
    <link rel="icon" href="../../../media/logoplatinoSinFondo.png">
    <link rel="stylesheet" href="../../../estilos/estilos_perfilSesion.css">
</head>
<body>

    <main class="perfil-container">
        <div class="perfil-card">
            
            <div class="perfil-header">
                <div class="avatar-grande">
                    <?php echo $inicial; ?>
                </div>
                <h1><?php echo htmlspecialchars($_SESSION['tag']); ?></h1>
                <p class="status">Miembro de SalsaBox</p>
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
                <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['tag']); ?></strong>. 
                Este es tu espacio privado donde podrás gestionar tu biblioteca de videojuegos, 
                revisar tus últimas puntuaciones y ver en qué comunidades participas.</p>
            </div>

            <div class="perfil-footer">
                <a href="../../../index.php" class="btn-volver">Volver al inicio</a>
                
                <a href="../../sesiones/logout/logout.php" class="btn-logout">Cerrar Sesión de forma segura</a>
            </div>

        </div>
    </main>

</body>
</html>