<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}
$admin = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Administracion</title>
    <link rel="stylesheet" href="../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="icon" href="../../media/logoPlatino.png">
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../media/logoPlatino.png" alt="" width="40px">
            <a href="../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../../index.php">Inicio</a></li>
                <li><a href="../videojuegos/juegos.php">Juegos</a></li>
                <li><a href="../jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
                <li><a href="../ranking/ranking.php">Ranking</a></li>
                <?php if ($admin): ?>
                    <li><a href="indexAdmin.php" class="activo">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>  
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
        <?php else: ?>
            <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>
    <div class="central">
    <h1>Admin</h1>
    <p>Este es el apartado de administración de la página web.</p>
    </div>
    <div class="admin-container">
    <h2>Gestión de contenido</h2>
    <div class="admin-grid">
        <a href="gestion/gestionJuegos/gestionJuegos.php" class="admin-card">
            <div class="card-icon">🎮</div>
            <span>Gestionar Videojuegos</span>
        </a>
        <a href="gestion/gestionJugadores/gestionJugadores.php" class="admin-card">
            <div class="card-icon">👤</div>
            <span>Gestionar Jugadores</span>
        </a>
        <a href="gestion/gestionComunidades/gestionComunidades.php" class="admin-card">
            <div class="card-icon">🏘️</div>
            <span>Gestionar Comunidades</span>
        </a>
        <a href="gestion/gestionLogros/gestionLogros.php" class="admin-card">
            <div class="card-icon">🏆</div>
            <span>Gestionar Logros</span>
        </a>
        <a href="gestion/gestionTiendas/gestionTienda.php" class="admin-card">
            <div class="card-icon">🛒</div>
            <span>Gestionar Tienda</span>
        </a>
    </div>
</div>

</body>
</html>